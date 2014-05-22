<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class paypal_pro_hs {
    var $code, $title, $description, $enabled;

    function paypal_pro_hs() {
      global $HTTP_GET_VARS, $PHP_SELF, $order;

      $this->signature = 'paypal|paypal_pro_hs|1.0|2.3';
      $this->api_version = '112';

      $this->code = 'paypal_pro_hs';
      $this->title = MODULE_PAYMENT_PAYPAL_PRO_HS_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_PAYPAL_PRO_HS_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_PAYPAL_PRO_HS_TEXT_DESCRIPTION;
      $this->sort_order = defined('MODULE_PAYMENT_PAYPAL_PRO_HS_SORT_ORDER') ? MODULE_PAYMENT_PAYPAL_PRO_HS_SORT_ORDER : 0;
      $this->enabled = defined('MODULE_PAYMENT_PAYPAL_PRO_HS_STATUS') && (MODULE_PAYMENT_PAYPAL_PRO_HS_STATUS == 'True') ? true : false;
      $this->order_status = defined('MODULE_PAYMENT_PAYPAL_PRO_HS_PREPARE_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_PAYPAL_PRO_HS_PREPARE_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_PAYPAL_PRO_HS_PREPARE_ORDER_STATUS_ID : 0;

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_HS_STATUS') ) {
        if ( MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER == 'Sandbox' ) {
          $this->title .= ' [Sandbox]';
          $this->public_title .= ' (' . $this->code . '; Sandbox)';
        }

        if ( MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER == 'Live' ) {
          $this->api_url = 'https://api-3t.paypal.com/nvp';
        } else {
          $this->api_url = 'https://api-3t.sandbox.paypal.com/nvp';
        }

        $this->description .= $this->getTestLinkInfo();
      }

      if ( !function_exists('curl_init') ) {
        $this->description = '<div class="secWarning">' . MODULE_PAYMENT_PAYPAL_PRO_HS_ERROR_ADMIN_CURL . '</div>' . $this->description;

        $this->enabled = false;
      }

      if ( $this->enabled === true ) {
        if ( !tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_ID) || !tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_API_USERNAME) || !tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_API_PASSWORD) || !tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_API_SIGNATURE) ) {
          $this->description = '<div class="secWarning">' . MODULE_PAYMENT_PAYPAL_PRO_HS_ERROR_ADMIN_CONFIGURATION . '</div>' . $this->description;

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

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PAYPAL_PRO_HS_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PAYPAL_PRO_HS_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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
      global $cart_PayPal_Pro_HS_ID;

      if (tep_session_is_registered('cart_PayPal_Pro_HS_ID')) {
        $order_id = substr($cart_PayPal_Pro_HS_ID, strpos($cart_PayPal_Pro_HS_ID, '-')+1);

        $check_query = tep_db_query('select orders_id from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '" limit 1');

        if (tep_db_num_rows($check_query) < 1) {
          tep_db_query('delete from ' . TABLE_ORDERS . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . ' where orders_id = "' . (int)$order_id . '"');

          tep_session_unregister('cart_PayPal_Pro_HS_ID');
        }
      }

      return array('id' => $this->code,
                   'module' => $this->public_title);
    }

    function pre_confirmation_check() {
      global $cartID, $cart;

      if (empty($cart->cartID)) {
        $cartID = $cart->cartID = $cart->generate_cart_id();
      }

      if (!tep_session_is_registered('cartID')) {
        tep_session_register('cartID');
      }
    }

    function confirmation() {
      global $cartID, $cart_PayPal_Pro_HS_ID, $customer_id, $languages_id, $order, $order_total_modules, $currency, $sendto, $pphs_result, $pphs_key;

      $pphs_result = array();

      if (tep_session_is_registered('cartID')) {
        $insert_order = false;

        if (tep_session_is_registered('cart_PayPal_Pro_HS_ID')) {
          $order_id = substr($cart_PayPal_Pro_HS_ID, strpos($cart_PayPal_Pro_HS_ID, '-')+1);

          $curr_check = tep_db_query("select currency from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
          $curr = tep_db_fetch_array($curr_check);

          if ( ($curr['currency'] != $order->info['currency']) || ($cartID != substr($cart_PayPal_Pro_HS_ID, 0, strlen($cartID))) ) {
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

          $cart_PayPal_Pro_HS_ID = $cartID . '-' . $insert_id;
          tep_session_register('cart_PayPal_Pro_HS_ID');
        }

        $order_id = substr($cart_PayPal_Pro_HS_ID, strpos($cart_PayPal_Pro_HS_ID, '-')+1);

        $params = array('business' => MODULE_PAYMENT_PAYPAL_PRO_HS_ID,
                        'bn' => 'OSCOM23_HS',
                        'buyer_email' => $order->customer['email_address'],
                        'cancel_return' => tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'),
                        'currency_code' => $currency,
                        'invoice' => $order_id,
                        'custom' => $customer_id,
                        'paymentaction' => MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTION_METHOD == 'Sale' ? 'sale' : 'authorization',
                        'return' => tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'),
                        'notify_url' => tep_href_link('ext/modules/payment/paypal/pro_hosted_ipn.php', '', 'SSL', false, false),
                        'shipping' => $this->format_raw($order->info['shipping_cost']),
                        'tax' => $this->format_raw($order->info['tax']),
                        'subtotal' => $this->format_raw($order->info['total'] - $order->info['shipping_cost'] - $order->info['tax']),
                        'billing_first_name' => $order->billing['firstname'],
                        'billing_last_name' => $order->billing['lastname'],
                        'billing_address1' => $order->billing['street_address'],
                        'billing_city' => $order->billing['city'],
                        'billing_state' => tep_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], $order->billing['state']),
                        'billing_zip' => $order->billing['postcode'],
                        'billing_country' => $order->billing['country']['iso_code_2'],
                        'night_phone_b' => $order->customer['telephone'],
                        'template' => 'templateD',
                        'item_name' => STORE_NAME,
                        'showBillingAddress' => 'false',
                        'showShippingAddress' => 'false',
                        'showHostedThankyouPage' => 'false');

        if ( is_numeric($sendto) && ($sendto > 0) ) {
          $params['address_override'] = 'true';
          $params['first_name'] = $order->delivery['firstname'];
          $params['last_name'] = $order->delivery['lastname'];
          $params['address1'] = $order->delivery['street_address'];
          $params['city'] = $order->delivery['city'];
          $params['state'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
          $params['zip'] = $order->delivery['postcode'];
          $params['country'] = $order->delivery['country']['iso_code_2'];
        }

        if ( tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_TEXT_PAYPAL_RETURN_BUTTON) && (strlen(MODULE_PAYMENT_PAYPAL_PRO_HS_TEXT_PAYPAL_RETURN_BUTTON) <= 60) ) {
          $params['cbt'] = MODULE_PAYMENT_PAYPAL_PRO_HS_TEXT_PAYPAL_RETURN_BUTTON;
        }

        $counter = 0;
        $params_string = 'USER=' . urlencode(utf8_encode(trim(MODULE_PAYMENT_PAYPAL_PRO_HS_API_USERNAME))) . '&PWD=' . urlencode(utf8_encode(trim(MODULE_PAYMENT_PAYPAL_PRO_HS_API_PASSWORD))) . '&SIGNATURE=' . urlencode(utf8_encode(trim(MODULE_PAYMENT_PAYPAL_PRO_HS_API_SIGNATURE))) . '&VERSION=' . $this->api_version . '&METHOD=BMCreateButton&BUTTONCODE=TOKEN&BUTTONTYPE=PAYMENT&';

        foreach ( $params as $key => $value ) {
          $params_string .= 'L_BUTTONVAR' . $counter . '=' . $key . '=' . urlencode(utf8_encode(trim($value))) . '&';

          $counter++;
        }

        $params_string = substr($params_string, 0, -1);

        $response = $this->sendTransactionToGateway($this->api_url, $params_string);

        $pphs_result = array();
        parse_str($response, $pphs_result);

        if (($pphs_result['ACK'] != 'Success') && ($pphs_result['ACK'] != 'SuccessWithWarning')) {
          $this->sendDebugEmail($pphs_result);
        }

        if ( !tep_session_is_registered('pphs_result') ) {
          tep_session_register('pphs_result');
        }
      }

      $pphs_key = tep_create_random_value(16);

      if ( !tep_session_is_registered('pphs_key') ) {
        tep_session_register('pphs_key');
      }

      $iframe_url = tep_href_link('ext/modules/payment/paypal/hosted_checkout.php', 'key=' . $pphs_key, 'SSL');
      $form_url = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=paypal_pro_hs', 'SSL');

      $output = <<<EOD
<iframe src="{$iframe_url}" width="570px" height="540px" frameBorder="0" scrolling="no"></iframe>
<script>
$(function() {
  $('form[name="checkout_confirmation"] input[type="submit"], form[name="checkout_confirmation"] input[type="image"], form[name="checkout_confirmation"] button[type="submit"]').hide();
  $('form[name="checkout_confirmation"]').attr('action', '{$form_url}');
});
</script>
EOD;

      $confirmation = array('title' => $output);

      return $confirmation;
    }

    function process_button() {
      return false;
    }

    function before_process() {
      global $HTTP_GET_VARS, $HTTP_POST_VARS, $cart_PayPal_Pro_HS_ID, $customer_id, $pphs_result, $order, $order_totals, $sendto, $billto, $languages_id, $payment, $currencies, $cart, $$payment;

      $result = false;

      if ( isset($HTTP_GET_VARS['tx']) && !empty($HTTP_GET_VARS['tx']) ) { // direct payment (eg, credit card)
        $result = $this->getTransactionDetails($HTTP_GET_VARS['tx']);
      } elseif ( isset($HTTP_POST_VARS['txn_id']) && !empty($HTTP_POST_VARS['txn_id']) ) { // paypal payment
        $result = $this->getTransactionDetails($HTTP_POST_VARS['txn_id']);
      }

      if ( !is_array($result) || !isset($result['ACK']) || (($result['ACK'] != 'Success') && ($result['ACK'] != 'SuccessWithWarning')) ) {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'error_message=' . stripslashes($result['L_LONGMESSAGE0'])));
      }

      $order_id = substr($cart_PayPal_Pro_HS_ID, strpos($cart_PayPal_Pro_HS_ID, '-')+1);

      $seller_accounts = array(MODULE_PAYMENT_PAYPAL_PRO_HS_ID);

      if ( tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_PRIMARY_ID) ) {
        $seller_accounts[] = MODULE_PAYMENT_PAYPAL_PRO_HS_PRIMARY_ID;
      }

      if ( !isset($result['RECEIVERBUSINESS']) || !in_array($result['RECEIVERBUSINESS'], $seller_accounts) || ($result['INVNUM'] != $order_id) || ($result['CUSTOM'] != $customer_id) ) {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
      }

      $pphs_result = $result;

      $check_query = tep_db_query("select orders_status from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "' and customers_id = '" . (int)$customer_id . "'");

      $tx_order_id = $pphs_result['INVNUM'];
      $tx_customer_id = $pphs_result['CUSTOM'];

      if (!tep_db_num_rows($check_query) || ($order_id != $tx_order_id) || ($customer_id != $tx_customer_id)) {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
      }

      $check = tep_db_fetch_array($check_query);

      $this->verifyTransaction();

      $new_order_status = DEFAULT_ORDERS_STATUS_ID;

      if ( $check['orders_status'] != MODULE_PAYMENT_PAYPAL_PRO_HS_PREPARE_ORDER_STATUS_ID ) {
        $new_order_status = $check['orders_status'];
      }

      if ( (MODULE_PAYMENT_PAYPAL_PRO_HS_ORDER_STATUS_ID > 0) && ($check['orders_status'] == MODULE_PAYMENT_PAYPAL_PRO_HS_ORDER_STATUS_ID) ) {
        $new_order_status = MODULE_PAYMENT_PAYPAL_PRO_HS_ORDER_STATUS_ID;
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

      tep_session_unregister('cart_PayPal_Pro_HS_ID');
      tep_session_unregister('pphs_result');
      tep_session_unregister('pphs_key');

      tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
    }

    function after_process() {
      return false;
    }

    function get_error() {
      global $pphs_error_msg;

      $error = array('title' => MODULE_PAYMENT_PAYPAL_PRO_HS_ERROR_TITLE,
                     'error' => MODULE_PAYMENT_PAYPAL_PRO_HS_ERROR_GENERAL);

      if ( tep_session_is_registered('pphs_error_msg') ) {
        $error['error'] = $pphs_error_msg;

        tep_session_unregister('pphs_error_msg');
      }

      return $error;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYPAL_PRO_HS_STATUS'");
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
      if (!defined('MODULE_PAYMENT_PAYPAL_PRO_HS_PREPARE_ORDER_STATUS_ID')) {
        $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Preparing [PayPal Pro HS]' limit 1");

        if (tep_db_num_rows($check_query) < 1) {
          $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
          $status = tep_db_fetch_array($status_query);

          $status_id = $status['status_id']+1;

          $languages = tep_get_languages();

          foreach ($languages as $lang) {
            tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $lang['id'] . "', 'Preparing [PayPal Pro HS]')");
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
        $status_id = MODULE_PAYMENT_PAYPAL_PRO_HS_PREPARE_ORDER_STATUS_ID;
      }

      if (!defined('MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTIONS_ORDER_STATUS_ID')) {
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
        $tx_status_id = MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTIONS_ORDER_STATUS_ID;
      }

      $params = array('MODULE_PAYMENT_PAYPAL_PRO_HS_STATUS' => array('title' => 'Enable PayPal Payments Pro (Hosted Solution)',
                                                                     'desc' => 'Do you want to accept PayPal Payments Pro (Hosted Solution) payments?',
                                                                     'value' => 'True',
                                                                     'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_PAYPAL_PRO_HS_API_USERNAME' => array('title' => 'API Username',
                                                                           'desc' => 'The username to use for the PayPal API service.'),
                      'MODULE_PAYMENT_PAYPAL_PRO_HS_API_PASSWORD' => array('title' => 'API Password',
                                                                           'desc' => 'The password to use for the PayPal API service.'),
                      'MODULE_PAYMENT_PAYPAL_PRO_HS_API_SIGNATURE' => array('title' => 'API Signature',
                                                                            'desc' => 'The signature to use for the PayPal API service.'),
                      'MODULE_PAYMENT_PAYPAL_PRO_HS_ID' => array('title' => 'Seller E-Mail Address',
                                                                 'desc' => 'The PayPal seller e-mail address to accept payments for'),
                      'MODULE_PAYMENT_PAYPAL_PRO_HS_PRIMARY_ID' => array('title' => 'Primary E-Mail Address',
                                                                         'desc' => 'The primary PayPal seller e-mail address to validate transactions with (leave empty if it is the same as the Seller E-Mail Address)'),
                      'MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTION_METHOD' => array('title' => 'Transaction Method',
                                                                                 'desc' => 'The processing method to use for each transaction.',
                                                                                 'value' => 'Sale',
                                                                                 'set_func' => 'tep_cfg_select_option(array(\'Authorization\', \'Sale\'), '),
                      'MODULE_PAYMENT_PAYPAL_PRO_HS_PREPARE_ORDER_STATUS_ID' => array('title' => 'Set Preparing Order Status',
                                                                                      'desc' => 'Set the status of prepared orders made with this payment module to this value',
                                                                                      'value' => $status_id,
                                                                                      'set_func' => 'tep_cfg_pull_down_order_statuses(',
                                                                                      'use_func' => 'tep_get_order_status_name'),
                      'MODULE_PAYMENT_PAYPAL_PRO_HS_ORDER_STATUS_ID' => array('title' => 'Set PayPal Acknowledged Order Status',
                                                                              'desc' => 'Set the status of orders made with this payment module to this value',
                                                                              'value' => '0',
                                                                              'set_func' => 'tep_cfg_pull_down_order_statuses(',
                                                                              'use_func' => 'tep_get_order_status_name'),
                      'MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTIONS_ORDER_STATUS_ID' => array('title' => 'PayPal Transactions Order Status Level',
                                                                                           'desc' => 'Include PayPal transaction information in this order status level.',
                                                                                           'value' => $tx_status_id,
                                                                                           'use_func' => 'tep_get_order_status_name',
                                                                                           'set_func' => 'tep_cfg_pull_down_order_statuses('),
                      'MODULE_PAYMENT_PAYPAL_PRO_HS_ZONE' => array('title' => 'Payment Zone',
                                                                   'desc' => 'If a zone is selected, only enable this payment method for that zone.',
                                                                   'value' => '0',
                                                                   'use_func' => 'tep_get_zone_class_title',
                                                                   'set_func' => 'tep_cfg_pull_down_zone_classes('),
                      'MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER' => array('title' => 'Gateway Server',
                                                                             'desc' => 'Use the testing (sandbox) or live gateway server for transactions?',
                                                                             'value' => 'Live',
                                                                             'set_func' => 'tep_cfg_select_option(array(\'Live\', \'Sandbox\'), '),
                      'MODULE_PAYMENT_PAYPAL_PRO_HS_VERIFY_SSL' => array('title' => 'Verify SSL Certificate',
                                                                         'desc' => 'Verify gateway server SSL certificate on connection?',
                                                                         'value' => 'True',
                                                                         'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_PAYPAL_PRO_HS_PROXY' => array('title' => 'Proxy Server',
                                                                    'desc' => 'Send API requests through this proxy server. (host:port, eg: 123.45.67.89:8080 or proxy.example.com:8080)'),
                      'MODULE_PAYMENT_PAYPAL_PRO_HS_DEBUG_EMAIL' => array('title' => 'Debug E-Mail Address',
                                                                          'desc' => 'All parameters of an invalid transaction will be sent to this email address.'),
                      'MODULE_PAYMENT_PAYPAL_PRO_HS_SORT_ORDER' => array('title' => 'Sort order of display.',
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

      if ( MODULE_PAYMENT_PAYPAL_PRO_HS_VERIFY_SSL == 'True' ) {
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

      if ( tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_PROXY) ) {
        curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($curl, CURLOPT_PROXY, MODULE_PAYMENT_PAYPAL_PRO_HS_PROXY);
      }

      $result = curl_exec($curl);

      curl_close($curl);

      return $result;
    }

    function getTransactionDetails($id) {
      $params = array('USER' => MODULE_PAYMENT_PAYPAL_PRO_HS_API_USERNAME,
                      'PWD' => MODULE_PAYMENT_PAYPAL_PRO_HS_API_PASSWORD,
                      'SIGNATURE' => MODULE_PAYMENT_PAYPAL_PRO_HS_API_SIGNATURE,
                      'VERSION' => $this->api_version,
                      'METHOD' => 'GetTransactionDetails',
                      'TRANSACTIONID' => $id);

      $post_string = '';

      foreach ($params as $key => $value) {
        $post_string .= $key . '=' . urlencode(utf8_encode(trim($value))) . '&';
      }

      $post_string = substr($post_string, 0, -1);

      $response = $this->sendTransactionToGateway($this->api_url, $post_string);

      $response_array = array();
      parse_str($response, $response_array);

      if (($response_array['ACK'] != 'Success') && ($response_array['ACK'] != 'SuccessWithWarning')) {
        $this->sendDebugEmail($response_array);
      }

      return $response_array;
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

    function getTestLinkInfo() {
      $dialog_title = MODULE_PAYMENT_PAYPAL_PRO_HS_DIALOG_CONNECTION_TITLE;
      $dialog_button_close = MODULE_PAYMENT_PAYPAL_PRO_HS_DIALOG_CONNECTION_BUTTON_CLOSE;
      $dialog_success = MODULE_PAYMENT_PAYPAL_PRO_HS_DIALOG_CONNECTION_SUCCESS;
      $dialog_failed = MODULE_PAYMENT_PAYPAL_PRO_HS_DIALOG_CONNECTION_FAILED;
      $dialog_error = MODULE_PAYMENT_PAYPAL_PRO_HS_DIALOG_CONNECTION_ERROR;
      $dialog_connection_time = MODULE_PAYMENT_PAYPAL_PRO_HS_DIALOG_CONNECTION_TIME;

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

      $info = '<p><img src="images/icons/locked.gif" border="0">&nbsp;<a href="javascript:openTestConnectionDialog();" style="text-decoration: underline; font-weight: bold;">' . MODULE_PAYMENT_PAYPAL_PRO_HS_DIALOG_CONNECTION_LINK_TITLE . '</a></p>' .
              '<div id="testConnectionDialog" style="display: none;"><p>';

      if ( MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTION_SERVER == 'Live' ) {
        $info .= 'Live Server:<br />' . $this->api_url;
      } else {
        $info .= 'Sandbox Server:<br />' . $this->api_url;
      }

      $info .= '</p><div id="testConnectionDialogProgress"><p>' . MODULE_PAYMENT_PAYPAL_PRO_HS_DIALOG_CONNECTION_GENERAL_TEXT . '</p><div id="tcdprogressbar"></div></div></div>' .
               $js;

      return $info;
    }

    function getTestConnectionResult() {
      $params = array('USER' => MODULE_PAYMENT_PAYPAL_PRO_HS_API_USERNAME,
                      'PWD' => MODULE_PAYMENT_PAYPAL_PRO_HS_API_PASSWORD,
                      'SIGNATURE' => MODULE_PAYMENT_PAYPAL_PRO_HS_API_SIGNATURE,
                      'VERSION' => $paypal_pro_hs->api_version,
                      'METHOD' => 'BMCreateButton');

      $post_string = '';

      foreach ($params as $key => $value) {
        $post_string .= $key . '=' . urlencode(utf8_encode(trim($value))) . '&';
      }

      $post_string = substr($post_string, 0, -1);

      $response = $this->sendTransactionToGateway($this->api_url, $post_string);

      $response_array = array();
      parse_str($response, $response_array);

      if ( is_array($response_array) && isset($response_array['ACK']) ) {
        return 1;
      }

      return -1;
    }

    function verifyTransaction($is_ipn = false) {
      global $pphs_result, $currencies;

      $tx_order_id = $pphs_result['INVNUM'];
      $tx_customer_id = $pphs_result['CUSTOM'];
      $tx_transaction_id = $pphs_result['TRANSACTIONID'];
      $tx_payment_status = $pphs_result['PAYMENTSTATUS'];
      $tx_payer_status = $pphs_result['PAYERSTATUS'];
      $tx_amount = $pphs_result['AMT'];
      $tx_currency = $pphs_result['CURRENCYCODE'];
      $tx_pending_reason = (isset($pphs_result['PENDINGREASON'])) ? $pphs_result['PENDINGREASON'] : null;
      $tx_reason_code = (isset($pphs_result['REASONCODE'])) ? $pphs_result['REASONCODE'] : null;

      if ( is_numeric($tx_order_id) && ($tx_order_id > 0) && is_numeric($tx_customer_id) && ($tx_customer_id > 0) ) {
        $order_query = tep_db_query("select orders_id, orders_status, currency, currency_value from " . TABLE_ORDERS . " where orders_id = '" . (int)$tx_order_id . "' and customers_id = '" . (int)$tx_customer_id . "'");

        if ( tep_db_num_rows($order_query) === 1 ) {
          $order = tep_db_fetch_array($order_query);

          $new_order_status = DEFAULT_ORDERS_STATUS_ID;

          if ( $order['orders_status'] != MODULE_PAYMENT_PAYPAL_PRO_HS_PREPARE_ORDER_STATUS_ID ) {
            $new_order_status = $order['orders_status'];
          }

          $total_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order['orders_id'] . "' and class = 'ot_total' limit 1");
          $total = tep_db_fetch_array($total_query);

          $comment_status = 'Transaction ID: ' . $tx_transaction_id . '; ' .
                            $tx_payment_status . ' (' . ucfirst($tx_payer_status) . '; ' . $currencies->format($tx_amount, false, $tx_currency) . ')';

          if ( $tx_payment_status == 'Pending' ) {
            $comment_status .= '; ' . $tx_pending_reason;
          } elseif ( ($tx_payment_status == 'Reversed') || ($tx_payment_status == 'Refunded') ) {
            $comment_status .= '; ' . $tx_reason_code;
          }

          if ( $tx_amount != number_format($total['value'] * $order['currency_value'], $currencies->get_decimal_places($order['currency'])) ) {
            $comment_status .= '; PayPal transaction value (' . $tx_amount . ') does not match order value (' . number_format($total['value'] * $order['currency_value'], $currencies->get_decimal_places($order['currency'])) . ')';
          } elseif ( $tx_payment_status == 'Completed' ) {
            $new_order_status = (MODULE_PAYMENT_PAYPAL_PRO_HS_ORDER_STATUS_ID > 0 ? MODULE_PAYMENT_PAYPAL_PRO_HS_ORDER_STATUS_ID : $new_order_status);
          }

          tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . (int)$new_order_status . "', last_modified = now() where orders_id = '" . (int)$order['orders_id'] . "'");

          if ( $is_ipn === true ) {
            $source = 'PayPal IPN Verified';
          } else {
            $source = 'PayPal Verified';
          }

          $sql_data_array = array('orders_id' => (int)$order['orders_id'],
                                  'orders_status_id' => MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTIONS_ORDER_STATUS_ID,
                                  'date_added' => 'now()',
                                  'customer_notified' => '0',
                                  'comments' => $source . ' [' . tep_output_string_protected($comment_status) . ']');

          tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        }
      }
    }

    function sendDebugEmail($response = array()) {
      global $HTTP_POST_VARS, $HTTP_GET_VARS;

      if (tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_DEBUG_EMAIL)) {
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
          tep_mail('', MODULE_PAYMENT_PAYPAL_PRO_HS_DEBUG_EMAIL, 'PayPal Payments Pro (Hosted Solution) Debug E-Mail', trim($email_body), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
        }
      }
    }
  }
?>
