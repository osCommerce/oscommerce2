<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class rbsworldpay_hosted {
    var $code, $title, $description, $enabled;

    function rbsworldpay_hosted() {
      global $order;

      $this->signature = 'rbs|worldpay_hosted|2.0|2.3';
      $this->api_version = '4.6';

      $this->code = 'rbsworldpay_hosted';
      $this->title = MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TEXT_DESCRIPTION;
      $this->sort_order = defined('MODULE_PAYMENT_RBSWORLDPAY_HOSTED_SORT_ORDER') ? MODULE_PAYMENT_RBSWORLDPAY_HOSTED_SORT_ORDER : 0;
      $this->enabled = defined('MODULE_PAYMENT_RBSWORLDPAY_HOSTED_STATUS') && (MODULE_PAYMENT_RBSWORLDPAY_HOSTED_STATUS == 'True') ? true : false;
      $this->order_status = defined('MODULE_PAYMENT_RBSWORLDPAY_HOSTED_PREPARE_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_RBSWORLDPAY_HOSTED_PREPARE_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_RBSWORLDPAY_HOSTED_PREPARE_ORDER_STATUS_ID : 0;

      if ( defined('MODULE_PAYMENT_RBSWORLDPAY_HOSTED_STATUS') ) {
        if ( MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TESTMODE == 'True' ) {
          $this->title .= ' [Test]';
          $this->public_title .= ' (' . $this->code . '; Test)';
        }

        if ( MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TESTMODE == 'True' ) {
          $this->form_action_url = 'https://secure-test.worldpay.com/wcc/purchase';
        } else {
          $this->form_action_url = 'https://secure.worldpay.com/wcc/purchase';
        }
      }

      if ( $this->enabled === true ) {
        if ( !tep_not_null(MODULE_PAYMENT_RBSWORLDPAY_HOSTED_INSTALLATION_ID) ) {
          $this->description = '<div class="secWarning">' . MODULE_PAYMENT_RBSWORLDPAY_HOSTED_ERROR_ADMIN_CONFIGURATION . '</div>' . $this->description;

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

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_RBSWORLDPAY_HOSTED_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_RBSWORLDPAY_HOSTED_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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
      global $cart_RBS_Worldpay_Hosted_ID;

      if (tep_session_is_registered('cart_RBS_Worldpay_Hosted_ID')) {
        $order_id = substr($cart_RBS_Worldpay_Hosted_ID, strpos($cart_RBS_Worldpay_Hosted_ID, '-')+1);

        $check_query = tep_db_query('select orders_id from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '" limit 1');

        if (tep_db_num_rows($check_query) < 1) {
          tep_db_query('delete from ' . TABLE_ORDERS . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . ' where orders_id = "' . (int)$order_id . '"');

          tep_session_unregister('cart_RBS_Worldpay_Hosted_ID');
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
      global $cartID, $cart_RBS_Worldpay_Hosted_ID, $customer_id, $languages_id, $order, $order_total_modules;

      $insert_order = false;

      if (tep_session_is_registered('cart_RBS_Worldpay_Hosted_ID')) {
        $order_id = substr($cart_RBS_Worldpay_Hosted_ID, strpos($cart_RBS_Worldpay_Hosted_ID, '-')+1);

        $curr_check = tep_db_query("select currency from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
        $curr = tep_db_fetch_array($curr_check);

        if ( ($curr['currency'] != $order->info['currency']) || ($cartID != substr($cart_RBS_Worldpay_Hosted_ID, 0, strlen($cartID))) ) {
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
          reset($order_total_modules->modules);
          while (list(, $value) = each($order_total_modules->modules)) {
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

        $cart_RBS_Worldpay_Hosted_ID = $cartID . '-' . $insert_id;
        tep_session_register('cart_RBS_Worldpay_Hosted_ID');
      }

      return false;
    }

    function process_button() {
      global $order, $currency, $languages_id, $language, $customer_id, $cart_RBS_Worldpay_Hosted_ID;

      $order_id = substr($cart_RBS_Worldpay_Hosted_ID, strpos($cart_RBS_Worldpay_Hosted_ID, '-')+1);

      $lang_query = tep_db_query("select code from " . TABLE_LANGUAGES . " where languages_id = '" . (int)$languages_id . "'");
      $lang = tep_db_fetch_array($lang_query);

      $process_button_string = tep_draw_hidden_field('instId', MODULE_PAYMENT_RBSWORLDPAY_HOSTED_INSTALLATION_ID) .
                               tep_draw_hidden_field('cartId', $order_id) .
                               tep_draw_hidden_field('amount', $this->format_raw($order->info['total'])) .
                               tep_draw_hidden_field('currency', $currency) .
                               tep_draw_hidden_field('desc', STORE_NAME) .
                               tep_draw_hidden_field('name', $order->billing['firstname'] . ' ' . $order->billing['lastname']) .
                               tep_draw_hidden_field('address1', $order->billing['street_address']) .
                               tep_draw_hidden_field('town', $order->billing['city']) .
                               tep_draw_hidden_field('region', $order->billing['state']) .
                               tep_draw_hidden_field('postcode', $order->billing['postcode']) .
                               tep_draw_hidden_field('country', $order->billing['country']['iso_code_2']) .
                               tep_draw_hidden_field('tel', $order->customer['telephone']) .
                               tep_draw_hidden_field('email', $order->customer['email_address']) .
                               tep_draw_hidden_field('fixContact', 'Y') .
                               tep_draw_hidden_field('hideCurrency', 'true') .
                               tep_draw_hidden_field('lang', strtoupper($lang['code'])) .
                               tep_draw_hidden_field('signatureFields', 'amount:currency:cartId') .
                               tep_draw_hidden_field('signature', md5(MODULE_PAYMENT_RBSWORLDPAY_HOSTED_MD5_PASSWORD . ':' . $this->format_raw($order->info['total']) . ':' . $currency . ':' . $order_id)) .
                               tep_draw_hidden_field('MC_callback', tep_href_link('ext/modules/payment/rbsworldpay/hosted_callback.php', '', 'SSL', false)) .
                               tep_draw_hidden_field('M_sid', tep_session_id()) .
                               tep_draw_hidden_field('M_cid', $customer_id) .
                               tep_draw_hidden_field('M_lang', $language) .
                               tep_draw_hidden_field('M_hash', md5(tep_session_id() . $customer_id . $order_id . $language . number_format($order->info['total'], 2) . MODULE_PAYMENT_RBSWORLDPAY_HOSTED_MD5_PASSWORD));

      if (MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TRANSACTION_METHOD == 'Pre-Authorization') {
        $process_button_string .= tep_draw_hidden_field('authMode', 'E');
      }

      if (MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TESTMODE == 'True') {
        $process_button_string .= tep_draw_hidden_field('testMode', '100');
      }

      return $process_button_string;
    }

    function before_process() {
      global $HTTP_GET_VARS, $customer_id, $language, $order, $order_totals, $sendto, $billto, $languages_id, $payment, $currencies, $cart, $cart_RBS_Worldpay_Hosted_ID;
      global $$payment;

      $order_id = substr($cart_RBS_Worldpay_Hosted_ID, strpos($cart_RBS_Worldpay_Hosted_ID, '-')+1);

      if (!isset($HTTP_GET_VARS['hash']) || ($HTTP_GET_VARS['hash'] != md5(tep_session_id() . $customer_id . $order_id . $language . number_format($order->info['total'], 2) . MODULE_PAYMENT_RBSWORLDPAY_HOSTED_MD5_PASSWORD))) {
        $this->sendDebugEmail();

        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
      }

      $order_query = tep_db_query("select orders_status from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "' and customers_id = '" . (int)$customer_id . "'");

      if (!tep_db_num_rows($order_query)) {
        $this->sendDebugEmail();

        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
      }

      $order = tep_db_fetch_array($order_query);

      $order_status_id = (MODULE_PAYMENT_RBSWORLDPAY_HOSTED_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_RBSWORLDPAY_HOSTED_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID);

      if ($order['orders_status'] == MODULE_PAYMENT_RBSWORLDPAY_HOSTED_PREPARE_ORDER_STATUS_ID) {
        tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . $order_status_id . "', last_modified = now() where orders_id = '" . (int)$order_id . "'");

        $sql_data_array = array('orders_id' => $order_id,
                                'orders_status_id' => $order_status_id,
                                'date_added' => 'now()',
                                'customer_notified' => (SEND_EMAILS == 'true') ? '1' : '0',
                                'comments' => $order->info['comments']);

        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
      } else {
        $order_status_query = tep_db_query("select orders_status_history_id from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$order_id . "' and orders_status_id = '" . (int)$order_status_id . "' and comments = '' order by date_added desc limit 1");

        if (tep_db_num_rows($order_status_query)) {
          $order_status = tep_db_fetch_array($order_status_query);

          $sql_data_array = array('customer_notified' => (SEND_EMAILS == 'true') ? '1' : '0',
                                  'comments' => $order->info['comments']);

          tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array, 'update', "orders_status_history_id = '" . (int)$order_status['orders_status_history_id'] . "'");
        }
      }

      $trans_result = 'WorldPay: Transaction Verified';

      if (MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TESTMODE == 'True') {
        $trans_result .= "\n" . MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TEXT_WARNING_DEMO_MODE;
      }

      $sql_data_array = array('orders_id' => $order_id,
                              'orders_status_id' => MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TRANSACTIONS_ORDER_STATUS_ID,
                              'date_added' => 'now()',
                              'customer_notified' => '0',
                              'comments' => $trans_result);

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

      tep_session_unregister('cart_RBS_Worldpay_Hosted_ID');

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
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_RBSWORLDPAY_HOSTED_STATUS'");
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
      if (!defined('MODULE_PAYMENT_RBSWORLDPAY_HOSTED_PREPARE_ORDER_STATUS_ID')) {
        $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Preparing [WorldPay]' limit 1");

        if (tep_db_num_rows($check_query) < 1) {
          $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
          $status = tep_db_fetch_array($status_query);

          $status_id = $status['status_id']+1;

          $languages = tep_get_languages();

          foreach ($languages as $lang) {
            tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $lang['id'] . "', 'Preparing [WorldPay]')");
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
        $status_id = MODULE_PAYMENT_RBSWORLDPAY_HOSTED_PREPARE_ORDER_STATUS_ID;
      }

      if (!defined('MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TRANSACTIONS_ORDER_STATUS_ID')) {
        $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'WorldPay [Transactions]' limit 1");

        if (tep_db_num_rows($check_query) < 1) {
          $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
          $status = tep_db_fetch_array($status_query);

          $tx_status_id = $status['status_id']+1;

          $languages = tep_get_languages();

          foreach ($languages as $lang) {
            tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $tx_status_id . "', '" . $lang['id'] . "', 'WorldPay [Transactions]')");
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
        $tx_status_id = MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TRANSACTIONS_ORDER_STATUS_ID;
      }

      $params = array('MODULE_PAYMENT_RBSWORLDPAY_HOSTED_STATUS' => array('title' => 'Enable WorldPay Hosted Payment Pages',
                                                                          'desc' => 'Do you want to accept WorldPay Hosted Payment Pages payments?',
                                                                          'value' => 'True',
                                                                          'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_RBSWORLDPAY_HOSTED_INSTALLATION_ID' => array('title' => 'Installation ID',
                                                                                   'desc' => 'The WorldPay Account Installation ID to accept payments for'),
                      'MODULE_PAYMENT_RBSWORLDPAY_HOSTED_CALLBACK_PASSWORD' => array('title' => 'Callback Password',
                                                                                     'desc' => 'The password sent to the callback processing script. This must be the same value defined in the WorldPay Merchant Interface.'),
                      'MODULE_PAYMENT_RBSWORLDPAY_HOSTED_MD5_PASSWORD' => array('title' => 'MD5 Password',
                                                                                'desc' => 'The MD5 password to verify transactions with. This must be the same value defined in the WorldPay Merchant Interface.'),
                      'MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TRANSACTION_METHOD' => array('title' => 'Transaction Method',
                                                                                      'desc' => 'The processing method to use for each transaction.',
                                                                                      'value' => 'Capture',
                                                                                      'set_func' => 'tep_cfg_select_option(array(\'Pre-Authorization\', \'Capture\'), '),
                      'MODULE_PAYMENT_RBSWORLDPAY_HOSTED_PREPARE_ORDER_STATUS_ID' => array('title' => 'Set Preparing Order Status',
                                                                                           'desc' => 'Set the status of prepared orders made with this payment module to this value',
                                                                                           'value' => $status_id,
                                                                                           'set_func' => 'tep_cfg_pull_down_order_statuses(',
                                                                                           'use_func' => 'tep_get_order_status_name'),
                      'MODULE_PAYMENT_RBSWORLDPAY_HOSTED_ORDER_STATUS_ID' => array('title' => 'Set Order Status',
                                                                                   'desc' => 'Set the status of orders made with this payment module to this value',
                                                                                   'value' => '0',
                                                                                   'set_func' => 'tep_cfg_pull_down_order_statuses(',
                                                                                   'use_func' => 'tep_get_order_status_name'),
                      'MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TRANSACTIONS_ORDER_STATUS_ID' => array('title' => 'Transactions Order Status Level',
                                                                                                'desc' => 'Include WorldPay transaction information in this order status level.',
                                                                                                'value' => $tx_status_id,
                                                                                                'use_func' => 'tep_get_order_status_name',
                                                                                                'set_func' => 'tep_cfg_pull_down_order_statuses('),
                      'MODULE_PAYMENT_RBSWORLDPAY_HOSTED_ZONE' => array('title' => 'Payment Zone',
                                                                        'desc' => 'If a zone is selected, only enable this payment method for that zone.',
                                                                        'value' => '0',
                                                                        'use_func' => 'tep_get_zone_class_title',
                                                                        'set_func' => 'tep_cfg_pull_down_zone_classes('),
                      'MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TESTMODE' => array('title' => 'Test Mode',
                                                                            'desc' => 'Should transactions be processed in test mode?',
                                                                            'value' => 'False',
                                                                            'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_RBSWORLDPAY_HOSTED_DEBUG_EMAIL' => array('title' => 'Debug E-Mail Address',
                                                                               'desc' => 'All parameters of an invalid transaction will be sent to this email address if one is entered.'),
                      'MODULE_PAYMENT_RBSWORLDPAY_HOSTED_SORT_ORDER' => array('title' => 'Sort order of display.',
                                                                              'desc' => 'Sort order of display. Lowest is displayed first.',
                                                                              'value' => '0'));

      return $params;
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

    function sendDebugEmail($response = array()) {
      global $HTTP_POST_VARS, $HTTP_GET_VARS;

      if (tep_not_null(MODULE_PAYMENT_RBSWORLDPAY_HOSTED_DEBUG_EMAIL)) {
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
          tep_mail('', MODULE_PAYMENT_RBSWORLDPAY_HOSTED_DEBUG_EMAIL, 'WorldPay Hosted Debug E-Mail', trim($email_body), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
        }
      }
    }
  }
?>
