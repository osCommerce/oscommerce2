<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

chdir('../../../../');
require('includes/application_top.php');

if (!defined('OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS') || !in_array(OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS, array('1', '0'))) {
  exit;
}

if (!isset($HTTP_GET_VARS['action'])) {
  exit;
}

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
  exit;
}

$requiredExtensions = array('xmlwriter', 'openssl', 'dom', 'hash', 'curl');

foreach ($requiredExtensions as $ext) {
  if (!extension_loaded($ext)) {
    exit;
  }
}

if (!class_exists('braintree_cc', false)) {
  include(DIR_FS_CATALOG . 'includes/modules/payment/braintree_cc.php');
}

$pm = new braintree_cc();

switch ($HTTP_GET_VARS['action']) {
  case 'paypal':
    if (
      $pm->isPaymentTypeAccepted('paypal') &&
      tep_session_is_registered('appBraintreeCcFormHash') &&
      isset($HTTP_POST_VARS['bt_paypal_form_hash']) &&
      $HTTP_POST_VARS['bt_paypal_form_hash'] == $appBraintreeCcFormHash &&
      isset($HTTP_POST_VARS['bt_paypal_nonce']) &&
      !empty($HTTP_POST_VARS['bt_paypal_nonce'])
    ) {
      tep_session_unregister('appBraintreeCcFormHash');

      $pm->_app->setupCredentials();

      $bt = null;

      try {
        $bt = Braintree_PaymentMethodNonce::find($HTTP_POST_VARS['bt_paypal_nonce']);
      } catch (Exception $e) {
      }

      if (
        isset($bt) &&
        is_object($bt) &&
        isset($bt->nonce) &&
        $bt->nonce == $HTTP_POST_VARS['bt_paypal_nonce'] &&
        $bt->type == 'PayPalAccount' &&
        $bt->consumed === false
      ) {
        if (!tep_session_is_registered('payment')) {
          tep_session_register('payment');
        }
        $payment = 'braintree_cc';

        tep_session_register('appBraintreeCcNonce');
        $appBraintreeCcNonce = $bt->nonce;

        $force_login = false;

// check if e-mail address exists in database and login or create customer account
        if (!tep_session_is_registered('customer_id')) {
          $force_login = true;

          $email_address = tep_db_prepare_input($bt->details['payerInfo']['email']);

          $check_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' limit 1");
          if (tep_db_num_rows($check_query)) {
            $check = tep_db_fetch_array($check_query);

            tep_session_register('customer_id');
            tep_session_register('customer_first_name');
            tep_session_register('customer_default_address_id');

            $customer_id = $check['customers_id'];
            $customer_first_name = $check['customers_firstname'];
            $customer_default_address_id = $check['customers_default_address_id'];
          } else {
            $customers_firstname = tep_db_prepare_input($bt->details['payerInfo']['firstName']);
            $customers_lastname = tep_db_prepare_input($bt->details['payerInfo']['lastName']);

            $sql_data_array = array('customers_firstname' => $customers_firstname,
                                    'customers_lastname' => $customers_lastname,
                                    'customers_email_address' => $email_address,
                                    'customers_telephone' => '',
                                    'customers_fax' => '',
                                    'customers_newsletter' => '0',
                                    'customers_password' => '');

            if (isset($bt->details['payerInfo']['phone']) && !empty($bt->details['payerInfo']['phone'])) {
              $customers_telephone = tep_db_prepare_input($bt->details['payerInfo']['phone']);

              $sql_data_array['customers_telephone'] = $customers_telephone;
            }

            tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);

            tep_session_register('customer_id');
            tep_session_register('customer_first_name');

            $customer_id = tep_db_insert_id();
            $customer_first_name = $customers_firstname;

            tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int)$customer_id . "', '0', now())");

// Only generate a password and send an email if the Set Password Content Module is not enabled
            if ( !defined('MODULE_CONTENT_ACCOUNT_SET_PASSWORD_STATUS') || (MODULE_CONTENT_ACCOUNT_SET_PASSWORD_STATUS != 'True') ) {
              $customer_password = tep_create_random_value(max(ENTRY_PASSWORD_MIN_LENGTH, 8));

              tep_db_perform(TABLE_CUSTOMERS, array('customers_password' => tep_encrypt_password($customer_password)), 'update', 'customers_id = "' . (int)$customer_id . '"');

// build the message content
              $name = $customers_firstname . ' ' . $customers_lastname;
              $email_text = sprintf(EMAIL_GREET_NONE, $customers_firstname) .
                EMAIL_WELCOME .
                $pm->_app->getDef('module_cc_email_account_password', [
                  'email_address' => $email_address,
                  'password' => $customer_password
                ]) . "\n\n" .
                EMAIL_TEXT .
                EMAIL_CONTACT .
                EMAIL_WARNING;
              tep_mail($name, $email_address, EMAIL_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
            }
          }

          if (SESSION_RECREATE == 'True') {
            tep_session_recreate();
          }
        }

        $address_key = null;

        if (isset($bt->details['payerInfo']['shippingAddress'])) {
          $address_key = 'shippingAddress';
        } elseif (isset($bt->details['payerInfo']['billingAddress'])) {
          $address_key = 'billingAddress';
        }

        if (isset($address_key)) {
// check if paypal address exists in the address book
          if (isset($bt->details['payerInfo'][$address_key]['recipientName'])) {
            $name_array = explode(' ', $bt->details['payerInfo'][$address_key]['recipientName'], 2);

            $ship_firstname = tep_db_prepare_input($name_array[0]);
            $ship_lastname = isset($name_array[1]) ? tep_db_prepare_input($name_array[1]) : '';
          } else {
            $ship_firstname = tep_db_prepare_input($bt->details['payerInfo']['firstName']);
            $ship_lastname = tep_db_prepare_input($bt->details['payerInfo']['lastName']);
          }

          $ship_address = tep_db_prepare_input($bt->details['payerInfo'][$address_key]['line1']);
          $ship_city = tep_db_prepare_input($bt->details['payerInfo'][$address_key]['city']);
          $ship_zone = tep_db_prepare_input($bt->details['payerInfo'][$address_key]['state']);
          $ship_zone_id = 0;
          $ship_postcode = tep_db_prepare_input($bt->details['payerInfo'][$address_key]['postalCode']);
          $ship_country = tep_db_prepare_input($bt->details['payerInfo'][$address_key]['countryCode']);
          $ship_country_id = 0;
          $ship_address_format_id = 1;

          $country_query = tep_db_query("select countries_id, address_format_id from " . TABLE_COUNTRIES . " where countries_iso_code_2 = '" . tep_db_input($ship_country) . "' limit 1");
          if (tep_db_num_rows($country_query)) {
            $country = tep_db_fetch_array($country_query);

            $ship_country_id = $country['countries_id'];
            $ship_address_format_id = $country['address_format_id'];
          }

          if ($ship_country_id > 0) {
            $zone_query = tep_db_query("select zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int)$ship_country_id . "' and (zone_name = '" . tep_db_input($ship_zone) . "' or zone_code = '" . tep_db_input($ship_zone) . "') limit 1");
            if (tep_db_num_rows($zone_query)) {
              $zone = tep_db_fetch_array($zone_query);

              $ship_zone_id = $zone['zone_id'];
            }
          }

          $check_query = tep_db_query("select address_book_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and entry_firstname = '" . tep_db_input($ship_firstname) . "' and entry_lastname = '" . tep_db_input($ship_lastname) . "' and entry_street_address = '" . tep_db_input($ship_address) . "' and entry_postcode = '" . tep_db_input($ship_postcode) . "' and entry_city = '" . tep_db_input($ship_city) . "' and (entry_state = '" . tep_db_input($ship_zone) . "' or entry_zone_id = '" . (int)$ship_zone_id . "') and entry_country_id = '" . (int)$ship_country_id . "' limit 1");
          if (tep_db_num_rows($check_query)) {
            $check = tep_db_fetch_array($check_query);

            tep_session_register('sendto');
            $sendto = $check['address_book_id'];

            if (!tep_session_is_registered('customer_default_address_id')) {
              tep_session_register('customer_default_address_id');
              $customer_default_address_id = $check['address_book_id'];
            }
          } else {
            $sql_data_array = array('customers_id' => $customer_id,
                                    'entry_firstname' => $ship_firstname,
                                    'entry_lastname' => $ship_lastname,
                                    'entry_street_address' => $ship_address,
                                    'entry_postcode' => $ship_postcode,
                                    'entry_city' => $ship_city,
                                    'entry_country_id' => $ship_country_id,
                                    'entry_gender' => '');

            if (ACCOUNT_STATE == 'true') {
              if ($ship_zone_id > 0) {
                $sql_data_array['entry_zone_id'] = $ship_zone_id;
                $sql_data_array['entry_state'] = '';
              } else {
                $sql_data_array['entry_zone_id'] = '0';
                $sql_data_array['entry_state'] = $ship_zone;
              }
            }

            tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);

            $address_id = tep_db_insert_id();

            tep_session_register('sendto');
            $sendto = $address_id;

            if (!tep_session_is_registered('customer_default_address_id')) {
              tep_session_register('customer_default_address_id');
              $customer_default_address_id = 0;
            }

            if ($customer_default_address_id < 1) {
              tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int)$address_id . "' where customers_id = '" . (int)$customer_id . "'");
              $customer_default_address_id = $address_id;
            }
          }

          tep_session_register('billto');
          $billto = $sendto;

          if ($force_login == true) {
            tep_session_register('customer_country_id');
            tep_session_register('customer_zone_id');

            $customer_country_id = $ship_country_id;
            $customer_zone_id = $ship_zone_id;
          }

          include(DIR_FS_CATALOG . 'includes/classes/order.php');
          $order = new order();

          if ($cart->get_content_type() != 'virtual') {
            $total_weight = $cart->show_weight();
            $total_count = $cart->count_contents();

// load all enabled shipping modules
            include(DIR_FS_CATALOG . 'includes/classes/shipping.php');
            $shipping_modules = new shipping();

            $free_shipping = false;

            if (defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')) {
              $pass = false;

              switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
                case 'national':
                  if ($order->delivery['country_id'] == STORE_COUNTRY) {
                    $pass = true;
                  }
                  break;

                case 'international':
                  if ($order->delivery['country_id'] != STORE_COUNTRY) {
                    $pass = true;
                  }
                  break;

                case 'both':
                  $pass = true;
                  break;
              }

              if (($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) {
                $free_shipping = true;

                include(DIR_FS_CATALOG . 'includes/languages/' . $language . '/modules/order_total/ot_shipping.php');
              }
            }

            tep_session_register('shipping');
            $shipping = false;

            if ((tep_count_shipping_modules() > 0) || ($free_shipping == true)) {
              if ($free_shipping == true) {
                $shipping = 'free_free';
              } else {
                $shipping_modules->quote();

                $shipping = method_exists($shipping_modules, 'get_first') ? $shipping_modules->get_first() : $shipping_modules->cheapest();
                $shipping = $shipping['id'];
              }
            } else {
              if (defined('SHIPPING_ALLOW_UNDEFINED_ZONES') && (SHIPPING_ALLOW_UNDEFINED_ZONES == 'False')) {
                tep_session_unregister('shipping');

                $messageStack->add_session('checkout_address', $pm->_app->getDef('module_ec_error_no_shipping_available'), 'error');

                tep_session_register('appBraintreeCcRightTurn');
                $appBraintreeCcRightTurn = true;

                tep_redirect(tep_href_link('checkout_shipping_address.php', '', 'SSL'));
              }
            }

            if (strpos($shipping, '_')) {
              list($module, $method) = explode('_', $shipping);

              if (is_object($GLOBALS[$module]) || ($shipping == 'free_free')) {
                if ($shipping == 'free_free') {
                  $quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
                  $quote[0]['methods'][0]['cost'] = '0';
                } else {
                  $quote = $shipping_modules->quote($method, $module);
                }

                if (isset($quote['error'])) {
                  tep_session_unregister('shipping');

                  tep_redirect(tep_href_link('checkout_shipping.php', '', 'SSL'));
                } else {
                  if ((isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost']))) {
                    $shipping = array(
                     'id' => $shipping,
                     'title' => (($free_shipping == true) ?  $quote[0]['methods'][0]['title'] : $quote[0]['module'] . ' ' . $quote[0]['methods'][0]['title']),
                     'cost' => $quote[0]['methods'][0]['cost']
                    );
                  }
                }
              }
            }
          } else {
            $shipping = false;
            $sendto = false;
          }

          if (tep_session_is_registered('shipping')) {
            tep_redirect(tep_href_link('checkout_confirmation.php', '', 'SSL'));
          } else {
            tep_session_register('appBraintreeCcRightTurn');
            $appBraintreeCcRightTurn = true;

            tep_redirect(tep_href_link('checkout_shipping.php', '', 'SSL'));
          }
        }
      }
    }

    tep_redirect(tep_href_link('checkout_shipping.php', '', 'SSL'));
    break;

  case 'getCardToken':
    if (!tep_session_is_registered('customer_id')) {
      exit;
    }

    if ((OSCOM_APP_PAYPAL_BRAINTREE_CC_CC_TOKENS != '1') && (OSCOM_APP_PAYPAL_BRAINTREE_CC_CC_TOKENS != '2')) {
      exit;
    }

    if (!isset($HTTP_POST_VARS['card_id']) || !is_numeric($HTTP_POST_VARS['card_id']) || ($HTTP_POST_VARS['card_id'] < 1)) {
      exit;
    }

    $result = array();

    $card_query = tep_db_query("select braintree_token from customers_braintree_tokens where id = '" . (int)$HTTP_POST_VARS['card_id'] . "' and customers_id = '" . (int)$customer_id . "'");

    if (tep_db_num_rows($card_query)) {
      $card = tep_db_fetch_array($card_query);

      $pm->_app->setupCredentials();

      $pmn = Braintree_PaymentMethodNonce::create($card['braintree_token']);

      $result = array('result' => 1, 'token' => $pmn->paymentMethodNonce->nonce);
    }

    echo json_encode($result);

    exit;

    break;
}

require('includes/application_bottom.php');
