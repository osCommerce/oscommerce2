<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2009 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
  if (!tep_session_is_registered('customer_id')) {
    $snapshot = array('page' => 'ext/modules/payment/paypal/express_payflow.php',
                      'mode' => $request_type,
                      'get' => $HTTP_GET_VARS,
                      'post' => $HTTP_POST_VARS);

    $navigation->set_snapshot($snapshot);

    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($cart->count_contents() < 1) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
  }

  require(DIR_WS_LANGUAGES . $language . '/modules/payment/paypal_pro_payflow_ec.php');
  require('includes/modules/payment/paypal_pro_payflow_ec.php');

  $paypal_pro_payflow_ec = new paypal_pro_payflow_ec();

  if (!$paypal_pro_payflow_ec->check() || !$paypal_pro_payflow_ec->enabled) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
  }

  if (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTION_SERVER == 'Live') {
    $api_url = 'https://payflowpro.verisign.com/transaction';
    $paypal_url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout';
  } else {
    $api_url = 'https://pilot-payflowpro.verisign.com/transaction';
    $paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout';
  }

  if (!tep_session_is_registered('sendto')) {
    tep_session_register('sendto');
    $sendto = $customer_default_address_id;
  }

  if (!tep_session_is_registered('billto')) {
    tep_session_register('billto');
    $billto = $customer_default_address_id;
  }

// register a random ID in the session to check throughout the checkout procedure
// against alterations in the shopping cart contents
  if (!tep_session_is_registered('cartID')) tep_session_register('cartID');
  $cartID = $cart->cartID;

  $params = array('USER' => (tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_USERNAME) ? MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_USERNAME : MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_VENDOR),
                  'VENDOR' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_VENDOR,
                  'PARTNER' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PARTNER,
                  'PWD' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PASSWORD,
                  'TENDER' => 'P',
                  'TRXTYPE' => ((MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTION_METHOD == 'Sale') ? 'S' : 'A'));

  switch ($HTTP_GET_VARS['osC_Action']) {
    case 'retrieve':
      $params['ACTION'] = 'G';
      $params['TOKEN'] = $HTTP_GET_VARS['token'];

      $post_string = '';

      foreach ($params as $key => $value) {
        $post_string .= $key . '[' . strlen(urlencode(utf8_encode(trim($value)))) . ']=' . urlencode(utf8_encode(trim($value))) . '&';
      }

      $post_string = substr($post_string, 0, -1);

      $response = $paypal_pro_payflow_ec->sendTransactionToGateway($api_url, $post_string, array('X-VPS-REQUEST-ID: ' . md5($cartID . tep_session_id() . rand())));
      $response_array = array();
      parse_str($response, $response_array);

      if ($response_array['RESULT'] == '0') {
        include(DIR_WS_CLASSES . 'order.php');

        if ($cart->get_content_type() != 'virtual') {
          $country_iso_code_2 = tep_db_prepare_input($response_array['SHIPTOCOUNTRY']);
          $zone_code = tep_db_prepare_input($response_array['SHIPTOSTATE']);

          $country_query = tep_db_query("select countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id from " . TABLE_COUNTRIES . " where countries_iso_code_2 = '" . tep_db_input($country_iso_code_2) . "'");
          $country = tep_db_fetch_array($country_query);

          $zone_name = $response_array['SHIPTOSTATE'];
          $zone_id = 0;

          $zone_query = tep_db_query("select zone_id, zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country['countries_id'] . "' and zone_code = '" . tep_db_input($zone_code) . "'");
          if (tep_db_num_rows($zone_query)) {
            $zone = tep_db_fetch_array($zone_query);

            $zone_name = $zone['zone_name'];
            $zone_id = $zone['zone_id'];
          }

          $sendto = array('firstname' => $response_array['FIRSTNAME'],
                          'lastname' => $response_array['LASTNAME'],
                          'company' => '',
                          'street_address' => $response_array['SHIPTOSTREET'],
                          'suburb' => '',
                          'postcode' => $response_array['SHIPTOZIP'],
                          'city' => $response_array['SHIPTOCITY'],
                          'zone_id' => $zone_id,
                          'zone_name' => $zone_name,
                          'country_id' => $country['countries_id'],
                          'country_name' => $country['countries_name'],
                          'country_iso_code_2' => $country['countries_iso_code_2'],
                          'country_iso_code_3' => $country['countries_iso_code_3'],
                          'address_format_id' => ($country['address_format_id'] > 0 ? $country['address_format_id'] : '1'));

          $billto = $sendto;

          $order = new order;

          $total_weight = $cart->show_weight();
          $total_count = $cart->count_contents();

// load all enabled shipping modules
          include(DIR_WS_CLASSES . 'shipping.php');
          $shipping_modules = new shipping;

          $free_shipping = false;

          if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') ) {
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

            if ( ($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
              $free_shipping = true;

              include(DIR_WS_LANGUAGES . $language . '/modules/order_total/ot_shipping.php');
            }
          }

          if (!tep_session_is_registered('shipping')) tep_session_register('shipping');
          $shipping = false;

          if ( (tep_count_shipping_modules() > 0) || ($free_shipping == true) ) {
            if ($free_shipping == true) {
              $shipping = 'free_free';
            } else {
// get all available shipping quotes
              $quotes = $shipping_modules->quote();

// select cheapest shipping method
              $shipping = $shipping_modules->cheapest();
              $shipping = $shipping['id'];
            }
          }

          if (strpos($shipping, '_')) {
            list($module, $method) = explode('_', $shipping);

            if ( is_object($$module) || ($shipping == 'free_free') ) {
              if ($shipping == 'free_free') {
                $quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
                $quote[0]['methods'][0]['cost'] = '0';
              } else {
                $quote = $shipping_modules->quote($method, $module);
              }

              if (isset($quote['error'])) {
                tep_session_unregister('shipping');

                tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
              } else {
                if ( (isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost'])) ) {
                  $shipping = array('id' => $shipping,
                                    'title' => (($free_shipping == true) ?  $quote[0]['methods'][0]['title'] : $quote[0]['module'] . ' (' . $quote[0]['methods'][0]['title'] . ')'),
                                    'cost' => $quote[0]['methods'][0]['cost']);
                }
              }
            }
          }

          if (!tep_session_is_registered('payment')) tep_session_register('payment');
          $payment = $paypal_pro_payflow_ec->code;

          if (!tep_session_is_registered('ppeuk_token')) tep_session_register('ppeuk_token');
          $ppeuk_token = $response_array['TOKEN'];

          if (!tep_session_is_registered('ppeuk_payerid')) tep_session_register('ppeuk_payerid');
          $ppeuk_payerid = $response_array['PAYERID'];

          tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'));
        } else {
          if (!tep_session_is_registered('shipping')) tep_session_register('shipping');
          $shipping = false;

          $sendto = false;

          if (!tep_session_is_registered('payment')) tep_session_register('payment');
          $payment = $paypal_pro_payflow_ec->code;

          if (!tep_session_is_registered('ppeuk_token')) tep_session_register('ppeuk_token');
          $ppeuk_token = $response_array['TOKEN'];

          if (!tep_session_is_registered('ppeuk_payerid')) tep_session_register('ppeuk_payerid');
          $ppeuk_payerid = $response_array['PAYERID'];

          tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'));
        }
      } else {
        switch ($response_array['RESULT']) {
          case '1':
          case '26':
            $error_message = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_CFG_ERROR;
            break;

          case '7':
            $error_message = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_ADDRESS;
            break;

          case '12':
            $error_message = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_DECLINED;
            break;

          case '1000':
            $error_message = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_EXPRESS_DISABLED;
            break;

          default:
            $error_message = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_GENERAL;
            break;
        }

        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'error_message=' . urlencode($error_message), 'SSL'));
      }

      break;

    default:
      include(DIR_WS_CLASSES . 'order.php');
      $order = new order;

      $params['ACTION'] = 'S';
      $params['CURRENCY'] = $order->info['currency'];
      $params['EMAIL'] = $order->customer['email_address'];
      $params['AMT'] = $paypal_pro_payflow_ec->format_raw($order->info['total']);
      $params['RETURNURL'] = tep_href_link('ext/modules/payment/paypal/express_payflow.php', 'osC_Action=retrieve', 'SSL', true, false);
      $params['CANCELURL'] = tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL', true, false);

      if ($order->content_type == 'virtual') {
        $params['NOSHIPPING'] = '1';
      }

      $post_string = '';

      foreach ($params as $key => $value) {
        $post_string .= $key . '[' . strlen(urlencode(utf8_encode(trim($value)))) . ']=' . urlencode(utf8_encode(trim($value))) . '&';
      }

      $post_string = substr($post_string, 0, -1);

      $response = $paypal_pro_payflow_ec->sendTransactionToGateway($api_url, $post_string, array('X-VPS-REQUEST-ID: ' . md5($cartID . tep_session_id() . rand())));
      $response_array = array();
      parse_str($response, $response_array);

      if ($response_array['RESULT'] == '0') {
        tep_redirect($paypal_url . '&token=' . $response_array['TOKEN']);
      } else {
        switch ($response_array['RESULT']) {
          case '1':
          case '26':
            $error_message = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_CFG_ERROR;
            break;

          case '1000':
            $error_message = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_EXPRESS_DISABLED;
            break;

          default:
            $error_message = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_GENERAL;
            break;
        }

        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'error_message=' . urlencode($error_message), 'SSL'));
      }

      break;
  }

  tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
