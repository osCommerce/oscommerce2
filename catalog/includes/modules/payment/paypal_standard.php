<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  if ( !class_exists('OSCOM_PayPal') ) {
    include(DIR_FS_CATALOG . 'includes/apps/paypal/OSCOM_PayPal.php');
  }

  class paypal_standard {
    var $code, $title, $description, $enabled, $_app;

    function paypal_standard() {
      global $PHP_SELF, $payment, $order;

      $this->_app = new OSCOM_PayPal();
      $this->_app->loadLanguageFile('modules/PS/PS.php');

      $this->signature = 'paypal|paypal_standard|' . $this->_app->getVersion() . '|2.3';
      $this->api_version = $this->_app->getApiVersion();

      $this->code = 'paypal_standard';
      $this->title = $this->_app->getDef('module_ps_title');
      $this->public_title = $this->_app->getDef('module_ps_public_title');
      $this->description = '<div align="center">' . $this->_app->drawButton($this->_app->getDef('module_ps_legacy_admin_app_button'), tep_href_link('paypal.php', 'action=configure&module=PS'), 'primary', null, true) . '</div>';
      $this->sort_order = defined('OSCOM_APP_PAYPAL_PS_SORT_ORDER') ? OSCOM_APP_PAYPAL_PS_SORT_ORDER : 0;
      $this->enabled = defined('OSCOM_APP_PAYPAL_PS_STATUS') && in_array(OSCOM_APP_PAYPAL_PS_STATUS, array('1', '0')) ? true : false;
      $this->order_status = defined('OSCOM_APP_PAYPAL_PS_PREPARE_ORDER_STATUS_ID') && ((int)OSCOM_APP_PAYPAL_PS_PREPARE_ORDER_STATUS_ID > 0) ? (int)OSCOM_APP_PAYPAL_PS_PREPARE_ORDER_STATUS_ID : 0;

      if ( defined('OSCOM_APP_PAYPAL_PS_STATUS') ) {
        if ( OSCOM_APP_PAYPAL_PS_STATUS == '0' ) {
          $this->title .= ' [Sandbox]';
          $this->public_title .= ' (' . $this->code . '; Sandbox)';
        }

        if ( OSCOM_APP_PAYPAL_PS_STATUS == '1' ) {
          $this->form_action_url = 'https://www.paypal.com/cgi-bin/webscr';
        } else {
          $this->form_action_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        }
      }

      if ( !function_exists('curl_init') ) {
        $this->description .= '<div class="secWarning">' . $this->_app->getDef('module_ps_error_curl') . '</div>';

        $this->enabled = false;
      }

      if ( $this->enabled === true ) {
        if ( !$this->_app->hasCredentials('PS', 'email') ) {
          $this->description .= '<div class="secWarning">' . $this->_app->getDef('module_ps_error_credentials') . '</div>';

          $this->enabled = false;
        }
      }

      if ( $this->enabled === true ) {
        if ( isset($order) && is_object($order) ) {
          $this->update_status();
        }
      }

// Before the stock quantity check is performed in checkout_process.php, detect if the quantity
// has already beed deducated in the IPN to avoid a quantity == 0 redirect
      if ( $this->enabled === true ) {
        if ( defined('FILENAME_CHECKOUT_PROCESS') && (basename($PHP_SELF) == FILENAME_CHECKOUT_PROCESS) ) {
          if ( tep_session_is_registered('payment') && ($payment == $this->code) ) {
            $this->pre_before_check();
          }
        }
      }
    }

    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)OSCOM_APP_PAYPAL_PS_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . (int)OSCOM_APP_PAYPAL_PS_ZONE . "' and zone_country_id = '" . (int)$order->billing['country']['id'] . "' order by zone_id");
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
                                       where pa.products_id = '" . (int)$order->products[$i]['id'] . "'
                                       and pa.options_id = '" . (int)$order->products[$i]['attributes'][$j]['option_id'] . "'
                                       and pa.options_id = popt.products_options_id
                                       and pa.options_values_id = '" . (int)$order->products[$i]['attributes'][$j]['value_id'] . "'
                                       and pa.options_values_id = poval.products_options_values_id
                                       and popt.language_id = '" . (int)$languages_id . "'
                                       and poval.language_id = '" . (int)$languages_id . "'";
                  $attributes = tep_db_query($attributes_query);
                } else {
                  $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . (int)$order->products[$i]['id'] . "' and pa.options_id = '" . (int)$order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . (int)$order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . (int)$languages_id . "' and poval.language_id = '" . (int)$languages_id . "'");
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
      global $language, $customer_id, $order, $sendto, $currency, $cart_PayPal_Standard_ID, $shipping, $order_total_modules;

      $total_tax = $order->info['tax'];

// remove shipping tax in total tax value
      if ( isset($shipping['cost']) ) {
        $total_tax -= ($order->info['shipping_cost'] - $shipping['cost']);
      }

      $ipn_language = null;

      if ( !class_exists('language') ) {
        include(DIR_WS_CLASSES . 'language.php');
      }

      $lng = new language();

      if ( count($lng->catalog_languages) > 1 ) {
        foreach ( $lng->catalog_languages as $key => $value ) {
          if ( $value['directory'] == $language ) {
            $ipn_language = $key;
            break;
          }
        }
      }

      $process_button_string = '';
      $parameters = array('cmd' => '_cart',
                          'upload' => '1',
                          'item_name_1' => STORE_NAME,
                          'shipping_1' => $this->_app->formatCurrencyRaw($order->info['shipping_cost']),
                          'business' => $this->_app->getCredentials('PS', 'email'),
                          'amount_1' => $this->_app->formatCurrencyRaw($order->info['total'] - $order->info['shipping_cost'] - $total_tax),
                          'currency_code' => $currency,
                          'invoice' => substr($cart_PayPal_Standard_ID, strpos($cart_PayPal_Standard_ID, '-')+1),
                          'custom' => $customer_id,
                          'no_note' => '1',
                          'notify_url' => tep_href_link('ext/modules/payment/paypal/standard_ipn.php', (isset($ipn_language) ? 'language=' . $ipn_language : ''), 'SSL', false, false),
                          'rm' => '2',
                          'return' => tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'),
                          'cancel_return' => tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'),
                          'bn' => $OSCOM_PayPal->getIdentifier(),
                          'paymentaction' => (OSCOM_APP_PAYPAL_PS_TRANSACTION_METHOD == '1') ? 'sale' : 'authorization');

      $return_link_title = $this->_app->getDef('module_ps_button_return_to_store', array('storename' => STORE_NAME));

      if ( strlen($return_link_title) <= 60 ) {
        $parameters['cbt'] = $return_link_title;
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

      if (tep_not_null(OSCOM_APP_PAYPAL_PS_PAGE_STYLE)) {
        $parameters['page_style'] = OSCOM_APP_PAYPAL_PS_PAGE_STYLE;
      }

      $item_params = array();

      $line_item_no = 1;

      foreach ($order->products as $product) {
        if ( DISPLAY_PRICE_WITH_TAX == 'true' ) {
          $product_price = $this->_app->formatCurrencyRaw($product['final_price'] + tep_calculate_tax($product['final_price'], $product['tax']));
        } else {
          $product_price = $this->_app->formatCurrencyRaw($product['final_price']);
        }

        $item_params['item_name_' . $line_item_no] = $product['name'];
        $item_params['amount_' . $line_item_no] = $product_price;
        $item_params['quantity_' . $line_item_no] = $product['qty'];

        $line_item_no++;
      }

      $items_total = $this->_app->formatCurrencyRaw($order->info['subtotal']);

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
                  $item_params['amount_' . $line_item_no] = $this->_app->formatCurrencyRaw($GLOBALS[$class]->output[$i]['value']);

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
        $item_params['tax_cart'] = $this->_app->formatCurrencyRaw($total_tax);

        $paypal_item_total += $item_params['tax_cart'];
      }

      if ( ($has_negative_price == false) && ($this->_app->formatCurrencyRaw($paypal_item_total) == $this->_app->formatCurrencyRaw($order->info['total'])) ) {
        $parameters = array_merge($parameters, $item_params);
      } else {
        $parameters['tax_cart'] = $this->_app->formatCurrencyRaw($total_tax);
      }

      if ( OSCOM_APP_PAYPAL_PS_EWP_STATUS == '1' ) {
        $parameters['cert_id'] = OSCOM_APP_PAYPAL_PS_EWP_PUBLIC_CERT_ID;

        $random_string = rand(100000, 999999) . '-' . $customer_id . '-';

        $data = '';
        foreach ($parameters as $key => $value) {
          $data .= $key . '=' . $value . "\n";
        }

        $fp = fopen(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt', 'w');
        fwrite($fp, $data);
        fclose($fp);

        unset($data);

        if (function_exists('openssl_pkcs7_sign') && function_exists('openssl_pkcs7_encrypt')) {
          openssl_pkcs7_sign(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt', OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt', file_get_contents(OSCOM_APP_PAYPAL_PS_EWP_PUBLIC_CERT), file_get_contents(OSCOM_APP_PAYPAL_PS_EWP_PRIVATE_KEY), array('From' => $this->_app->getCredentials('PS', 'email')), PKCS7_BINARY);

          unlink(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt');

// remove headers from the signature
          $signed = file_get_contents(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt');
          $signed = explode("\n\n", $signed);
          $signed = base64_decode($signed[1]);

          $fp = fopen(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt', 'w');
          fwrite($fp, $signed);
          fclose($fp);

          unset($signed);

          openssl_pkcs7_encrypt(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt', OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt', file_get_contents(OSCOM_APP_PAYPAL_PS_EWP_PAYPAL_CERT), array('From' => $this->_app->getCredentials('PS', 'email')), PKCS7_BINARY);

          unlink(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt');

// remove headers from the encrypted result
          $data = file_get_contents(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt');
          $data = explode("\n\n", $data);
          $data = '-----BEGIN PKCS7-----' . "\n" . $data[1] . "\n" . '-----END PKCS7-----';

          unlink(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt');
        } else {
          exec(OSCOM_APP_PAYPAL_PS_EWP_OPENSSL . ' smime -sign -in ' . OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt -signer ' . OSCOM_APP_PAYPAL_PS_EWP_PUBLIC_CERT . ' -inkey ' . OSCOM_APP_PAYPAL_PS_EWP_PRIVATE_KEY . ' -outform der -nodetach -binary > ' . OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt');
          unlink(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt');

          exec(OSCOM_APP_PAYPAL_PS_EWP_OPENSSL . ' smime -encrypt -des3 -binary -outform pem ' . OSCOM_APP_PAYPAL_PS_EWP_PAYPAL_CERT . ' < ' . OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt > ' . OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt');
          unlink(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt');

          $fh = fopen(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt', 'rb');
          $data = fread($fh, filesize(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt'));
          fclose($fh);

          unlink(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt');
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

    function pre_before_check() {
      global $HTTP_GET_VARS, $HTTP_POST_VARS, $messageStack, $order_id, $cart_PayPal_Standard_ID, $customer_id, $comments;

      $result = false;

      $pptx_params = array();

      $seller_accounts = array($this->_app->getCredentials('PS', 'email'));

      if ( tep_not_null($this->_app->getCredentials('PS', 'email_primary')) ) {
        $seller_accounts[] = $this->_app->getCredentials('PS', 'email_primary');
      }

      if ( isset($HTTP_POST_VARS['receiver_email']) && in_array($HTTP_POST_VARS['receiver_email'], $seller_accounts) ) {
        $parameters = 'cmd=_notify-validate&';

        foreach ( $HTTP_POST_VARS as $key => $value ) {
          if ( $key != 'cmd' ) {
            $parameters .= $key . '=' . urlencode(stripslashes($value)) . '&';
          }
        }

        $parameters = substr($parameters, 0, -1);

        $result = $this->_app->makeApiCall($this->form_action_url, $parameters);

        $pptx_params = $HTTP_POST_VARS;
        $pptx_params['cmd'] = '_notify-validate';

        foreach ( $HTTP_GET_VARS as $key => $value ) {
          $pptx_params['GET ' . $key] = $value;
        }

        $this->_app->log('PS', $pptx_params['cmd'], ($result == 'VERIFIED') ? 1 : -1, $pptx_params, $result, (OSCOM_APP_PAYPAL_PS_STATUS == '1') ? 'live' : 'sandbox');
      } elseif ( isset($HTTP_GET_VARS['tx']) ) { // PDT
        if ( tep_not_null(OSCOM_APP_PAYPAL_PS_PDT_IDENTITY_TOKEN) ) {
          $pptx_params['cmd'] = '_notify-synch';

          $parameters = 'cmd=_notify-synch&tx=' . urlencode($HTTP_GET_VARS['tx']) . '&at=' . urlencode(OSCOM_APP_PAYPAL_PS_PDT_IDENTITY_TOKEN);

          $pdt_raw = $this->_app->makeApiCall($this->form_action_url, $parameters);

          if ( !empty($pdt_raw) ) {
            $pdt = explode("\n", trim($pdt_raw));

            if ( isset($pdt[0]) ) {
              if ( $pdt[0] == 'SUCCESS' ) {
                $result = 'VERIFIED';

                unset($pdt[0]);
              } else {
                $result = $pdt_raw;
              }
            }

            if ( is_array($pdt) && !empty($pdt) ) {
              foreach ( $pdt as $line ) {
                $p = explode('=', $line, 2);

                if ( count($p) === 2 ) {
                  $pptx_params[trim($p[0])] = trim(urldecode($p[1]));
                }
              }
            }
          }

          foreach ( $HTTP_GET_VARS as $key => $value ) {
            $pptx_params['GET ' . $key] = $value;
          }

          $this->_app->log('PS', $pptx_params['cmd'], ($result == 'VERIFIED') ? 1 : -1, $pptx_params, $result, (OSCOM_APP_PAYPAL_PS_STATUS == '1') ? 'live' : 'sandbox');
        } else {
          $details = $this->_app->getApiResult('PS', 'GetTransactionDetails', array('TRANSACTIONID' => $HTTP_GET_VARS['tx']), (OSCOM_APP_PAYPAL_DP_STATUS == '1') ? 'live' : 'sandbox');

          if ( in_array($details['ACK'], array('Success', 'SuccessWithWarning')) ) {
            $result = 'VERIFIED';

            $pptx_params = array('txn_id' => $details['TRANSACTIONID'],
                                 'invoice' => $details['INVNUM'],
                                 'custom' => $details['CUSTOM'],
                                 'payment_status' => $details['PAYMENTSTATUS'],
                                 'payer_status' => $details['PAYERSTATUS'],
                                 'mc_gross' => $details['AMT'],
                                 'mc_currency' => $details['CURRENCYCODE'],
                                 'pending_reason' => $details['PENDINGREASON'],
                                 'reason_code' => $details['REASONCODE'],
                                 'address_status' => $details['ADDRESSSTATUS'],
                                 'payment_type' => $details['PAYMENTTYPE']);
          }
        }
      } else {
        $pptx_params = $HTTP_POST_VARS;
        $pptx_params['cmd'] = '_notify-validate';

        foreach ( $HTTP_GET_VARS as $key => $value ) {
          $pptx_params['GET ' . $key] = $value;
        }

        $this->_app->log('PS', $pptx_params['cmd'], ($result == 'VERIFIED') ? 1 : -1, $pptx_params, $result, (OSCOM_APP_PAYPAL_PS_STATUS == '1') ? 'live' : 'sandbox');
      }

      if ( $result != 'VERIFIED' ) {
        $messageStack->add_session('header', $this->_app->getDef('module_ps_error_invalid_transaction'));

        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
      }

      $this->verifyTransaction($pptx_params);

      $order_id = substr($cart_PayPal_Standard_ID, strpos($cart_PayPal_Standard_ID, '-')+1);

      $check_query = tep_db_query("select orders_status from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "' and customers_id = '" . (int)$customer_id . "'");

      if (!tep_db_num_rows($check_query) || ($order_id != $pptx_params['invoice']) || ($customer_id != $pptx_params['custom'])) {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
      }

      $check = tep_db_fetch_array($check_query);

// skip before_process() if order was already processed in IPN
      if ( $check['orders_status'] != OSCOM_APP_PAYPAL_PS_PREPARE_ORDER_STATUS_ID ) {
        if ( tep_session_is_registered('comments') && !empty($comments) ) {
          $sql_data_array = array('orders_id' => $order_id,
                                  'orders_status_id' => (int)$check['orders_status'],
                                  'date_added' => 'now()',
                                  'customer_notified' => '0',
                                  'comments' => $comments);

          tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        }

// load the after_process function from the payment modules
        $this->after_process();
      }
    }

    function before_process() {
      global $order_id, $order, $languages_id, $currencies, $order_totals, $customer_id, $sendto, $billto, $payment;

      $new_order_status = DEFAULT_ORDERS_STATUS_ID;

      if ( OSCOM_APP_PAYPAL_PS_ORDER_STATUS_ID > 0) {
        $new_order_status = OSCOM_APP_PAYPAL_PS_ORDER_STATUS_ID;
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
            $products_attributes = (isset($order->products[$i]['attributes'])) ? $order->products[$i]['attributes'] : '';
            if (is_array($products_attributes)) {
              $stock_query_raw .= " AND pa.options_id = '" . (int)$products_attributes[0]['option_id'] . "' AND pa.options_values_id = '" . (int)$products_attributes[0]['value_id'] . "'";
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
            tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . (int)$stock_left . "' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
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
                                   where pa.products_id = '" . (int)$order->products[$i]['id'] . "'
                                   and pa.options_id = '" . (int)$order->products[$i]['attributes'][$j]['option_id'] . "'
                                   and pa.options_id = popt.products_options_id
                                   and pa.options_values_id = '" . (int)$order->products[$i]['attributes'][$j]['value_id'] . "'
                                   and pa.options_values_id = poval.products_options_values_id
                                   and popt.language_id = '" . (int)$languages_id . "'
                                   and poval.language_id = '" . (int)$languages_id . "'";
              $attributes = tep_db_query($attributes_query);
            } else {
              $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . (int)$order->products[$i]['id'] . "' and pa.options_id = '" . (int)$order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . (int)$order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . (int)$languages_id . "' and poval.language_id = '" . (int)$languages_id . "'");
            }
            $attributes_values = tep_db_fetch_array($attributes);

            $products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
          }
        }
//------insert customer choosen option eof ----
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

      if (isset($GLOBALS[$payment]) && is_object($GLOBALS[$payment])) {
        $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
                        EMAIL_SEPARATOR . "\n";
        $payment_class = $GLOBALS[$payment];
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
    }

    function after_process() {
      global $cart;

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

    function get_error() {
      return false;
    }

    function check() {
      $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'OSCOM_APP_PAYPAL_PS_STATUS'");
      if ( tep_db_num_rows($check_query) ) {
        $check = tep_db_fetch_array($check_query);

        return tep_not_null($check['configuration_value']);
      }

      return false;
    }

    function install() {
      tep_redirect(tep_href_link('paypal.php', 'action=configure&subaction=install&module=PS'));
    }

    function remove() {
      tep_redirect(tep_href_link('paypal.php', 'action=configure&subaction=uninstall&module=PS'));
    }

    function keys() {
      return array('OSCOM_APP_PAYPAL_PS_SORT_ORDER');
    }

    function verifyTransaction($pptx_params, $is_ipn = false) {
      global $currencies;

      if ( isset($pptx_params['invoice']) && is_numeric($pptx_params['invoice']) && ($pptx_params['invoice'] > 0) && isset($pptx_params['custom']) && is_numeric($pptx_params['custom']) && ($pptx_params['custom'] > 0) ) {
        $order_query = tep_db_query("select orders_id, currency, currency_value from " . TABLE_ORDERS . " where orders_id = '" . (int)$pptx_params['invoice'] . "' and customers_id = '" . (int)$pptx_params['custom'] . "'");

        if ( tep_db_num_rows($order_query) === 1 ) {
          $order = tep_db_fetch_array($order_query);

          $total_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order['orders_id'] . "' and class = 'ot_total' limit 1");
          $total = tep_db_fetch_array($total_query);

          $comment_status = 'Transaction ID: ' . tep_output_string_protected($pptx_params['txn_id']) . "\n" .
                            'Payer Status: ' . tep_output_string_protected($pptx_params['payer_status']) . "\n" .
                            'Address Status: ' . tep_output_string_protected($pptx_params['address_status']) . "\n" .
                            'Payment Status: ' . tep_output_string_protected($pptx_params['payment_status']) . "\n" .
                            'Payment Type: ' . tep_output_string_protected($pptx_params['payment_type']) . "\n" .
                            'Pending Reason: ' . tep_output_string_protected($pptx_params['pending_reason']);

          if ( $pptx_params['mc_gross'] != $this->_app->formatCurrencyRaw($total['value'], $order['currency'], $order['currency_value']) ) {
            $comment_status .= "\n" . 'OSCOM Error Total Mismatch: PayPal transaction value (' . tep_output_string_protected($pptx_params['mc_gross']) . ') does not match order value (' . $this->_app->formatCurrencyRaw($total['value'], $order['currency'], $order['currency_value']) . ')';
          }

          if ( $is_ipn === true ) {
            $comment_status .= "\n" . 'Source: IPN';
          }

          $sql_data_array = array('orders_id' => (int)$order['orders_id'],
                                  'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                                  'date_added' => 'now()',
                                  'customer_notified' => '0',
                                  'comments' => $comment_status);

          tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        }
      }
    }
  }
?>
