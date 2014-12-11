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

  class paypal_pro_hs {
    var $code, $title, $description, $enabled, $_app;

    function paypal_pro_hs() {
      global $order;

      $this->_app = new OSCOM_PayPal();
      $this->_app->loadLanguageFile('modules/HS/HS.php');

      $this->signature = 'paypal|paypal_pro_hs|' . $this->_app->getVersion() . '|2.3';
      $this->api_version = $this->_app->getApiVersion();

      $this->code = 'paypal_pro_hs';
      $this->title = $this->_app->getDef('module_hs_title');
      $this->public_title = $this->_app->getDef('module_hs_public_title');
      $this->description = '<div align="center">' . $this->_app->drawButton($this->_app->getDef('module_hs_legacy_admin_app_button'), tep_href_link('paypal.php', 'action=configure&module=HS'), 'primary', null, true) . '</div>';
      $this->sort_order = defined('OSCOM_APP_PAYPAL_HS_SORT_ORDER') ? OSCOM_APP_PAYPAL_HS_SORT_ORDER : 0;
      $this->enabled = defined('OSCOM_APP_PAYPAL_HS_STATUS') && in_array(OSCOM_APP_PAYPAL_HS_STATUS, array('1', '0')) ? true : false;
      $this->order_status = defined('OSCOM_APP_PAYPAL_HS_PREPARE_ORDER_STATUS_ID') && ((int)OSCOM_APP_PAYPAL_HS_PREPARE_ORDER_STATUS_ID > 0) ? (int)OSCOM_APP_PAYPAL_HS_PREPARE_ORDER_STATUS_ID : 0;

      if ( defined('OSCOM_APP_PAYPAL_HS_STATUS') ) {
        if ( OSCOM_APP_PAYPAL_HS_STATUS == '0' ) {
          $this->title .= ' [Sandbox]';
          $this->public_title .= ' (' . $this->code . '; Sandbox)';
        }
      }

      if ( !function_exists('curl_init') ) {
        $this->description .= '<div class="secWarning">' . $this->_app->getDef('module_hs_error_curl') . '</div>';

        $this->enabled = false;
      }

      if ( $this->enabled === true ) {
        if ( (OSCOM_APP_PAYPAL_GATEWAY == '1') && !$this->_app->hasCredentials('HS') ) { // PayPal
          $this->description .= '<div class="secWarning">' . $this->_app->getDef('module_hs_error_credentials') . '</div>';

          $this->enabled = false;
        } elseif ( OSCOM_APP_PAYPAL_GATEWAY == '0' ) { // Payflow
          $this->description .= '<div class="secWarning">' . $this->_app->getDef('module_hs_error_payflow') . '</div>';

          $this->enabled = false;
        }
      }

      if ( $this->enabled === true ) {
        if ( isset($order) && is_object($order) ) {
          $this->update_status();
        }
      }
    }

    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)OSCOM_APP_PAYPAL_HS_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . OSCOM_APP_PAYPAL_HS_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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

        $params = array('buyer_email' => $order->customer['email_address'],
                        'cancel_return' => tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'),
                        'currency_code' => $currency,
                        'invoice' => $order_id,
                        'custom' => $customer_id,
                        'paymentaction' => OSCOM_APP_PAYPAL_HS_TRANSACTION_METHOD == '1' ? 'sale' : 'authorization',
                        'return' => tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'),
                        'notify_url' => tep_href_link('ext/modules/payment/paypal/pro_hosted_ipn.php', '', 'SSL', false, false),
                        'shipping' => $this->_app->formatCurrencyRaw($order->info['shipping_cost']),
                        'tax' => $this->_app->formatCurrencyRaw($order->info['tax']),
                        'subtotal' => $this->_app->formatCurrencyRaw($order->info['total'] - $order->info['shipping_cost'] - $order->info['tax']),
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

        $return_link_title = $this->_app->getDef('module_hs_button_return_to_store', array('storename' => STORE_NAME));

        if ( strlen($return_link_title) <= 60 ) {
          $params['cbt'] = $return_link_title;
        }

        $pphs_result = $this->_app->getApiResult('APP', 'BMCreateButton', $params, (OSCOM_APP_PAYPAL_HS_STATUS == '1') ? 'live' : 'sandbox');

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

// include jquery if it doesn't exist in the template
      $output = <<<EOD
<iframe src="{$iframe_url}" width="570px" height="540px" frameBorder="0" scrolling="no"></iframe>
<script>
if ( typeof jQuery == 'undefined' ) {
  document.write('<scr' + 'ipt src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></scr' + 'ipt>');
}
</script>

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
        $result = $this->_app->getApiResult('APP', 'GetTransactionDetails', array('TRANSACTIONID' => $HTTP_GET_VARS['tx']), (OSCOM_APP_PAYPAL_HS_STATUS == '1') ? 'live' : 'sandbox');
      } elseif ( isset($HTTP_POST_VARS['txn_id']) && !empty($HTTP_POST_VARS['txn_id']) ) { // paypal payment
        $result = $this->_app->getApiResult('APP', 'GetTransactionDetails', array('TRANSACTIONID' => $HTTP_POST_VARS['txn_id']), (OSCOM_APP_PAYPAL_HS_STATUS == '1') ? 'live' : 'sandbox');
      }

      if ( OSCOM_APP_PAYPAL_GATEWAY == '0' ) { // Payflow
        echo '<pre>';
        var_dump($HTTP_GET_VARS);
        var_dump($HTTP_POST_VARS);
        var_dump($result);
        exit;
      }

      if ( !in_array($result['ACK'], array('Success', 'SuccessWithWarning')) ) {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'error_message=' . stripslashes($result['L_LONGMESSAGE0'])));
      }

      $order_id = substr($cart_PayPal_Pro_HS_ID, strpos($cart_PayPal_Pro_HS_ID, '-')+1);

      $seller_accounts = array($this->_app->getCredentials('HS', 'email'));

      if ( tep_not_null($this->_app->getCredentials('HS', 'email_primary')) ) {
        $seller_accounts[] = $this->_app->getCredentials('HS', 'email_primary');
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

      if ( $check['orders_status'] != OSCOM_APP_PAYPAL_HS_PREPARE_ORDER_STATUS_ID ) {
        $new_order_status = $check['orders_status'];
      }

      if ( (OSCOM_APP_PAYPAL_HS_ORDER_STATUS_ID > 0) && ($check['orders_status'] == OSCOM_APP_PAYPAL_HS_ORDER_STATUS_ID) ) {
        $new_order_status = OSCOM_APP_PAYPAL_HS_ORDER_STATUS_ID;
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

      $error = array('title' => $this->_app->getDef('module_hs_error_general_title'),
                     'error' => $this->_app->getDef('module_hs_error_general'));

      if ( tep_session_is_registered('pphs_error_msg') ) {
        $error['error'] = $pphs_error_msg;

        tep_session_unregister('pphs_error_msg');
      }

      return $error;
    }

    function check() {
      $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'OSCOM_APP_PAYPAL_HS_STATUS'");
      if ( tep_db_num_rows($check_query) ) {
        $check = tep_db_fetch_array($check_query);

        return tep_not_null($check['configuration_value']);
      }

      return false;
    }

    function install() {
      tep_redirect(tep_href_link('paypal.php', 'action=configure&subaction=install&module=HS'));
    }

    function remove() {
      tep_redirect(tep_href_link('paypal.php', 'action=configure&subaction=uninstall&module=HS'));
    }

    function keys() {
      return array('OSCOM_APP_PAYPAL_HS_SORT_ORDER');
    }

    function verifyTransaction($is_ipn = false) {
      global $pphs_result, $currencies;

      $tx_order_id = $pphs_result['INVNUM'];
      $tx_customer_id = $pphs_result['CUSTOM'];
      $tx_transaction_id = $pphs_result['TRANSACTIONID'];
      $tx_payment_status = $pphs_result['PAYMENTSTATUS'];
      $tx_payment_type = $pphs_result['PAYMENTTYPE'];
      $tx_payer_status = $pphs_result['PAYERSTATUS'];
      $tx_address_status = $pphs_result['ADDRESSSTATUS'];
      $tx_amount = $pphs_result['AMT'];
      $tx_currency = $pphs_result['CURRENCYCODE'];
      $tx_pending_reason = (isset($pphs_result['PENDINGREASON'])) ? $pphs_result['PENDINGREASON'] : null;

      if ( is_numeric($tx_order_id) && ($tx_order_id > 0) && is_numeric($tx_customer_id) && ($tx_customer_id > 0) ) {
        $order_query = tep_db_query("select orders_id, orders_status, currency, currency_value from " . TABLE_ORDERS . " where orders_id = '" . (int)$tx_order_id . "' and customers_id = '" . (int)$tx_customer_id . "'");

        if ( tep_db_num_rows($order_query) === 1 ) {
          $order = tep_db_fetch_array($order_query);

          $new_order_status = DEFAULT_ORDERS_STATUS_ID;

          if ( $order['orders_status'] != OSCOM_APP_PAYPAL_HS_PREPARE_ORDER_STATUS_ID ) {
            $new_order_status = $order['orders_status'];
          }

          $total_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order['orders_id'] . "' and class = 'ot_total' limit 1");
          $total = tep_db_fetch_array($total_query);

          $comment_status = 'Transaction ID: ' . tep_output_string_protected($tx_transaction_id) . "\n" .
                            'Payer Status: ' . tep_output_string_protected($tx_payer_status) . "\n" .
                            'Address Status: ' . tep_output_string_protected($tx_address_status) . "\n" .
                            'Payment Status: ' . tep_output_string_protected($tx_payment_status) . "\n" .
                            'Payment Type: ' . tep_output_string_protected($tx_payment_type) . "\n" .
                            'Pending Reason: ' . tep_output_string_protected($tx_pending_reason);

          if ( $tx_amount != $this->_app->formatCurrencyRaw($total['value'], $order['currency'], $order['currency_value']) ) {
            $comment_status .= "\n" . 'OSCOM Error Total Mismatch: PayPal transaction value (' . tep_output_string_protected($tx_amount) . ') does not match order value (' . $this->_app->formatCurrencyRaw($total['value'], $order['currency'], $order['currency_value']) . ')';
          } elseif ( $tx_payment_status == 'Completed' ) {
            $new_order_status = (OSCOM_APP_PAYPAL_HS_ORDER_STATUS_ID > 0 ? OSCOM_APP_PAYPAL_HS_ORDER_STATUS_ID : $new_order_status);
          }

          tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . (int)$new_order_status . "', last_modified = now() where orders_id = '" . (int)$order['orders_id'] . "'");

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
