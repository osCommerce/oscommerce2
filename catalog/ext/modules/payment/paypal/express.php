<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/paypal_express.php');
  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/account.php');

// initialize variables if the customer is not logged in
  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['customer_id'] = 0;
    $_SESSION['customer_default_address_id'] = 0;
  }

  require('includes/modules/payment/paypal_express.php');
  $paypal_express = new paypal_express();

  if (!$paypal_express->check() || !$paypal_express->enabled) {
    osc_redirect(osc_href_link('cart'));
  }

  if (!isset($_SESSION['sendto'])) {
    $_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
  }

  if (!isset($_SESSION['billto'])) {
    $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
  }

// register a random ID in the session to check throughout the checkout procedure
// against alterations in the shopping cart contents
  $_SESSION['cartID'] = $_SESSION['cart']->cartID;

  switch ($_GET['osC_Action']) {
    case 'cancel':
      unset($_SESSION['ppe_token']);

      osc_redirect(osc_href_link('cart', '', 'SSL'));

      break;
    case 'callbackSet':
      if (MODULE_PAYMENT_PAYPAL_EXPRESS_INSTANT_UPDATE == 'True') {
        $counter = 0;

        while (true) {
          if (isset($_POST['L_NUMBER' . $counter])) {
            $_SESSION['cart']->add_cart($_POST['L_NUMBER' . $counter], $_POST['L_QTY' . $counter]);
          } else {
            break;
          }

          $counter++;
        }

// exit if there is nothing in the shopping cart
        if ($_SESSION['cart']->count_contents() < 1) {
          exit;
        }

        $_SESSION['sendto'] = array('firstname' => '',
                                    'lastname' => '',
                                    'company' => '',
                                    'street_address' => '',
                                    'suburb' => '',
                                    'postcode' => $_POST['SHIPTOZIP'],
                                    'city' => $_POST['SHIPTOCITY'],
                                    'zone_id' => '',
                                    'zone_name' => $_POST['SHIPTOSTATE'],
                                    'country_id' => '',
                                    'country_name' => $_POST['SHIPTOCOUNTRY'],
                                    'country_iso_code_2' => '',
                                    'country_iso_code_3' => '',
                                    'address_format_id' => '');

        $country_query = osc_db_query("select * from " . TABLE_COUNTRIES . " where countries_iso_code_2 = '" . osc_db_input($_SESSION['sendto']['country_name']) . "' limit 1");
        if (osc_db_num_rows($country_query)) {
          $country = osc_db_fetch_array($country_query);

          $_SESSION['sendto']['country_id'] = $country['countries_id'];
          $_SESSION['sendto']['country_name'] = $country['countries_name'];
          $_SESSION['sendto']['country_iso_code_2'] = $country['countries_iso_code_2'];
          $_SESSION['sendto']['country_iso_code_3'] = $country['countries_iso_code_3'];
          $_SESSION['sendto']['address_format_id'] = $country['address_format_id'];
        }

        if ($_SESSION['sendto']['country_id'] > 0) {
          $zone_query = osc_db_query("select * from " . TABLE_ZONES . " where zone_country_id = '" . (int)$_SESSION['sendto']['country_id'] . "' and (zone_name = '" . osc_db_input($_SESSION['sendto']['zone_name']) . "' or zone_code = '" . osc_db_input($_SESSION['sendto']['zone_name']) . "') limit 1");
          if (osc_db_num_rows($zone_query)) {
            $zone = osc_db_fetch_array($zone_query);

            $_SESSION['sendto']['zone_id'] = $zone['zone_id'];
            $_SESSION['sendto']['zone_name'] = $zone['zone_name'];
          }
        }

        $_SESSION['billto'] = $_SESSION['sendto'];

        $quotes_array = array();

        include(DIR_WS_CLASSES . 'order.php');

        if ($_SESSION['cart']->get_content_type() != 'virtual') {
          $order = new order;

          $total_weight = $_SESSION['cart']->show_weight();
          $total_count = $_SESSION['cart']->count_contents();

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

              include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/order_total/ot_shipping.php');
            }
          }

          if ( (osc_count_shipping_modules() > 0) || ($free_shipping == true) ) {
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
          $shipping_rate = $paypal_express->format_raw($quote['cost'] + osc_calculate_tax($quote['cost'], $quote['tax']));

          $params['L_SHIPPINGOPTIONNAME' . $counter] = $quote['name'] . ' (' . $quote['label'] . ')';
          $params['L_SHIPINGPOPTIONLABEL' . $counter] = $quote['name'] . ' (' . $quote['label'] . ')';
          $params['L_SHIPPINGOPTIONAMOUNT' . $counter] = $paypal_express->format_raw($quote['cost']);
          $params['L_SHIPPINGOPTIONISDEFAULT' . $counter] = 'false';
          $params['L_TAXAMT' . $counter] = $paypal_express->format_raw($order->info['tax'] + osc_calculate_tax($quote['cost'], $quote['tax']));

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
      if ($_SESSION['cart']->count_contents() < 1) {
        osc_redirect(osc_href_link('cart'));
      }

      $response_array = $paypal_express->getExpressCheckoutDetails($_GET['token']);

      if (($response_array['ACK'] == 'Success') || ($response_array['ACK'] == 'SuccessWithWarning')) {
        $force_login = false;

// check if e-mail address exists in database and login or create customer account
        if (!isset($_SESSION['customer_id'])) {
          $force_login = true;

          $email_address = osc_db_prepare_input($response_array['EMAIL']);

          $check_query = osc_db_query("select * from " . TABLE_CUSTOMERS . " where customers_email_address = '" . osc_db_input($email_address) . "' limit 1");
          if (osc_db_num_rows($check_query)) {
            $check = osc_db_fetch_array($check_query);

            $_SESSION['customer_id'] = $check['customers_id'];
            $customers_firstname = $check['customers_firstname'];
            $_SESSION['customer_default_address_id'] = $check['customers_default_address_id'];
          } else {
            $customers_firstname = osc_db_prepare_input($response_array['FIRSTNAME']);
            $customers_lastname = osc_db_prepare_input($response_array['LASTNAME']);

            $customer_password = osc_create_random_value(max(ENTRY_PASSWORD_MIN_LENGTH, 8));

            $sql_data_array = array('customers_firstname' => $customers_firstname,
                                    'customers_lastname' => $customers_lastname,
                                    'customers_email_address' => $email_address,
                                    'customers_telephone' => '',
                                    'customers_fax' => '',
                                    'customers_newsletter' => '0',
                                    'customers_password' => osc_encrypt_password($customer_password));

            if (isset($response_array['PHONENUM']) && osc_not_null($response_array['PHONENUM'])) {
              $customers_telephone = osc_db_prepare_input($response_array['PHONENUM']);

              $sql_data_array['customers_telephone'] = $customers_telephone;
            }

            osc_db_perform(TABLE_CUSTOMERS, $sql_data_array);

            $_SESSION['customer_id'] = osc_db_insert_id();

            osc_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int)$_SESSION['customer_id'] . "', '0', now())");

// build the message content
            $name = $customers_firstname . ' ' . $customers_lastname;
            $email_text = sprintf(EMAIL_GREET_NONE, $customers_firstname) . EMAIL_WELCOME . sprintf(MODULE_PAYMENT_PAYPAL_EXPRESS_EMAIL_PASSWORD, $email_address, $customer_password) . EMAIL_TEXT . EMAIL_CONTACT . EMAIL_WARNING;
            osc_mail($name, $email_address, EMAIL_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
          }

          if (SESSION_RECREATE == 'True') {
            osc_session_recreate();
          }

          $_SESSION['customer_first_name'] = $customers_firstname;

// reset session token
          $_SESSION['sessiontoken'] = md5(osc_rand() . osc_rand() . osc_rand() . osc_rand());
        }

// check if paypal shipping address exists in the address book
        $ship_firstname = osc_db_prepare_input(substr($response_array['SHIPTONAME'], 0, strpos($response_array['SHIPTONAME'], ' ')));
        $ship_lastname = osc_db_prepare_input(substr($response_array['SHIPTONAME'], strpos($response_array['SHIPTONAME'], ' ')+1));
        $ship_address = osc_db_prepare_input($response_array['SHIPTOSTREET']);
        $ship_city = osc_db_prepare_input($response_array['SHIPTOCITY']);
        $ship_zone = osc_db_prepare_input($response_array['SHIPTOSTATE']);
        $ship_zone_id = 0;
        $ship_postcode = osc_db_prepare_input($response_array['SHIPTOZIP']);
        $ship_country = osc_db_prepare_input($response_array['SHIPTOCOUNTRYCODE']);
        $ship_country_id = 0;
        $ship_address_format_id = 1;

        $country_query = osc_db_query("select countries_id, address_format_id from " . TABLE_COUNTRIES . " where countries_iso_code_2 = '" . osc_db_input($ship_country) . "' limit 1");
        if (osc_db_num_rows($country_query)) {
          $country = osc_db_fetch_array($country_query);

          $ship_country_id = $country['countries_id'];
          $ship_address_format_id = $country['address_format_id'];
        }

        if ($ship_country_id > 0) {
          $zone_query = osc_db_query("select zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int)$ship_country_id . "' and (zone_name = '" . osc_db_input($ship_zone) . "' or zone_code = '" . osc_db_input($ship_zone) . "') limit 1");
          if (osc_db_num_rows($zone_query)) {
            $zone = osc_db_fetch_array($zone_query);

            $ship_zone_id = $zone['zone_id'];
          }
        }

        $check_query = osc_db_query("select address_book_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$_SESSION['customer_id'] . "' and entry_firstname = '" . osc_db_input($ship_firstname) . "' and entry_lastname = '" . osc_db_input($ship_lastname) . "' and entry_street_address = '" . osc_db_input($ship_address) . "' and entry_postcode = '" . osc_db_input($ship_postcode) . "' and entry_city = '" . osc_db_input($ship_city) . "' and (entry_state = '" . osc_db_input($ship_zone) . "' or entry_zone_id = '" . (int)$ship_zone_id . "') and entry_country_id = '" . (int)$ship_country_id . "' limit 1");
        if (osc_db_num_rows($check_query)) {
          $check = osc_db_fetch_array($check_query);

          $_SESSION['sendto'] = $check['address_book_id'];
        } else {
          $sql_data_array = array('customers_id' => $_SESSION['customer_id'],
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

          osc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);

          $address_id = osc_db_insert_id();

          $_SESSION['sendto'] = $address_id;

          if ($_SESSION['customer_default_address_id'] < 1) {
            osc_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int)$address_id . "' where customers_id = '" . (int)$_SESSION['customer_id'] . "'");
            $_SESSION['customer_default_address_id'] = $address_id;
          }
        }

        if ($force_login == true) {
          $_SESSION['customer_country_id'] = $ship_country_id;
          $_SESSION['customer_zone_id'] = $ship_zone_id;

          $_SESSION['billto'] = $_SESSION['sendto'];
        }

        include(DIR_WS_CLASSES . 'order.php');

        if ($_SESSION['cart']->get_content_type() != 'virtual') {
          $order = new order;

          $total_weight = $_SESSION['cart']->show_weight();
          $total_count = $_SESSION['cart']->count_contents();

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

              include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/order_total/ot_shipping.php');
            }
          }

          $_SESSION['shipping'] = false;

          if ( (osc_count_shipping_modules() > 0) || ($free_shipping == true) ) {
            if ($free_shipping == true) {
              $_SESSION['shipping'] = 'free_free';
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
                        if ($response_array['SHIPPINGOPTIONAMOUNT'] == $paypal_express->format_raw($rate['cost'] + osc_calculate_tax($rate['cost'], $quote['tax']))) {
                          $_SESSION['shipping'] = $quote['id'] . '_' . $rate['id'];
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
                $_SESSION['shipping'] = $shipping_modules->cheapest();
                $_SESSION['shipping'] = $_SESSION['shipping']['id'];
              }
            }
          }

          if (strpos($_SESSION['shipping'], '_')) {
            list($module, $method) = explode('_', $_SESSION['shipping']);

            if ( is_object($$module) || ($_SESSION['shipping'] == 'free_free') ) {
              if ($_SESSION['shipping'] == 'free_free') {
                $quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
                $quote[0]['methods'][0]['cost'] = '0';
              } else {
                $quote = $shipping_modules->quote($method, $module);
              }

              if (isset($quote['error'])) {
                unset($_SESSION['shipping']);

                osc_redirect(osc_href_link('checkout', 'shipping', 'SSL'));
              } else {
                if ( (isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost'])) ) {
                  $_SESSION['shipping'] = array('id' => $_SESSION['shipping'],
                                                'title' => (($free_shipping == true) ?  $quote[0]['methods'][0]['title'] : $quote[0]['module'] . ' (' . $quote[0]['methods'][0]['title'] . ')'),
                                                'cost' => $quote[0]['methods'][0]['cost']);
                }
              }
            }
          }
        } else {
          $_SESSION['shipping'] = false;

          $_SESSION['sendto'] = false;
        }

        $_SESSION['payment'] = $paypal_express->code;

        $_SESSION['ppe_token'] = $response_array['TOKEN'];

        $_SESSION['ppe_payerid'] = $response_array['PAYERID'];

        $_SESSION['ppe_payerstatus'] = $response_array['PAYERSTATUS'];

        $_SESSION['ppe_addressstatus'] = $response_array['ADDRESSSTATUS'];

        osc_redirect(osc_href_link('checkout', '', 'SSL'));
      } else {
        osc_redirect(osc_href_link('cart', 'error_message=' . stripslashes($response_array['L_LONGMESSAGE0']), 'SSL'));
      }

      break;

    default:
// if there is nothing in the customers cart, redirect them to the shopping cart page
      if ($_SESSION['cart']->count_contents() < 1) {
        osc_redirect(osc_href_link('cart'));
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

        $product_tax = osc_calculate_tax($product['final_price'], $product['tax']);

        $params['L_TAXAMT' . $line_item_no] = $paypal_express->format_raw($product_tax);
        $tax_total += $paypal_express->format_raw($product_tax) * $product['qty'];

        $items_total += $paypal_express->format_raw($product['final_price']) * $product['qty'];

        $line_item_no++;
      }

      $params['ITEMAMT'] = $items_total;
      $params['TAXAMT'] = $tax_total;

      if (osc_not_null($order->delivery['firstname'])) {
        $params['ADDROVERRIDE'] = '1';
        $params['SHIPTONAME'] = $order->delivery['firstname'] . ' ' . $order->delivery['lastname'];
        $params['SHIPTOSTREET'] = $order->delivery['street_address'];
        $params['SHIPTOCITY'] = $order->delivery['city'];
        $params['SHIPTOSTATE'] = osc_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
        $params['SHIPTOCOUNTRYCODE'] = $order->delivery['country']['iso_code_2'];
        $params['SHIPTOZIP'] = $order->delivery['postcode'];
      }

      $quotes_array = array();

      if ($_SESSION['cart']->get_content_type() != 'virtual') {
        $total_weight = $_SESSION['cart']->show_weight();
        $total_count = $_SESSION['cart']->count_contents();

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

            include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/order_total/ot_shipping.php');
          }
        }

        if ( (osc_count_shipping_modules() > 0) || ($free_shipping == true) ) {
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
        $shipping_rate = $paypal_express->format_raw($quote['cost'] + osc_calculate_tax($quote['cost'], $quote['tax']));

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

        if (osc_not_null($_SESSION['shipping']) && ($_SESSION['shipping']['id'] == $quote['id'])) {
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
          $params['CALLBACK'] = osc_href_link('ext/modules/payment/paypal/express.php', 'osC_Action=callbackSet', 'SSL', false, false);
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
        osc_redirect($paypal_url . '&token=' . $response_array['TOKEN'] . '&useraction=commit');
      } else {
        osc_redirect(osc_href_link('cart', 'error_message=' . stripslashes($response_array['L_LONGMESSAGE0']), 'SSL'));
      }

      break;
  }

  osc_redirect(osc_href_link('cart', '', 'SSL'));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
