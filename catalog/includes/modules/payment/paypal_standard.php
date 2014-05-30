<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class paypal_standard {
    var $code, $title, $description, $enabled;

    function paypal_standard() {
      global $HTTP_GET_VARS, $PHP_SELF, $order;

      $this->signature = 'paypal|paypal_standard|3.1|2.3';

      $this->code = 'paypal_standard';
      $this->title = MODULE_PAYMENT_PAYPAL_STANDARD_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_PAYPAL_STANDARD_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_PAYPAL_STANDARD_TEXT_DESCRIPTION;
      $this->sort_order = defined('MODULE_PAYMENT_PAYPAL_STANDARD_SORT_ORDER') ? MODULE_PAYMENT_PAYPAL_STANDARD_SORT_ORDER : 0;
      $this->enabled = defined('MODULE_PAYMENT_PAYPAL_STANDARD_STATUS') && (MODULE_PAYMENT_PAYPAL_STANDARD_STATUS == 'True') ? true : false;
      $this->order_status = defined('MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID : 0;

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_STATUS') ) {
        if ( MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER == 'Sandbox' ) {
          $this->title .= ' [Sandbox]';
          $this->public_title .= ' (' . $this->code . '; Sandbox)';
        }

        $this->description .= $this->getTestLinkInfo();

        if ( MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER == 'Live' ) {
          $this->form_action_url = 'https://www.paypal.com/cgi-bin/webscr';
        } else {
          $this->form_action_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        }
      }

      if ( !function_exists('curl_init') ) {
        $this->description = '<div class="secWarning">' . MODULE_PAYMENT_PAYPAL_STANDARD_ERROR_ADMIN_CURL . '</div>' . $this->description;

        $this->enabled = false;
      }

      if ( $this->enabled === true ) {
        if ( !tep_not_null(MODULE_PAYMENT_PAYPAL_STANDARD_ID) ) {
          $this->description = '<div class="secWarning">' . MODULE_PAYMENT_PAYPAL_STANDARD_ERROR_ADMIN_CONFIGURATION . '</div>' . $this->description;

          $this->enabled = false;
        }
      }

      if ( $this->enabled === true ) {
        if ( isset($order) && is_object($order) ) {
          $this->update_status();
        }
      }

      if ( defined('FILENAME_MODULES') && ($PHP_SELF == FILENAME_MODULES) && isset($HTTP_GET_VARS['action']) && ($HTTP_GET_VARS['action'] == 'install') && isset($HTTP_GET_VARS['subaction']) && ($HTTP_GET_VARS['subaction'] == 'conntest') ) {
        echo $this->getTestConnectionResult();
        exit;
      }
    }

    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PAYPAL_STANDARD_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PAYPAL_STANDARD_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->billing['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    function javascript_validation() {
      return false;
    }

    function selection() {
      global $cart_PayPal_Standard_ID;

      if (tep_session_is_registered('cart_PayPal_Standard_ID')) {
        $order_id = substr($cart_PayPal_Standard_ID, strpos($cart_PayPal_Standard_ID, '-')+1);

        $check_query = tep_db_query('select orders_id from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '" limit 1');

        if (tep_db_num_rows($check_query) < 1) {
          tep_db_query('delete from ' . TABLE_ORDERS . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . ' where orders_id = "' . (int)$order_id . '"');

          tep_session_unregister('cart_PayPal_Standard_ID');
        }
      }

      return array('id' => $this->code,
                   'module' => $this->public_title);
    }

    function pre_confirmation_check() {
      global $cartID, $cart, $order;

      if (empty($cart->cartID)) {
        $cartID = $cart->cartID = $cart->generate_cart_id();
      }

      if (!tep_session_is_registered('cartID')) {
        tep_session_register('cartID');
      }

      $order->info['payment_method_raw'] = $order->info['payment_method'];
      $order->info['payment_method'] = '<img src="https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-100px.png" border="0" alt="PayPal Logo" style="padding: 3px;" />';
    }

    function confirmation() {
      global $cartID, $cart_PayPal_Standard_ID, $customer_id, $languages_id, $order, $order_total_modules;

      if (tep_session_is_registered('cartID')) {
        $insert_order = false;

        if (tep_session_is_registered('cart_PayPal_Standard_ID')) {
          $order_id = substr($cart_PayPal_Standard_ID, strpos($cart_PayPal_Standard_ID, '-')+1);

          $curr_check = tep_db_query("select currency from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
          $curr = tep_db_fetch_array($curr_check);

          if ( ($curr['currency'] != $order->info['currency']) || ($cartID != substr($cart_PayPal_Standard_ID, 0, strlen($cartID))) ) {
            $check_query = tep_db_query('select orders_id from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '" limit 1');

            if (tep_db_num_rows($check_query) < 1) {
              tep_db_query('delete from ' . TABLE_ORDERS . ' where orders_id = "' . (int)$order_id . '"');
              tep_db_query('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = "' . (int)$order_id . '"');
              tep_db_query('delete from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '"');
              tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS . ' where orders_id = "' . (int)$order_id . '"');
              tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . ' where orders_id = "' . (int)$order_id . '"');
              tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . ' where orders_id = "' . (int)$order_id . '"');
            }

            $insert_order = true;
          }
        } else {
          $insert_order = true;
        }

        if ($insert_order == true) {
          $order_totals = array();
          if (is_array($order_total_modules->modules)) {
            foreach ($order_total_modules->modules as $value) {
              $class = substr($value, 0, strrpos($value, '.'));
              if ($GLOBALS[$class]->enabled) {
                for ($i=0, $n=sizeof($GLOBALS[$class]->output); $i<$n; $i++) {
                  if (tep_not_null($GLOBALS[$class]->output[$i]['title']) && tep_not_null($GLOBALS[$class]->output[$i]['text'])) {
                    $order_totals[] = array('code' => $GLOBALS[$class]->code,
                                            'title' => $GLOBALS[$class]->output[$i]['title'],
                                            'text' => $GLOBALS[$class]->output[$i]['text'],
                                            'value' => $GLOBALS[$class]->output[$i]['value'],
                                            'sort_order' => $GLOBALS[$class]->sort_order);
                  }
                }
              }
            }
          }

          if ( isset($order->info['payment_method_raw']) ) {
            $order->info['payment_method'] = $order->info['payment_method_raw'];
            unset($order->info['payment_method_raw']);
          }

          $sql_data_array = array('customers_id' => $customer_id,
                                  'customers_name' => $order->customer['firstname'] . ' ' . $order->customer['lastname'],
                                  'customers_company' => $order->customer['company'],
                                  'customers_street_address' => $order->customer['street_address'],
                                  'customers_suburb' => $order->customer['suburb'],
                                  'customers_city' => $order->customer['city'],
                                  'customers_postcode' => $order->customer['postcode'],
                                  'customers_state' => $order->customer['state'],
                                  'customers_country' => $order->customer['country']['title'],
                                  'customers_telephone' => $order->customer['telephone'],
                                  'customers_email_address' => $order->customer['email_address'],
                                  'customers_address_format_id' => $order->customer['format_id'],
                                  'delivery_name' => $order->delivery['firstname'] . ' ' . $order->delivery['lastname'],
                                  'delivery_company' => $order->delivery['company'],
                                  'delivery_street_address' => $order->delivery['street_address'],
                                  'delivery_suburb' => $order->delivery['suburb'],
                                  'delivery_city' => $order->delivery['city'],
                                  'delivery_postcode' => $order->delivery['postcode'],
                                  'delivery_state' => $order->delivery['state'],
                                  'delivery_country' => $order->delivery['country']['title'],
                                  'delivery_address_format_id' => $order->delivery['format_id'],
                                  'billing_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname'],
                                  'billing_company' => $order->billing['company'],
                                  'billing_street_address' => $order->billing['street_address'],
                                  'billing_suburb' => $order->billing['suburb'],
                                  'billing_city' => $order->billing['city'],
                                  'billing_postcode' => $order->billing['postcode'],
                                  'billing_state' => $order->billing['state'],
                                  'billing_country' => $order->billing['country']['title'],
                                  'billing_address_format_id' => $order->billing['format_id'],
                                  'payment_method' => $order->info['payment_method'],
                                  'cc_type' => $order->info['cc_type'],
                                  'cc_owner' => $order->info['cc_owner'],
                                  'cc_number' => $order->info['cc_number'],
                                  'cc_expires' => $order->info['cc_expires'],
                                  'date_purchased' => 'now()',
                                  'orders_status' => $order->info['order_status'],
                                  'currency' => $order->info['currency'],
                                  'currency_value' => $order->info['currency_value']);

          tep_db_perform(TABLE_ORDERS, $sql_data_array);

          $insert_id = tep_db_insert_id();

          for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
            $sql_data_array = array('orders_id' => $insert_id,
                                    'title' => $order_totals[$i]['title'],
                                    'text' => $order_totals[$i]['text'],
                                    'value' => $order_totals[$i]['value'],
                                    'class' => $order_totals[$i]['code'],
                                    'sort_order' => $order_totals[$i]['sort_order']);

            tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
          }

          for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
            $sql_data_array = array('orders_id' => $insert_id,
                                    'products_id' => tep_get_prid($order->products[$i]['id']),
                                    'products_model' => $order->products[$i]['model'],
                                    'products_name' => $order->products[$i]['name'],
                                    'products_price' => $order->products[$i]['price'],
                                    'final_price' => $order->products[$i]['final_price'],
                                    'products_tax' => $order->products[$i]['tax'],
                                    'products_quantity' => $order->products[$i]['qty']);

            tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);

            $order_products_id = tep_db_insert_id();

            $attributes_exist = '0';
            if (isset($order->products[$i]['attributes'])) {
              $attributes_exist = '1';
              for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
                if (DOWNLOAD_ENABLED == 'true') {
                  $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
                                       from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                       left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                       on pa.products_attributes_id=pad.products_attributes_id
                                       where pa.products_id = '" . $order->products[$i]['id'] . "'
                                       and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
                                       and pa.options_id = popt.products_options_id
                                       and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
                                       and pa.options_values_id = poval.products_options_values_id
                                       and popt.language_id = '" . $languages_id . "'
                                       and poval.language_id = '" . $languages_id . "'";
                  $attributes = tep_db_query($attributes_query);
                } else {
                  $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $order->products[$i]['id'] . "' and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $languages_id . "' and poval.language_id = '" . $languages_id . "'");
                }
                $attributes_values = tep_db_fetch_array($attributes);

                $sql_data_array = array('orders_id' => $insert_id,
                                        'orders_products_id' => $order_products_id,
                                        'products_options' => $attributes_values['products_options_name'],
                                        'products_options_values' => $attributes_values['products_options_values_name'],
                                        'options_values_price' => $attributes_values['options_values_price'],
                                        'price_prefix' => $attributes_values['price_prefix']);

                tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);

                if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {
                  $sql_data_array = array('orders_id' => $insert_id,
                                          'orders_products_id' => $order_products_id,
                                          'orders_products_filename' => $attributes_values['products_attributes_filename'],
                                          'download_maxdays' => $attributes_values['products_attributes_maxdays'],
                                          'download_count' => $attributes_values['products_attributes_maxcount']);

                  tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
                }
              }
            }
          }

          $cart_PayPal_Standard_ID = $cartID . '-' . $insert_id;
          tep_session_register('cart_PayPal_Standard_ID');
        }
      }

      return false;
    }

    function process_button() {
      global $customer_id, $order, $sendto, $currency, $cart_PayPal_Standard_ID, $shipping, $order_total_modules;

      $total_tax = $order->info['tax'];

// remove shipping tax in total tax value
      if ( isset($shipping['cost']) ) {
        $total_tax -= ($order->info['shipping_cost'] - $shipping['cost']);
      }

      $process_button_string = '';
      $parameters = array('cmd' => '_cart',
                          'upload' => '1',
                          'item_name_1' => STORE_NAME,
                          'shipping_1' => $this->format_raw($order->info['shipping_cost']),
                          'business' => MODULE_PAYMENT_PAYPAL_STANDARD_ID,
                          'amount_1' => $this->format_raw($order->info['total'] - $order->info['shipping_cost'] - $total_tax),
                          'currency_code' => $currency,
                          'invoice' => substr($cart_PayPal_Standard_ID, strpos($cart_PayPal_Standard_ID, '-')+1),
                          'custom' => $customer_id,
                          'no_note' => '1',
                          'notify_url' => tep_href_link('ext/modules/payment/paypal/standard_ipn.php', '', 'SSL', false, false),
                          'rm' => '2',
                          'return' => tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'),
                          'cancel_return' => tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'),
                          'bn' => 'OSCOM23_PS',
                          'paymentaction' => ((MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTION_METHOD == 'Sale') ? 'sale' : 'authorization'));

      if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_TEXT_PAYPAL_RETURN_BUTTON') && tep_not_null(MODULE_PAYMENT_PAYPAL_STANDARD_TEXT_PAYPAL_RETURN_BUTTON) && (strlen(MODULE_PAYMENT_PAYPAL_STANDARD_TEXT_PAYPAL_RETURN_BUTTON) <= 60)) {
        $parameters['cbt'] = MODULE_PAYMENT_PAYPAL_STANDARD_TEXT_PAYPAL_RETURN_BUTTON;
      }

      if (is_numeric($sendto) && ($sendto > 0)) {
        $parameters['address_override'] = '1';
        $parameters['first_name'] = $order->delivery['firstname'];
        $parameters['last_name'] = $order->delivery['lastname'];
        $parameters['address1'] = $order->delivery['street_address'];
        $parameters['city'] = $order->delivery['city'];
        $parameters['state'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
        $parameters['zip'] = $order->delivery['postcode'];
        $parameters['country'] = $order->delivery['country']['iso_code_2'];
      } else {
        $parameters['no_shipping'] = '1';
        $parameters['first_name'] = $order->billing['firstname'];
        $parameters['last_name'] = $order->billing['lastname'];
        $parameters['address1'] = $order->billing['street_address'];
        $parameters['city'] = $order->billing['city'];
        $parameters['state'] = tep_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], $order->billing['state']);
        $parameters['zip'] = $order->billing['postcode'];
        $parameters['country'] = $order->billing['country']['iso_code_2'];
      }

      if (tep_not_null(MODULE_PAYMENT_PAYPAL_STANDARD_PAGE_STYLE)) {
        $parameters['page_style'] = MODULE_PAYMENT_PAYPAL_STANDARD_PAGE_STYLE;
      }

      $item_params = array();

      $line_item_no = 1;

      foreach ($order->products as $product) {
        if ( DISPLAY_PRICE_WITH_TAX == 'true' ) {
          $product_price = $this->format_raw($product['final_price'] + tep_calculate_tax($product['final_price'], $product['tax']));
        } else {
          $product_price = $this->format_raw($product['final_price']);
        }

        $item_params['item_name_' . $line_item_no] = $product['name'];
        $item_params['amount_' . $line_item_no] = $product_price;
        $item_params['quantity_' . $line_item_no] = $product['qty'];

        $line_item_no++;
      }

      $items_total = $this->format_raw($order->info['subtotal']);

      $has_negative_price = false;

// order totals are processed on checkout confirmation but not captured into a variable
      if (is_array($order_total_modules->modules)) {
        foreach ($order_total_modules->modules as $value) {
          $class = substr($value, 0, strrpos($value, '.'));

          if ($GLOBALS[$class]->enabled) {
            for ($i=0, $n=sizeof($GLOBALS[$class]->output); $i<$n; $i++) {
              if (tep_not_null($GLOBALS[$class]->output[$i]['title']) && tep_not_null($GLOBALS[$class]->output[$i]['text'])) {
                if ( !in_array($GLOBALS[$class]->code, array('ot_subtotal', 'ot_shipping', 'ot_tax', 'ot_total')) ) {
                  $item_params['item_name_' . $line_item_no] = $GLOBALS[$class]->output[$i]['title'];
                  $item_params['amount_' . $line_item_no] = $this->format_raw($GLOBALS[$class]->output[$i]['value']);

                  $items_total += $item_params['amount_' . $line_item_no];

                  if ( $item_params['amount_' . $line_item_no] < 0 ) {
                    $has_negative_price = true;
                  }

                  $line_item_no++;
                }
              }
            }
          }
        }
      }

      $paypal_item_total = $items_total + $parameters['shipping_1'];

      if ( DISPLAY_PRICE_WITH_TAX == 'false' ) {
        $item_params['tax_cart'] = $this->format_raw($total_tax);

        $paypal_item_total += $item_params['tax_cart'];
      }

      if ( ($has_negative_price == false) && ($this->format_raw($paypal_item_total) == $this->format_raw($order->info['total'])) ) {
        $parameters = array_merge($parameters, $item_params);
      } else {
        $parameters['tax_cart'] = $this->format_raw($total_tax);
      }

      if (MODULE_PAYMENT_PAYPAL_STANDARD_EWP_STATUS == 'True') {
        $parameters['cert_id'] = MODULE_PAYMENT_PAYPAL_STANDARD_EWP_CERT_ID;

        $random_string = rand(100000, 999999) . '-' . $customer_id . '-';

        $data = '';
        foreach ($parameters as $key => $value) {
          $data .= $key . '=' . $value . "\n";
        }

        $fp = fopen(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt', 'w');
        fwrite($fp, $data);
        fclose($fp);

        unset($data);

        if (function_exists('openssl_pkcs7_sign') && function_exists('openssl_pkcs7_encrypt')) {
          openssl_pkcs7_sign(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt', MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt', file_get_contents(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PUBLIC_KEY), file_get_contents(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PRIVATE_KEY), array('From' => MODULE_PAYMENT_PAYPAL_STANDARD_ID), PKCS7_BINARY);

          unlink(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt');

// remove headers from the signature
          $signed = file_get_contents(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt');
          $signed = explode("\n\n", $signed);
          $signed = base64_decode($signed[1]);

          $fp = fopen(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt', 'w');
          fwrite($fp, $signed);
          fclose($fp);

          unset($signed);

          openssl_pkcs7_encrypt(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt', MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt', file_get_contents(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PAYPAL_KEY), array('From' => MODULE_PAYMENT_PAYPAL_STANDARD_ID), PKCS7_BINARY);

          unlink(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt');

// remove headers from the encrypted result
          $data = file_get_contents(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt');
          $data = explode("\n\n", $data);
          $data = '-----BEGIN PKCS7-----' . "\n" . $data[1] . "\n" . '-----END PKCS7-----';

          unlink(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt');
        } else {
          exec(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_OPENSSL . ' smime -sign -in ' . MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt -signer ' . MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PUBLIC_KEY . ' -inkey ' . MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PRIVATE_KEY . ' -outform der -nodetach -binary > ' . MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt');
          unlink(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt');

          exec(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_OPENSSL . ' smime -encrypt -des3 -binary -outform pem ' . MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PAYPAL_KEY . ' < ' . MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt > ' . MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt');
          unlink(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt');

          $fh = fopen(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt', 'rb');
          $data = fread($fh, filesize(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt'));
          fclose($fh);

          unlink(MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt');
        }

        $process_button_string = tep_draw_hidden_field('cmd', '_s-xclick') .
                                 tep_draw_hidden_field('encrypted', $data);

        unset($data);
      } else {
        foreach ($parameters as $key => $value) {
          $process_button_string .= tep_draw_hidden_field($key, $value);
        }
      }

      return $process_button_string;
    }

    function before_process() {
      global $customer_id, $order, $order_totals, $sendto, $billto, $languages_id, $payment, $currencies, $cart, $cart_PayPal_Standard_ID, $$payment, $HTTP_GET_VARS, $HTTP_POST_VARS, $messageStack;

      $result = false;

      if ( isset($HTTP_POST_VARS['receiver_email']) && (($HTTP_POST_VARS['receiver_email'] == MODULE_PAYMENT_PAYPAL_STANDARD_ID) || (defined('MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID') && tep_not_null(MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID) && ($HTTP_POST_VARS['receiver_email'] == MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID))) ) {
        $parameters = 'cmd=_notify-validate';

        foreach ($HTTP_POST_VARS as $key => $value) {
          $parameters .= '&' . $key . '=' . urlencode(stripslashes($value));
        }

        $result = $this->sendTransactionToGateway($this->form_action_url, $parameters);
      }

      if ($result != 'VERIFIED') {
        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_TEXT_INVALID_TRANSACTION')) {
          $messageStack->add_session('header', MODULE_PAYMENT_PAYPAL_STANDARD_TEXT_INVALID_TRANSACTION);
        }

        $this->sendDebugEmail($result);

        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
      }

      $this->verifyTransaction();

      $order_id = substr($cart_PayPal_Standard_ID, strpos($cart_PayPal_Standard_ID, '-')+1);

      $check_query = tep_db_query("select orders_status from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "' and customers_id = '" . (int)$customer_id . "'");

      if (!tep_db_num_rows($check_query) || ($order_id != $HTTP_POST_VARS['invoice']) || ($customer_id != $HTTP_POST_VARS['custom'])) {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
      }

      $check = tep_db_fetch_array($check_query);

      $new_order_status = DEFAULT_ORDERS_STATUS_ID;

      if ( $check['orders_status'] != MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID ) {
        $new_order_status = $check['orders_status'];
      }

      if ( (MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID > 0) && ($check['orders_status'] == MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID) ) {
        $new_order_status = MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID;
      }

      tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . (int)$new_order_status . "', last_modified = now() where orders_id = '" . (int)$order_id . "'");

      $sql_data_array = array('orders_id' => $order_id,
                              'orders_status_id' => (int)$new_order_status,
                              'date_added' => 'now()',
                              'customer_notified' => (SEND_EMAILS == 'true') ? '1' : '0',
                              'comments' => $order->info['comments']);

      tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

// initialized for the email confirmation
      $products_ordered = '';
      $subtotal = 0;
      $total_tax = 0;

      for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
// Stock Update - Joao Correia
        if (STOCK_LIMITED == 'true') {
          if (DOWNLOAD_ENABLED == 'true') {
            $stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
                                FROM " . TABLE_PRODUCTS . " p
                                LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                ON p.products_id=pa.products_id
                                LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                ON pa.products_attributes_id=pad.products_attributes_id
                                WHERE p.products_id = '" . tep_get_prid($order->products[$i]['id']) . "'";
// Will work with only one option for downloadable products
// otherwise, we have to build the query dynamically with a loop
            $products_attributes = $order->products[$i]['attributes'];
            if (is_array($products_attributes)) {
              $stock_query_raw .= " AND pa.options_id = '" . $products_attributes[0]['option_id'] . "' AND pa.options_values_id = '" . $products_attributes[0]['value_id'] . "'";
            }
            $stock_query = tep_db_query($stock_query_raw);
          } else {
            $stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
          }
          if (tep_db_num_rows($stock_query) > 0) {
            $stock_values = tep_db_fetch_array($stock_query);
// do not decrement quantities if products_attributes_filename exists
            if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename'])) {
              $stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
            } else {
              $stock_left = $stock_values['products_quantity'];
            }
            tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . $stock_left . "' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
            if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {
              tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '0' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
            }
          }
        }

// Update products_ordered (for bestsellers list)
        tep_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $order->products[$i]['qty']) . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");

//------insert customer choosen option to order--------
        $attributes_exist = '0';
        $products_ordered_attributes = '';
        if (isset($order->products[$i]['attributes'])) {
          $attributes_exist = '1';
          for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
            if (DOWNLOAD_ENABLED == 'true') {
              $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
                                   from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                   left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                   on pa.products_attributes_id=pad.products_attributes_id
                                   where pa.products_id = '" . $order->products[$i]['id'] . "'
                                   and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
                                   and pa.options_id = popt.products_options_id
                                   and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
                                   and pa.options_values_id = poval.products_options_values_id
                                   and popt.language_id = '" . $languages_id . "'
                                   and poval.language_id = '" . $languages_id . "'";
              $attributes = tep_db_query($attributes_query);
            } else {
              $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $order->products[$i]['id'] . "' and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $languages_id . "' and poval.language_id = '" . $languages_id . "'");
            }
            $attributes_values = tep_db_fetch_array($attributes);

            $products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
          }
        }
//------insert customer choosen option eof ----
        $total_weight += ($order->products[$i]['qty'] * $order->products[$i]['weight']);
        $total_tax += tep_calculate_tax($total_products_price, $products_tax) * $order->products[$i]['qty'];
        $total_cost += $total_products_price;

        $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "\n";
      }

// lets start with the email confirmation
      $email_order = STORE_NAME . "\n" .
                     EMAIL_SEPARATOR . "\n" .
                     EMAIL_TEXT_ORDER_NUMBER . ' ' . $order_id . "\n" .
                     EMAIL_TEXT_INVOICE_URL . ' ' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $order_id, 'SSL', false) . "\n" .
                     EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n\n";
      if ($order->info['comments']) {
        $email_order .= tep_db_output($order->info['comments']) . "\n\n";
      }
      $email_order .= EMAIL_TEXT_PRODUCTS . "\n" .
                      EMAIL_SEPARATOR . "\n" .
                      $products_ordered .
                      EMAIL_SEPARATOR . "\n";

      for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
        $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
      }

      if ($order->content_type != 'virtual') {
        $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
                        EMAIL_SEPARATOR . "\n" .
                        tep_address_label($customer_id, $sendto, 0, '', "\n") . "\n";
      }

      $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
                      EMAIL_SEPARATOR . "\n" .
                      tep_address_label($customer_id, $billto, 0, '', "\n") . "\n\n";

      if (is_object($$payment)) {
        $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
                        EMAIL_SEPARATOR . "\n";
        $payment_class = $$payment;
        $email_order .= $payment_class->title . "\n\n";
        if ($payment_class->email_footer) {
          $email_order .= $payment_class->email_footer . "\n\n";
        }
      }

      tep_mail($order->customer['firstname'] . ' ' . $order->customer['lastname'], $order->customer['email_address'], EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

// send emails to other people
      if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
        tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
      }

// load the after_process function from the payment modules
      $this->after_process();

      $cart->reset(true);

// unregister session variables used during checkout
      tep_session_unregister('sendto');
      tep_session_unregister('billto');
      tep_session_unregister('shipping');
      tep_session_unregister('payment');
      tep_session_unregister('comments');

      tep_session_unregister('cart_PayPal_Standard_ID');

      tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
    }

    function after_process() {
      return false;
    }

    function get_error() {
      return false;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYPAL_STANDARD_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install($parameter = null) {
      $params = $this->getParams();

      if (isset($parameter)) {
        if (isset($params[$parameter])) {
          $params = array($parameter => $params[$parameter]);
        } else {
          $params = array();
        }
      }

      foreach ($params as $key => $data) {
        $sql_data_array = array('configuration_title' => $data['title'],
                                'configuration_key' => $key,
                                'configuration_value' => (isset($data['value']) ? $data['value'] : ''),
                                'configuration_description' => $data['desc'],
                                'configuration_group_id' => '6',
                                'sort_order' => '0',
                                'date_added' => 'now()');

        if (isset($data['set_func'])) {
          $sql_data_array['set_function'] = $data['set_func'];
        }

        if (isset($data['use_func'])) {
          $sql_data_array['use_function'] = $data['use_func'];
        }

        tep_db_perform(TABLE_CONFIGURATION, $sql_data_array);
      }
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array_keys($this->getParams());

      if ($this->check()) {
        foreach ($keys as $key) {
          if (!defined($key)) {
            $this->install($key);
          }
        }
      }

      return $keys;
    }

    function getParams() {
      if (!defined('MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID')) {
        $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Preparing [PayPal Standard]' limit 1");

        if (tep_db_num_rows($check_query) < 1) {
          $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
          $status = tep_db_fetch_array($status_query);

          $status_id = $status['status_id']+1;

          $languages = tep_get_languages();

          foreach ($languages as $lang) {
            tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $lang['id'] . "', 'Preparing [PayPal Standard]')");
          }

          $flags_query = tep_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
          if (tep_db_num_rows($flags_query) == 1) {
            tep_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 0 and downloads_flag = 0 where orders_status_id = '" . $status_id . "'");
          }
        } else {
          $check = tep_db_fetch_array($check_query);

          $status_id = $check['orders_status_id'];
        }
      } else {
        $status_id = MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID;
      }

      if (!defined('MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTIONS_ORDER_STATUS_ID')) {
        $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'PayPal [Transactions]' limit 1");

        if (tep_db_num_rows($check_query) < 1) {
          $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
          $status = tep_db_fetch_array($status_query);

          $tx_status_id = $status['status_id']+1;

          $languages = tep_get_languages();

          foreach ($languages as $lang) {
            tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $tx_status_id . "', '" . $lang['id'] . "', 'PayPal [Transactions]')");
          }

          $flags_query = tep_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
          if (tep_db_num_rows($flags_query) == 1) {
            tep_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 0 and downloads_flag = 0 where orders_status_id = '" . $tx_status_id . "'");
          }
        } else {
          $check = tep_db_fetch_array($check_query);

          $tx_status_id = $check['orders_status_id'];
        }
      } else {
        $tx_status_id = MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTIONS_ORDER_STATUS_ID;
      }

      $params = array('MODULE_PAYMENT_PAYPAL_STANDARD_STATUS' => array('title' => 'Enable PayPal Payments Standard',
                                                                       'desc' => 'Do you want to accept PayPal Payments Standard payments?',
                                                                       'value' => 'True',
                                                                       'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_ID' => array('title' => 'Seller E-Mail Address',
                                                                   'desc' => 'The PayPal seller e-mail address to accept payments for'),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID' => array('title' => 'Primary E-Mail Address',
                                                                           'desc' => 'The primary PayPal seller e-mail address to validate IPN with (leave empty if it is the same as the Seller E-Mail Address)'),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_PAGE_STYLE' => array('title' => 'Page Style',
                                                                           'desc' => 'The page style to use for the transaction procedure (defined at your PayPal Profile page)'),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTION_METHOD' => array('title' => 'Transaction Method',
                                                                                   'desc' => 'The processing method to use for each transaction.',
                                                                                   'value' => 'Sale',
                                                                                   'set_func' => 'tep_cfg_select_option(array(\'Authorization\', \'Sale\'), '),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID' => array('title' => 'Set Preparing Order Status',
                                                                                        'desc' => 'Set the status of prepared orders made with this payment module to this value',
                                                                                        'value' => $status_id,
                                                                                        'set_func' => 'tep_cfg_pull_down_order_statuses(',
                                                                                        'use_func' => 'tep_get_order_status_name'),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID' => array('title' => 'Set PayPal Acknowledged Order Status',
                                                                                'desc' => 'Set the status of orders made with this payment module to this value',
                                                                                'value' => '0',
                                                                                'set_func' => 'tep_cfg_pull_down_order_statuses(',
                                                                                'use_func' => 'tep_get_order_status_name'),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTIONS_ORDER_STATUS_ID' => array('title' => 'PayPal Transactions Order Status Level',
                                                                                             'desc' => 'Include PayPal transaction information in this order status level.',
                                                                                             'value' => $tx_status_id,
                                                                                             'use_func' => 'tep_get_order_status_name',
                                                                                             'set_func' => 'tep_cfg_pull_down_order_statuses('),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_ZONE' => array('title' => 'Payment Zone',
                                                                     'desc' => 'If a zone is selected, only enable this payment method for that zone.',
                                                                     'value' => '0',
                                                                     'use_func' => 'tep_get_zone_class_title',
                                                                     'set_func' => 'tep_cfg_pull_down_zone_classes('),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER' => array('title' => 'Gateway Server',
                                                                               'desc' => 'Use the testing (sandbox) or live gateway server for transactions?',
                                                                               'value' => 'Live',
                                                                               'set_func' => 'tep_cfg_select_option(array(\'Live\', \'Sandbox\'), '),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_VERIFY_SSL' => array('title' => 'Verify SSL Certificate',
                                                                           'desc' => 'Verify gateway server SSL certificate on connection?',
                                                                           'value' => 'True',
                                                                           'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_PROXY' => array('title' => 'Proxy Server',
                                                                      'desc' => 'Send API requests through this proxy server. (host:port, eg: 123.45.67.89:8080 or proxy.example.com:8080)'),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL' => array('title' => 'Debug E-Mail Address',
                                                                            'desc' => 'All parameters of an Invalid IPN notification will be sent to this email address if one is entered.'),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_STATUS' => array('title' => 'Enable Encrypted Website Payments',
                                                                           'desc' => 'Do you want to enable Encrypted Website Payments?',
                                                                           'value' => 'False',
                                                                           'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PRIVATE_KEY' => array('title' => 'Your Private Key',
                                                                                'desc' => 'The location of your Private Key to use for signing the data. (*.pem)'),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PUBLIC_KEY' => array('title' => 'Your Public Certificate',
                                                                               'desc' => 'The location of your Public Certificate to use for signing the data. (*.pem)'),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PAYPAL_KEY' => array('title' => 'PayPals Public Certificate',
                                                                               'desc' => 'The location of the PayPal Public Certificate for encrypting the data.'),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_CERT_ID' => array('title' => 'Your PayPal Public Certificate ID',
                                                                            'desc' => 'The Certificate ID to use from your PayPal Encrypted Payment Settings Profile.'),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY' => array('title' => 'Working Directory',
                                                                                      'desc' => 'The working directory to use for temporary files. (trailing slash needed)'),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_EWP_OPENSSL' => array('title' => 'OpenSSL Location',
                                                                            'desc' => 'The location of the openssl binary file.',
                                                                            'value' => '/usr/bin/openssl'),
                      'MODULE_PAYMENT_PAYPAL_STANDARD_SORT_ORDER' => array('title' => 'Sort order of display.',
                                                                           'desc' => 'Sort order of display. Lowest is displayed first.',
                                                                           'value' => '0'));

      return $params;
    }

    function sendTransactionToGateway($url, $parameters) {
      $server = parse_url($url);

      if ( !isset($server['port']) ) {
        $server['port'] = ($server['scheme'] == 'https') ? 443 : 80;
      }

      if ( !isset($server['path']) ) {
        $server['path'] = '/';
      }

      $curl = curl_init($server['scheme'] . '://' . $server['host'] . $server['path'] . (isset($server['query']) ? '?' . $server['query'] : ''));
      curl_setopt($curl, CURLOPT_PORT, $server['port']);
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
      curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);

      if ( MODULE_PAYMENT_PAYPAL_STANDARD_VERIFY_SSL == 'True' ) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

        if ( file_exists(DIR_FS_CATALOG . 'ext/modules/payment/paypal/paypal.com.crt') ) {
          curl_setopt($curl, CURLOPT_CAINFO, DIR_FS_CATALOG . 'ext/modules/payment/paypal/paypal.com.crt');
        } elseif ( file_exists(DIR_FS_CATALOG . 'includes/cacert.pem') ) {
          curl_setopt($curl, CURLOPT_CAINFO, DIR_FS_CATALOG . 'includes/cacert.pem');
        }
      } else {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      }

      if ( tep_not_null(MODULE_PAYMENT_PAYPAL_STANDARD_PROXY) ) {
        curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($curl, CURLOPT_PROXY, MODULE_PAYMENT_PAYPAL_STANDARD_PROXY);
      }

      $result = curl_exec($curl);

      curl_close($curl);

      return $result;
    }

// format prices without currency formatting
    function format_raw($number, $currency_code = '', $currency_value = '') {
      global $currencies, $currency;

      if (empty($currency_code) || !$this->is_set($currency_code)) {
        $currency_code = $currency;
      }

      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
      }

      return number_format(tep_round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }

    function sendDebugEmail($response = '', $ipn = false) {
      global $HTTP_POST_VARS, $HTTP_GET_VARS;

      if (tep_not_null(MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL)) {
        $email_body = '';

        if (!empty($response)) {
          $email_body .= 'RESPONSE:' . "\n\n" . print_r($response, true) . "\n\n";
        }

        if (!empty($HTTP_POST_VARS)) {
          $email_body .= '$HTTP_POST_VARS:' . "\n\n" . print_r($HTTP_POST_VARS, true) . "\n\n";
        }

        if (!empty($HTTP_GET_VARS)) {
          $email_body .= '$HTTP_GET_VARS:' . "\n\n" . print_r($HTTP_GET_VARS, true) . "\n\n";
        }

        if (!empty($email_body)) {
          tep_mail('', MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL, 'PayPal Standard Debug E-Mail' . ($ipn == true ? ' (IPN)' : ''), trim($email_body), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
        }
      }
    }

    function getTestLinkInfo() {
      $dialog_title = MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_TITLE;
      $dialog_button_close = MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_BUTTON_CLOSE;
      $dialog_success = MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_SUCCESS;
      $dialog_failed = MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_FAILED;
      $dialog_error = MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_ERROR;
      $dialog_connection_time = MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_TIME;

      $test_url = tep_href_link(FILENAME_MODULES, 'set=payment&module=' . $this->code . '&action=install&subaction=conntest');

      $js = <<<EOD
<script type="text/javascript">
$(function() {
  $('#tcdprogressbar').progressbar({
    value: false
  });
});

function openTestConnectionDialog() {
  var d = $('<div>').html($('#testConnectionDialog').html()).dialog({
    modal: true,
    title: '{$dialog_title}',
    buttons: {
      '{$dialog_button_close}': function () {
        $(this).dialog('destroy');
      }
    }
  });

  var timeStart = new Date().getTime();

  $.ajax({
    url: '{$test_url}'
  }).done(function(data) {
    if ( data == '1' ) {
      d.find('#testConnectionDialogProgress').html('<p style="font-weight: bold; color: green;">{$dialog_success}</p>');
    } else {
      d.find('#testConnectionDialogProgress').html('<p style="font-weight: bold; color: red;">{$dialog_failed}</p>');
    }
  }).fail(function() {
    d.find('#testConnectionDialogProgress').html('<p style="font-weight: bold; color: red;">{$dialog_error}</p>');
  }).always(function() {
    var timeEnd = new Date().getTime();
    var timeTook = new Date(0, 0, 0, 0, 0, 0, timeEnd-timeStart);

    d.find('#testConnectionDialogProgress').append('<p>{$dialog_connection_time} ' + timeTook.getSeconds() + '.' + timeTook.getMilliseconds() + 's</p>');
  });
}
</script>
EOD;

      $info = '<p><img src="images/icons/locked.gif" border="0">&nbsp;<a href="javascript:openTestConnectionDialog();" style="text-decoration: underline; font-weight: bold;">' . MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_LINK_TITLE . '</a></p>' .
              '<div id="testConnectionDialog" style="display: none;"><p>';

      if ( MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER == 'Live' ) {
        $info .= 'Live Server:<br />https://www.paypal.com/cgi-bin/webscr';
      } else {
        $info .= 'Sandbox Server:<br />https://www.sandbox.paypal.com/cgi-bin/webscr';
      }

      $info .= '</p><div id="testConnectionDialogProgress"><p>' . MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_GENERAL_TEXT . '</p><div id="tcdprogressbar"></div></div></div>' .
               $js;

      return $info;
    }

    function getTestConnectionResult() {
      $parameters = 'cmd=_notify-validate&business=' . urlencode(MODULE_PAYMENT_PAYPAL_STANDARD_ID);

      $result = $this->sendTransactionToGateway($this->form_action_url, $parameters);

      if ( $result == 'INVALID' ) {
        return 1;
      }

      return -1;
    }

    function verifyTransaction($is_ipn = false) {
      global $HTTP_POST_VARS, $currencies;

      if ( isset($HTTP_POST_VARS['invoice']) && is_numeric($HTTP_POST_VARS['invoice']) && ($HTTP_POST_VARS['invoice'] > 0) && isset($HTTP_POST_VARS['custom']) && is_numeric($HTTP_POST_VARS['custom']) && ($HTTP_POST_VARS['custom'] > 0) ) {
        $order_query = tep_db_query("select orders_id, orders_status, currency, currency_value from " . TABLE_ORDERS . " where orders_id = '" . (int)$HTTP_POST_VARS['invoice'] . "' and customers_id = '" . (int)$HTTP_POST_VARS['custom'] . "'");

        if ( tep_db_num_rows($order_query) === 1 ) {
          $order = tep_db_fetch_array($order_query);

          $new_order_status = DEFAULT_ORDERS_STATUS_ID;

          if ( $order['orders_status'] != MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID) {
            $new_order_status = $order['orders_status'];
          }

          $total_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order['orders_id'] . "' and class = 'ot_total' limit 1");
          $total = tep_db_fetch_array($total_query);

          $comment_status = 'Transaction ID: ' . $HTTP_POST_VARS['txn_id'] . '; ' .
                            $HTTP_POST_VARS['payment_status'] . ' (' . ucfirst($HTTP_POST_VARS['payer_status']) . '; ' . $currencies->format($HTTP_POST_VARS['mc_gross'], false, $HTTP_POST_VARS['mc_currency']) . ')';

          if ( $HTTP_POST_VARS['payment_status'] == 'Pending' ) {
            $comment_status .= '; ' . $HTTP_POST_VARS['pending_reason'];
          } elseif ( ($HTTP_POST_VARS['payment_status'] == 'Reversed') || ($HTTP_POST_VARS['payment_status'] == 'Refunded') ) {
            $comment_status .= '; ' . $HTTP_POST_VARS['reason_code'];
          }

          if ( $HTTP_POST_VARS['mc_gross'] != number_format($total['value'] * $order['currency_value'], $currencies->get_decimal_places($order['currency'])) ) {
            $comment_status .= '; PayPal transaction value (' . $HTTP_POST_VARS['mc_gross'] . ') does not match order value (' . number_format($total['value'] * $order['currency_value'], $currencies->get_decimal_places($order['currency'])) . ')';
          } elseif ($HTTP_POST_VARS['payment_status'] == 'Completed') {
            $new_order_status = (MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID > 0 ? MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID : $new_order_status);
          }

          tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . (int)$new_order_status . "', last_modified = now() where orders_id = '" . (int)$order['orders_id'] . "'");

          if ( $is_ipn === true ) {
            $source = 'PayPal IPN Verified';
          } else {
            $source = 'PayPal Verified';
          }

          $sql_data_array = array('orders_id' => (int)$order['orders_id'],
                                  'orders_status_id' => MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTIONS_ORDER_STATUS_ID,
                                  'date_added' => 'now()',
                                  'customer_notified' => '0',
                                  'comments' => $source . ' [' . tep_output_string_protected($comment_status) . ']');

          tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        }
      }
    }
  }
?>
