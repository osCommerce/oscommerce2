<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($cart->count_contents() < 1) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
  }

// initialize variables if the customer is not logged in
  if (!tep_session_is_registered('customer_id')) {
    $customer_id = 0;
    $customer_default_address_id = 0;
  }

  require(DIR_WS_LANGUAGES . $language . '/modules/payment/paypal_pro_payflow_ec.php');
  require('includes/modules/payment/paypal_pro_payflow_ec.php');
  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CREATE_ACCOUNT);

  $paypal_pro_payflow_ec = new paypal_pro_payflow_ec();

  if (!$paypal_pro_payflow_ec->check() || !$paypal_pro_payflow_ec->enabled) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
  }

  if ( !tep_session_is_registered('sendto') ) {
    if ( tep_session_is_registered('customer_id') ) {
      $sendto = $customer_default_address_id;
    } else {
      $country = tep_get_countries(STORE_COUNTRY, true);

      $sendto = array('firstname' => '',
                      'lastname' => '',
                      'company' => '',
                      'street_address' => '',
                      'suburb' => '',
                      'postcode' => '',
                      'city' => '',
                      'zone_id' => STORE_ZONE,
                      'zone_name' => tep_get_zone_name(STORE_COUNTRY, STORE_ZONE, ''),
                      'country_id' => STORE_COUNTRY,
                      'country_name' => $country['countries_name'],
                      'country_iso_code_2' => $country['countries_iso_code_2'],
                      'country_iso_code_3' => $country['countries_iso_code_3'],
                      'address_format_id' => tep_get_address_format_id(STORE_COUNTRY));
    }
  }

  if ( !tep_session_is_registered('billto') ) {
    $billto = $sendto;
  }

// register a random ID in the session to check throughout the checkout procedure
// against alterations in the shopping cart contents
  if (!tep_session_is_registered('cartID')) tep_session_register('cartID');
  $cartID = $cart->cartID;

  switch ($HTTP_GET_VARS['osC_Action']) {
    case 'retrieve':
      $response_array = $paypal_pro_payflow_ec->getExpressCheckoutDetails($HTTP_GET_VARS['token']);

      if ($response_array['RESULT'] == '0') {
        if ( !tep_session_is_registered('ppeuk_secret') || ($response_array['CUSTOM'] != $ppeuk_secret) ) {
          tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
        }

        if (!tep_session_is_registered('payment')) tep_session_register('payment');
        $payment = $paypal_pro_payflow_ec->code;

        if (!tep_session_is_registered('ppeuk_token')) tep_session_register('ppeuk_token');
        $ppeuk_token = $response_array['TOKEN'];

        if (!tep_session_is_registered('ppeuk_payerid')) tep_session_register('ppeuk_payerid');
        $ppeuk_payerid = $response_array['PAYERID'];

        if (!tep_session_is_registered('ppeuk_payerstatus')) tep_session_register('ppeuk_payerstatus');
        $ppeuk_payerstatus = $response_array['PAYERSTATUS'];

        if (!tep_session_is_registered('ppeuk_addressstatus')) tep_session_register('ppeuk_addressstatus');
        $ppeuk_addressstatus = $response_array['ADDRESSSTATUS'];

        $force_login = false;

// check if e-mail address exists in database and login or create customer account
        if (!tep_session_is_registered('customer_id')) {
          $force_login = true;

          $email_address = tep_db_prepare_input($response_array['EMAIL']);

          $check_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' limit 1");
          if (tep_db_num_rows($check_query)) {
            $check = tep_db_fetch_array($check_query);

// Force the customer to log into their local account if payerstatus is unverified and a local password is set
            if ( ($response_array['PAYERSTATUS'] == 'unverified') && !empty($check['customers_password']) ) {
              $messageStack->add_session('login', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_WARNING_LOCAL_LOGIN_REQUIRED, 'warning');

              $navigation->set_snapshot();

              $login_url = tep_href_link(FILENAME_LOGIN, '', 'SSL');
              $login_email_address = tep_output_string($response_array['EMAIL']);

      $output = <<<EOD
<form name="pe" action="{$login_url}" method="post" target="_top">
  <input type="hidden" name="email_address" value="{$login_email_address}" />
</form>
<script type="text/javascript">
document.pe.submit();
</script>
EOD;

              echo $output;
              exit;
            } else {
              $customer_id = $check['customers_id'];
              $customers_firstname = $check['customers_firstname'];
              $customer_default_address_id = $check['customers_default_address_id'];
            }
          } else {
            $customers_firstname = tep_db_prepare_input($response_array['FIRSTNAME']);
            $customers_lastname = tep_db_prepare_input($response_array['LASTNAME']);

            $sql_data_array = array('customers_firstname' => $customers_firstname,
                                    'customers_lastname' => $customers_lastname,
                                    'customers_email_address' => $email_address,
                                    'customers_telephone' => '',
                                    'customers_fax' => '',
                                    'customers_newsletter' => '0',
                                    'customers_password' => '');

            if (isset($response_array['PHONENUM']) && tep_not_null($response_array['PHONENUM'])) {
              $customers_telephone = tep_db_prepare_input($response_array['PHONENUM']);

              $sql_data_array['customers_telephone'] = $customers_telephone;
            }

            tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);

            $customer_id = tep_db_insert_id();

            tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int)$customer_id . "', '0', now())");

// Only generate a password and send an email if the Set Password Content Module is not enabled
            if ( !defined('MODULE_CONTENT_ACCOUNT_SET_PASSWORD_STATUS') || (MODULE_CONTENT_ACCOUNT_SET_PASSWORD_STATUS != 'True') ) {
              $customer_password = tep_create_random_value(max(ENTRY_PASSWORD_MIN_LENGTH, 8));

              tep_db_perform(TABLE_CUSTOMERS, array('customers_password' => tep_encrypt_password($customer_password)), 'update', 'customers_id = "' . (int)$customer_id . '"');

// build the message content
              $name = $customers_firstname . ' ' . $customers_lastname;
              $email_text = sprintf(EMAIL_GREET_NONE, $customers_firstname) . EMAIL_WELCOME . sprintf(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_EMAIL_PASSWORD, $email_address, $customer_password) . EMAIL_TEXT . EMAIL_CONTACT . EMAIL_WARNING;
              tep_mail($name, $email_address, EMAIL_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
            }
          }

          if (SESSION_RECREATE == 'True') {
            tep_session_recreate();
          }

          $customer_first_name = $customers_firstname;
          tep_session_register('customer_id');
          tep_session_register('customer_first_name');

// reset session token
          $sessiontoken = md5(tep_rand() . tep_rand() . tep_rand() . tep_rand());
        }

// check if paypal shipping address exists in the address book
        $ship_firstname = tep_db_prepare_input(substr($response_array['SHIPTONAME'], 0, strpos($response_array['SHIPTONAME'], ' ')));
        $ship_lastname = tep_db_prepare_input(substr($response_array['SHIPTONAME'], strpos($response_array['SHIPTONAME'], ' ')+1));
        $ship_address = tep_db_prepare_input($response_array['SHIPTOSTREET']);
        $ship_city = tep_db_prepare_input($response_array['SHIPTOCITY']);
        $ship_zone = tep_db_prepare_input($response_array['SHIPTOSTATE']);
        $ship_zone_id = 0;
        $ship_postcode = tep_db_prepare_input($response_array['SHIPTOZIP']);
        $ship_country = tep_db_prepare_input($response_array['SHIPTOCOUNTRY']);
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

          $sendto = $check['address_book_id'];
        } else {
          $sql_data_array = array('customers_id' => $customer_id,
                                  'entry_firstname' => $ship_firstname,
                                  'entry_lastname' => $ship_lastname,
                                  'entry_street_address' => $ship_address,
                                  'entry_postcode' => $ship_postcode,
                                  'entry_city' => $ship_city,
                                  'entry_country_id' => $ship_country_id);

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

          $sendto = $address_id;

          if ($customer_default_address_id < 1) {
            tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int)$address_id . "' where customers_id = '" . (int)$customer_id . "'");
            $customer_default_address_id = $address_id;
          }
        }

        $billto = $sendto;

        if ( !tep_session_is_registered('sendto') ) {
          tep_session_register('sendto');
        }

        if ( !tep_session_is_registered('billto') ) {
          tep_session_register('billto');
        }

        if ($force_login == true) {
          $customer_country_id = $ship_country_id;
          $customer_zone_id = $ship_zone_id;
          tep_session_register('customer_default_address_id');
          tep_session_register('customer_country_id');
          tep_session_register('customer_zone_id');
        }

        include(DIR_WS_CLASSES . 'order.php');
        $order = new order;

        if ($cart->get_content_type() != 'virtual') {
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
          } else {
            if ( defined('SHIPPING_ALLOW_UNDEFINED_ZONES') && (SHIPPING_ALLOW_UNDEFINED_ZONES == 'False') ) {
              tep_session_unregister('shipping');

              $messageStack->add_session('checkout_address', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_NO_SHIPPING_AVAILABLE_TO_SHIPPING_ADDRESS, 'error');

              tep_session_register('ppecuk_right_turn');
              $ppecuk_right_turn = true;

              tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'));
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
        } else {
          if (!tep_session_is_registered('shipping')) tep_session_register('shipping');
          $shipping = false;

          $sendto = false;
        }

/* useraction=commit       tep_redirect(tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL')); */
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'));
      } else {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'error_message=' . urlencode($response_array['OSCOM_ERROR_MESSAGE']), 'SSL'));
      }

      break;

    default:
      if (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTION_SERVER == 'Live') {
        $paypal_url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout';
      } else {
        $paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout';
      }

      include(DIR_WS_CLASSES . 'order.php');
      $order = new order;

      $params = array('CURRENCY' => $order->info['currency'],
                      'EMAIL' => $order->customer['email_address'],
                      'ALLOWNOTE' => '0');

// A billing address is required for digital orders so we use the shipping address PayPal provides
//      if ($order->content_type == 'virtual') {
//        $params['NOSHIPPING'] = '1';
//      }

      $item_params = array();

      $line_item_no = 0;

      foreach ($order->products as $product) {
        if ( DISPLAY_PRICE_WITH_TAX == 'true' ) {
          $product_price = $paypal_pro_payflow_ec->format_raw($product['final_price'] + tep_calculate_tax($product['final_price'], $product['tax']));
        } else {
          $product_price = $paypal_pro_payflow_ec->format_raw($product['final_price']);
        }

        $item_params['L_NAME' . $line_item_no] = $product['name'];
        $item_params['L_COST' . $line_item_no] = $product_price;
        $item_params['L_QTY' . $line_item_no] = $product['qty'];

        $line_item_no++;
      }

      $params['BILLTOFIRSTNAME'] = $order->billing['firstname'];
      $params['BILLTOLASTNAME'] = $order->billing['lastname'];
      $params['BILLTOSTREET'] = $order->billing['street_address'];
      $params['BILLTOCITY'] = $order->billing['city'];
      $params['BILLTOSTATE'] = tep_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], $order->billing['state']);
      $params['BILLTOCOUNTRY'] = $order->billing['country']['iso_code_2'];
      $params['BILLTOZIP'] = $order->billing['postcode'];

      if (tep_not_null($order->delivery['street_address'])) {
        $params['SHIPTONAME'] = $order->delivery['firstname'] . ' ' . $order->delivery['lastname'];
        $params['SHIPTOSTREET'] = $order->delivery['street_address'];
        $params['SHIPTOCITY'] = $order->delivery['city'];
        $params['SHIPTOSTATE'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
        $params['SHIPTOCOUNTRY'] = $order->delivery['country']['iso_code_2'];
        $params['SHIPTOZIP'] = $order->delivery['postcode'];
      }

      if ($cart->get_content_type() != 'virtual') {
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

        if ( (tep_count_shipping_modules() > 0) || ($free_shipping == true) ) {
          if ($free_shipping == true) {
            $quotes_array[] = array('id' => 'free_free',
                                    'name' => FREE_SHIPPING_TITLE,
                                    'label' => '',
                                    'cost' => '0.00',
                                    'tax' => '0');
          } else {
// get all available shipping quotes
            $quotes = $shipping_modules->quote();

            foreach ($quotes as $quote) {
              if (!isset($quote['error'])) {
                foreach ($quote['methods'] as $rate) {
                  $quotes_array[] = array('id' => $quote['id'] . '_' . $rate['id'],
                                          'name' => $quote['module'],
                                          'label' => $rate['title'],
                                          'cost' => $rate['cost'],
                                          'tax' => $quote['tax']);
                }
              }
            }
          }
        } else {
          if ( defined('SHIPPING_ALLOW_UNDEFINED_ZONES') && (SHIPPING_ALLOW_UNDEFINED_ZONES == 'False') ) {
            tep_session_unregister('shipping');

            $messageStack->add_session('checkout_address', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_NO_SHIPPING_AVAILABLE_TO_SHIPPING_ADDRESS);

            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'));
          }
        }
      }

      $counter = 0;
      $cheapest_rate = null;
      $expensive_rate = 0;
      $cheapest_counter = $counter;
      $default_shipping = null;

      foreach ($quotes_array as $quote) {
        $shipping_rate = $paypal_pro_payflow_ec->format_raw($quote['cost'] + tep_calculate_tax($quote['cost'], $quote['tax']));

        if (is_null($cheapest_rate) || ($shipping_rate < $cheapest_rate)) {
          $cheapest_rate = $shipping_rate;
          $cheapest_counter = $counter;
        }

        if ($shipping_rate > $expensive_rate) {
          $expensive_rate = $shipping_rate;
        }

        if (tep_session_is_registered('shipping') && ($shipping['id'] == $quote['id'])) {
          $default_shipping = $counter;
        }

        $counter++;
      }

      if (!is_null($default_shipping)) {
        $cheapest_counter = $default_shipping;
      } else {
        if ( !empty($quotes_array) ) {
          $shipping = array('id' => $quotes_array[$cheapest_counter]['id'],
                            'title' => trim($quotes_array[$cheapest_counter]['name'] . ' ' . $quotes_array[$cheapest_counter]['label']),
                            'cost' => $paypal_pro_payflow_ec->format_raw($quotes_array[$cheapest_counter]['cost']));

          $default_shipping = $cheapest_counter;
        } else {
          $shipping = false;
        }

        if ( !tep_session_is_registered('shipping') ) {
          tep_session_register('shipping');
        }
      }

// set shipping for order total calculations; shipping in $item_params includes taxes
      if (!is_null($default_shipping)) {
        $order->info['shipping_method'] = trim($quotes_array[$default_shipping]['name'] . ' ' . $quotes_array[$default_shipping]['label']);
        $order->info['shipping_cost'] = $paypal_pro_payflow_ec->format_raw($quotes_array[$default_shipping]['cost'] + tep_calculate_tax($quotes_array[$default_shipping]['cost'], $quotes_array[$default_shipping]['tax']));

        $order->info['total'] = $order->info['subtotal'] + $order->info['shipping_cost'];

        if ( DISPLAY_PRICE_WITH_TAX == 'false' ) {
          $order->info['total'] += $order->info['tax'];
        }
      }

      include(DIR_WS_CLASSES . 'order_total.php');
      $order_total_modules = new order_total;
      $order_totals = $order_total_modules->process();

// Remove shipping tax from total that was added again in ot_shipping
      if (DISPLAY_PRICE_WITH_TAX == 'true') $order->info['shipping_cost'] = $order->info['shipping_cost'] / (1.0 + ($quotes_array[$default_shipping]['tax'] / 100));
      $module = substr($shipping['id'], 0, strpos($shipping['id'], '_'));
      $order->info['tax'] -= tep_calculate_tax($order->info['shipping_cost'], $quotes_array[$default_shipping]['tax']);
      $order->info['tax_groups'][tep_get_tax_description($module->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id'])] -= tep_calculate_tax($order->info['shipping_cost'], $quotes_array[$default_shipping]['tax']);
      $order->info['total'] -= tep_calculate_tax($order->info['shipping_cost'], $quotes_array[$default_shipping]['tax']);

      $items_total = $paypal_pro_payflow_ec->format_raw($order->info['subtotal']);

      foreach ($order_totals as $ot) {
        if ( !in_array($ot['code'], array('ot_subtotal', 'ot_shipping', 'ot_tax', 'ot_total')) ) {
          $item_params['L_NAME' . $line_item_no] = $ot['title'];
          $item_params['L_COST' . $line_item_no] = $paypal_pro_payflow_ec->format_raw($ot['value']);
          $item_params['L_QTY' . $line_item_no] = 1;

          $items_total += $paypal_pro_payflow_ec->format_raw($ot['value']);

          $line_item_no++;
        }
      }

      $params['AMT'] = $paypal_pro_payflow_ec->format_raw($order->info['total']);

      $item_params['MAXAMT'] = $paypal_pro_payflow_ec->format_raw($params['AMT'] + $expensive_rate + 100, '', 1); // safely pad higher for dynamic shipping rates (eg, USPS express)
      $item_params['ITEMAMT'] = $items_total;
      $item_params['FREIGHTAMT'] = $paypal_pro_payflow_ec->format_raw($order->info['shipping_cost']);

      $paypal_item_total = $item_params['ITEMAMT'] + $item_params['FREIGHTAMT'];

      if ( DISPLAY_PRICE_WITH_TAX == 'false' ) {
        $item_params['TAXAMT'] = $paypal_pro_payflow_ec->format_raw($order->info['tax']);

        $paypal_item_total += $item_params['TAXAMT'];
      }

      if ( $paypal_pro_payflow_ec->format_raw($paypal_item_total) == $params['AMT'] ) {
        $params = array_merge($params, $item_params);
      }

      if (tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PAGE_STYLE)) {
        $params['PAGESTYLE'] = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PAGE_STYLE;
      }

      $ppeuk_secret = tep_create_random_value(16, 'digits');

      if ( !tep_session_is_registered('ppeuk_secret') ) {
        tep_session_register('ppeuk_secret');
      }

      $params['CUSTOM'] = $ppeuk_secret;

      $response_array = $paypal_pro_payflow_ec->setExpressCheckout($params);

      if ($response_array['RESULT'] == '0') {
        tep_redirect($paypal_url . '&token=' . $response_array['TOKEN'] /*. '&useraction=commit'*/);
      } else {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'error_message=' . urlencode($response_array['OSCOM_ERROR_MESSAGE']), 'SSL'));
      }

      break;
  }

  tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
