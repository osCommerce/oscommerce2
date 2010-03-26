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

  require(DIR_WS_LANGUAGES . $language . '/modules/payment/paypal_express.php');
  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CREATE_ACCOUNT);

// initialize variables if the customer is not logged in
  if (!tep_session_is_registered('customer_id')) {
    $customer_id = 0;
    $customer_default_address_id = 0;
  }

  require('includes/modules/payment/paypal_express.php');
  $paypal_express = new paypal_express();

  if (!$paypal_express->check() || !$paypal_express->enabled) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
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

  switch ($HTTP_GET_VARS['osC_Action']) {
    case 'cancel':
      tep_session_unregister('ppe_token');

      tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));

      break;
    case 'callbackSet':
      if (MODULE_PAYMENT_PAYPAL_EXPRESS_INSTANT_UPDATE == 'True') {
        $counter = 0;

        while (true) {
          if (isset($HTTP_POST_VARS['L_NUMBER' . $counter])) {
            $cart->add_cart($HTTP_POST_VARS['L_NUMBER' . $counter], $HTTP_POST_VARS['L_QTY' . $counter]);
          } else {
            break;
          }

          $counter++;
        }

// exit if there is nothing in the shopping cart
        if ($cart->count_contents() < 1) {
          exit;
        }

        $sendto = array('firstname' => '',
                        'lastname' => '',
                        'company' => '',
                        'street_address' => '',
                        'suburb' => '',
                        'postcode' => $HTTP_POST_VARS['SHIPTOZIP'],
                        'city' => $HTTP_POST_VARS['SHIPTOCITY'],
                        'zone_id' => '',
                        'zone_name' => $HTTP_POST_VARS['SHIPTOSTATE'],
                        'country_id' => '',
                        'country_name' => $HTTP_POST_VARS['SHIPTOCOUNTRY'],
                        'country_iso_code_2' => '',
                        'country_iso_code_3' => '',
                        'address_format_id' => '');

        $country_query = tep_db_query("select * from " . TABLE_COUNTRIES . " where countries_iso_code_2 = '" . tep_db_input($sendto['country_name']) . "' limit 1");
        if (tep_db_num_rows($country_query)) {
          $country = tep_db_fetch_array($country_query);

          $sendto['country_id'] = $country['countries_id'];
          $sendto['country_name'] = $country['countries_name'];
          $sendto['country_iso_code_2'] = $country['countries_iso_code_2'];
          $sendto['country_iso_code_3'] = $country['countries_iso_code_3'];
          $sendto['address_format_id'] = $country['address_format_id'];
        }

        if ($sendto['country_id'] > 0) {
          $zone_query = tep_db_query("select * from " . TABLE_ZONES . " where zone_country_id = '" . (int)$sendto['country_id'] . "' and (zone_name = '" . tep_db_input($sendto['zone_name']) . "' or zone_code = '" . tep_db_input($sendto['zone_name']) . "') limit 1");
          if (tep_db_num_rows($zone_query)) {
            $zone = tep_db_fetch_array($zone_query);

            $sendto['zone_id'] = $zone['zone_id'];
            $sendto['zone_name'] = $zone['zone_name'];
          }
        }

        $billto = $sendto;

        $quotes_array = array();

        include(DIR_WS_CLASSES . 'order.php');

        if ($cart->get_content_type() != 'virtual') {
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

          if ( (tep_count_shipping_modules() > 0) || ($free_shipping == true) ) {
            if ($free_shipping == true) {
              $quotes_array[] = array('id' => 'free_free',
                                      'name' => FREE_SHIPPING_TITLE,
                                      'label' => FREE_SHIPPING_TITLE,
                                      'cost' => '0',
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
                                            'tax' => isset($quote['tax']) ? $quote['tax'] : '0');
                  }
                }
              }
            }
          }
        } else {
          $quotes_array[] = array('id' => 'null',
                                  'name' => 'No Shipping',
                                  'label' => 'No Shipping',
                                  'cost' => '0',
                                  'tax' => '0');
        }

        $params = array('METHOD' => 'CallbackResponse',
                        'OFFERINSURANCEOPTION' => 'false');

        $counter = 0;
        $cheapest_rate = null;
        $cheapest_counter = $counter;

        foreach ($quotes_array as $quote) {
          $shipping_rate = $paypal_express->format_raw($quote['cost'] + tep_calculate_tax($quote['cost'], $quote['tax']));

          $params['L_SHIPPINGOPTIONNAME' . $counter] = $quote['name'] . ' (' . $quote['label'] . ')';
          $params['L_SHIPINGPOPTIONLABEL' . $counter] = $quote['name'] . ' (' . $quote['label'] . ')';
          $params['L_SHIPPINGOPTIONAMOUNT' . $counter] = $paypal_express->format_raw($quote['cost']);
          $params['L_SHIPPINGOPTIONISDEFAULT' . $counter] = 'false';
          $params['L_TAXAMT' . $counter] = $paypal_express->format_raw($order->info['tax'] + tep_calculate_tax($quote['cost'], $quote['tax']));

          if (is_null($cheapest_rate) || ($shipping_rate < $cheapest_rate)) {
            $cheapest_rate = $shipping_rate;
            $cheapest_counter = $counter;
          }

          $counter++;
        }

        $params['L_SHIPPINGOPTIONISDEFAULT' . $cheapest_counter] = 'true';

        $post_string = '';

        foreach ($params as $key => $value) {
          $post_string .= $key . '=' . urlencode(utf8_encode(trim($value))) . '&';
        }

        $post_string = substr($post_string, 0, -1);

        echo $post_string;
      }

      exit;

      break;
    case 'retrieve':
// if there is nothing in the customers cart, redirect them to the shopping cart page
      if ($cart->count_contents() < 1) {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
      }

      $response_array = $paypal_express->getExpressCheckoutDetails($HTTP_GET_VARS['token']);

      if (($response_array['ACK'] == 'Success') || ($response_array['ACK'] == 'SuccessWithWarning')) {
        $force_login = false;

// check if e-mail address exists in database and login or create customer account
        if (!tep_session_is_registered('customer_id')) {
          $force_login = true;

          $email_address = tep_db_prepare_input($response_array['EMAIL']);

          $check_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' limit 1");
          if (tep_db_num_rows($check_query)) {
            $check = tep_db_fetch_array($check_query);

            $customer_id = $check['customers_id'];
            $customers_firstname = $check['customers_firstname'];
            $customer_default_address_id = $check['customers_default_address_id'];
          } else {
            $customers_firstname = tep_db_prepare_input($response_array['FIRSTNAME']);
            $customers_lastname = tep_db_prepare_input($response_array['LASTNAME']);

            $customer_password = tep_create_random_value(max(ENTRY_PASSWORD_MIN_LENGTH, 8));

            $sql_data_array = array('customers_firstname' => $customers_firstname,
                                    'customers_lastname' => $customers_lastname,
                                    'customers_email_address' => $email_address,
                                    'customers_telephone' => '',
                                    'customers_fax' => '',
                                    'customers_newsletter' => '0',
                                    'customers_password' => tep_encrypt_password($customer_password));

            if (isset($response_array['PHONENUM']) && tep_not_null($response_array['PHONENUM'])) {
              $customers_telephone = tep_db_prepare_input($response_array['PHONENUM']);

              $sql_data_array['customers_telephone'] = $customers_telephone;
            }

            tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);

            $customer_id = tep_db_insert_id();

            tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int)$customer_id . "', '0', now())");

// build the message content
            $name = $customers_firstname . ' ' . $customers_lastname;
            $email_text = sprintf(EMAIL_GREET_NONE, $customers_firstname) . EMAIL_WELCOME . sprintf(MODULE_PAYMENT_PAYPAL_EXPRESS_EMAIL_PASSWORD, $email_address, $customer_password) . EMAIL_TEXT . EMAIL_CONTACT . EMAIL_WARNING;
            tep_mail($name, $email_address, EMAIL_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
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
        $ship_country = tep_db_prepare_input($response_array['SHIPTOCOUNTRYCODE']);
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

        if ($force_login == true) {
          $customer_country_id = $ship_country_id;
          $customer_zone_id = $ship_zone_id;
          tep_session_register('customer_default_address_id');
          tep_session_register('customer_country_id');
          tep_session_register('customer_zone_id');

          $billto = $sendto;
        }

        include(DIR_WS_CLASSES . 'order.php');

        if ($cart->get_content_type() != 'virtual') {
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

              $shipping_set = false;

// if available, set the selected shipping rate from PayPals order review page
              if (isset($response_array['SHIPPINGOPTIONNAME']) && isset($response_array['SHIPPINGOPTIONAMOUNT'])) {
                foreach ($quotes as $quote) {
                  if (!isset($quote['error'])) {
                    foreach ($quote['methods'] as $rate) {
                      if ($response_array['SHIPPINGOPTIONNAME'] == $quote['module'] . ' (' . $rate['title'] . ')') {
                        if ($response_array['SHIPPINGOPTIONAMOUNT'] == $paypal_express->format_raw($rate['cost'] + tep_calculate_tax($rate['cost'], $quote['tax']))) {
                          $shipping = $quote['id'] . '_' . $rate['id'];
                          $shipping_set = true;
                          break 2;
                        }
                      }
                    }
                  }
                }
              }

              if ($shipping_set == false) {
// select cheapest shipping method
                $shipping = $shipping_modules->cheapest();
                $shipping = $shipping['id'];
              }
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

        if (!tep_session_is_registered('payment')) tep_session_register('payment');
        $payment = $paypal_express->code;

        if (!tep_session_is_registered('ppe_token')) tep_session_register('ppe_token');
        $ppe_token = $response_array['TOKEN'];

        if (!tep_session_is_registered('ppe_payerid')) tep_session_register('ppe_payerid');
        $ppe_payerid = $response_array['PAYERID'];

        if (!tep_session_is_registered('ppe_payerstatus')) tep_session_register('ppe_payerstatus');
        $ppe_payerstatus = $response_array['PAYERSTATUS'];

        if (!tep_session_is_registered('ppe_addressstatus')) tep_session_register('ppe_addressstatus');
        $ppe_addressstatus = $response_array['ADDRESSSTATUS'];

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'));
      } else {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'error_message=' . stripslashes($response_array['L_LONGMESSAGE0']), 'SSL'));
      }

      break;

    default:
// if there is nothing in the customers cart, redirect them to the shopping cart page
      if ($cart->count_contents() < 1) {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
      }

      if (MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER == 'Live') {
        $paypal_url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout';
      } else {
        $paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout';
      }

      include(DIR_WS_CLASSES . 'order.php');
      $order = new order;

      $params = array('CURRENCYCODE' => $order->info['currency']);

// A billing address is required for digital orders so we use the shipping address PayPal provides
//      if ($order->content_type == 'virtual') {
//        $params['NOSHIPPING'] = '1';
//      }

      $line_item_no = 0;
      $items_total = 0;
      $tax_total = 0;

      foreach ($order->products as $product) {
        $params['L_NAME' . $line_item_no] = $product['name'];
        $params['L_AMT' . $line_item_no] = $paypal_express->format_raw($product['final_price']);
        $params['L_NUMBER' . $line_item_no] = $product['id'];
        $params['L_QTY' . $line_item_no] = $product['qty'];

        $product_tax = tep_calculate_tax($product['final_price'], $product['tax']);

        $params['L_TAXAMT' . $line_item_no] = $paypal_express->format_raw($product_tax);
        $tax_total += $paypal_express->format_raw($product_tax) * $product['qty'];

        $items_total += $paypal_express->format_raw($product['final_price']) * $product['qty'];

        $line_item_no++;
      }

      $params['ITEMAMT'] = $items_total;
      $params['TAXAMT'] = $tax_total;

      if (tep_not_null($order->delivery['firstname'])) {
        $params['ADDROVERRIDE'] = '1';
        $params['SHIPTONAME'] = $order->delivery['firstname'] . ' ' . $order->delivery['lastname'];
        $params['SHIPTOSTREET'] = $order->delivery['street_address'];
        $params['SHIPTOCITY'] = $order->delivery['city'];
        $params['SHIPTOSTATE'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
        $params['SHIPTOCOUNTRYCODE'] = $order->delivery['country']['iso_code_2'];
        $params['SHIPTOZIP'] = $order->delivery['postcode'];
      }

      $quotes_array = array();

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
                                    'label' => FREE_SHIPPING_TITLE,
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
        }
      }

      $counter = 0;
      $cheapest_rate = null;
      $expensive_rate = 0;
      $cheapest_counter = $counter;
      $default_shipping = null;

      foreach ($quotes_array as $quote) {
        $shipping_rate = $paypal_express->format_raw($quote['cost'] + tep_calculate_tax($quote['cost'], $quote['tax']));

        $params['L_SHIPPINGOPTIONNAME' . $counter] = $quote['name'] . ' (' . $quote['label'] . ')';
        $params['L_SHIPINGPOPTIONLABEL' . $counter] = $quote['name'] . ' (' . $quote['label'] . ')';
        $params['L_SHIPPINGOPTIONAMOUNT' . $counter] = $shipping_rate;
        $params['L_SHIPPINGOPTIONISDEFAULT' . $counter] = 'false';

        if (is_null($cheapest_rate) || ($shipping_rate < $cheapest_rate)) {
          $cheapest_rate = $shipping_rate;
          $cheapest_counter = $counter;
        }

        if ($shipping_rate > $expensive_rate) {
          $expensive_rate = $shipping_rate;
        }

        if (tep_not_null($shipping) && ($shipping['id'] == $quote['id'])) {
          $default_shipping = $counter;
        }

        $counter++;
      }

      if (!is_null($default_shipping)) {
        $cheapest_rate = $params['L_SHIPPINGOPTIONAMOUNT' . $default_shipping];
        $cheapest_counter = $default_shipping;
      }

      if (!is_null($cheapest_rate)) {
        if ( (MODULE_PAYMENT_PAYPAL_EXPRESS_INSTANT_UPDATE == 'True') && ((MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER != 'Live') || ((MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER == 'Live') && (ENABLE_SSL == true))) ) { // Live server requires SSL to be enabled
          $params['CALLBACK'] = tep_href_link('ext/modules/payment/paypal/express.php', 'osC_Action=callbackSet', 'SSL', false, false);
          $params['CALLBACKTIMEOUT'] = '5';
        }

        $params['INSURANCEOPTIONSOFFERED'] = 'false';
        $params['L_SHIPPINGOPTIONISDEFAULT' . $cheapest_counter] = 'true';
      }

// don't recalculate currency values as they have already been calculated
      $params['SHIPPINGAMT'] = $paypal_express->format_raw($cheapest_rate, '', 1);
      $params['AMT'] = $paypal_express->format_raw($params['ITEMAMT'] + $params['TAXAMT'] + $params['SHIPPINGAMT'], '', 1);
      $params['MAXAMT'] = $paypal_express->format_raw($params['AMT'] + $expensive_rate + 100, '', 1); // safely pad higher for dynamic shipping rates (eg, USPS express)

      $response_array = $paypal_express->setExpressCheckout($params);

      if (($response_array['ACK'] == 'Success') || ($response_array['ACK'] == 'SuccessWithWarning')) {
        tep_redirect($paypal_url . '&token=' . $response_array['TOKEN'] . '&useraction=commit');
      } else {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'error_message=' . stripslashes($response_array['L_LONGMESSAGE0']), 'SSL'));
      }

      break;
  }

  tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
