<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class moneybookers {
    var $code, $title, $description, $enabled;

    var $_sid; // Moneybookers transaction session ID
    var $_mbcartID = 'cart_Moneybookers_ID';
    var $_payment_method = '';
    var $_payment_method_image = 'by_ewallet_90x45.gif';

// class constructor
    function moneybookers() {
      global $order;

      $this->signature = 'moneybookers|moneybookers|1.0|2.3';

      $this->code = 'moneybookers';
      $this->title = MODULE_PAYMENT_MONEYBOOKERS_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_MONEYBOOKERS_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_MONEYBOOKERS_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_MONEYBOOKERS_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_MONEYBOOKERS_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_MONEYBOOKERS_PREPARE_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_MONEYBOOKERS_PREPARE_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

      if (defined('MODULE_PAYMENT_MONEYBOOKERS_IFRAME') && (MODULE_PAYMENT_MONEYBOOKERS_IFRAME == 'True')) {
        $this->form_action_url = tep_href_link('ext/modules/payment/moneybookers/checkout.php', '', 'SSL');
      } else {
        $this->form_action_url = 'https://www.moneybookers.com/app/payment.pl';
      }
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_MONEYBOOKERS_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_MONEYBOOKERS_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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

    function _deletePreparing() {
      if (tep_session_is_registered($this->_mbcartID)) {
        $order_id = substr($GLOBALS[$this->_mbcartID], strpos($GLOBALS[$this->_mbcartID], '-')+1);

        $check_query = tep_db_query('select orders_id from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '" limit 1');

        if (tep_db_num_rows($check_query) < 1) {
          tep_db_query('delete from ' . TABLE_ORDERS . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . ' where orders_id = "' . (int)$order_id . '"');

          tep_session_unregister($this->_mbcartID);
        }
      }
    }

    function selection() {
      $this->_deletePreparing();

      return array('id' => $this->code,
                   'module' => $this->public_title . (!empty($this->_payment_method_image) ? '<br />' . tep_image('ext/modules/payment/moneybookers/logos/' . $this->_payment_method_image, $this->public_title) : ''));
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

    function _prepareOrder() {
      global $cartID, $customer_id, $languages_id, $order, $order_total_modules, $currency;

      $insert_order = false;

      if (tep_session_is_registered($this->_mbcartID)) {
        $order_id = substr($GLOBALS[$this->_mbcartID], strpos($GLOBALS[$this->_mbcartID], '-')+1);

        $curr_check = tep_db_query("select currency from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
        $curr = tep_db_fetch_array($curr_check);

        if ( ($curr['currency'] != $order->info['currency']) || ($cartID != substr($GLOBALS[$this->_mbcartID], 0, strlen($cartID))) ) {
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

        $GLOBALS[$this->_mbcartID] = $cartID . '-' . $insert_id;
        tep_session_register($this->_mbcartID);
      }
    }

    function confirmation() {
      global $customer_id, $order, $currency;

      if (tep_session_is_registered('cartID')) {
        $this->_prepareOrder();

        $parameters = array('pay_to_email' => MODULE_PAYMENT_MONEYBOOKERS_PAY_TO,
                            'recipient_description' => STORE_NAME,
                            'transaction_id' => substr($GLOBALS[$this->_mbcartID], strpos($GLOBALS[$this->_mbcartID], '-')+1),
                            'return_url' => tep_href_link(FILENAME_CHECKOUT_PROCESS, 'osig=' . md5(MODULE_PAYMENT_MONEYBOOKERS_SECRET_WORD . $GLOBALS[$this->_mbcartID]), 'SSL'),
                            'return_url_text' => MODULE_PAYMENT_MONEYBOOKERS_RETURN_TEXT,
                            'return_url_target' => 1,
                            'cancel_url' => tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'),
                            'cancel_url_target' => 1,
                            'status_url' => tep_href_link('ext/modules/payment/moneybookers/callback.php', '', 'SSL', false, false),
                            'language' => MODULE_PAYMENT_MONEYBOOKERS_LANGUAGE_CODE,
                            'prepare_only' => '1',
                            'pay_from_email' => $order->customer['email_address'],
                            'first_name' => $order->billing['firstname'],
                            'last_name' => $order->billing['lastname'],
                            'address' => $order->billing['street_address'],
                            'phone_number' => $order->customer['telephone'],
                            'postal_code' => $order->billing['postcode'],
                            'city' => $order->billing['city'],
                            'state' => $order->billing['state'],
                            'country' => $order->billing['country']['iso_code_3'],
                            'amount' => $this->format_raw($order->info['total']),
                            'currency' => $currency,
                            'hide_login' => '1',
                            'merchant_fields' => 'osc_custid,referring_platform',
                            'osc_custid' => $customer_id,
                            'referring_platform' => 'osCommerce|' . $this->signature);

        if (MODULE_PAYMENT_MONEYBOOKERS_IFRAME == 'False') {
          $parameters['logo_url'] = MODULE_PAYMENT_MONEYBOOKERS_STORE_IMAGE;
        }

        if (tep_not_null(MODULE_PAYMENT_MONEYBOOKERS_DEBUG_EMAIL)) {
          $parameters['status_url2'] = MODULE_PAYMENT_MONEYBOOKERS_DEBUG_EMAIL;
        }

        if (tep_not_null($this->_payment_method)) {
          $parameters['payment_methods'] = $this->_payment_method;
        }

        $params_string = '';

        reset($parameters);
        while (list($key, $value) = each($parameters)) {
          $params_string .= $key . '=' . urlencode($value) . '&';
        }

        $params_string = substr($params_string, 0, -1);

        $response = $this->sendTransactionToGateway('https://www.moneybookers.com/app/payment.pl', $params_string, true);

        $m = array();

        preg_match('/^Set-Cookie: SESSION_ID=(.*?);/m', $response, $m);

        if ( !empty($m) ) {
          $this->_sid = $m[1];
        }
      }
    }

    function process_button() {
      if ( isset($this->_sid) ) {
        return tep_draw_hidden_field('sid', $this->_sid);
      }
    }

    function before_process() {
      global $HTTP_GET_VARS, $customer_id, $order, $order_totals, $sendto, $billto, $languages_id, $payment, $currencies, $cart;
      global $$payment;

      $pass = false;

      if (isset($HTTP_GET_VARS['transaction_id']) && isset($HTTP_GET_VARS['msid'])) {
        if ($HTTP_GET_VARS['transaction_id'] == substr($GLOBALS[$this->_mbcartID], strpos($GLOBALS[$this->_mbcartID], '-')+1)) {
          if ($HTTP_GET_VARS['msid'] == strtoupper(md5(MODULE_PAYMENT_MONEYBOOKERS_MERCHANT_ID . $HTTP_GET_VARS['transaction_id'] . strtoupper(md5(MODULE_PAYMENT_MONEYBOOKERS_SECRET_WORD))))) {
            $pass = true;
          }
        }
      } elseif (isset($HTTP_GET_VARS['osig']) && ($HTTP_GET_VARS['osig'] == md5(MODULE_PAYMENT_MONEYBOOKERS_SECRET_WORD . $GLOBALS[$this->_mbcartID]))) {
        $pass = true;
      }

      if ($pass == true) {
        $order_id = substr($GLOBALS[$this->_mbcartID], strpos($GLOBALS[$this->_mbcartID], '-')+1);

        $check_query = tep_db_query("select orders_status from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
        if (tep_db_num_rows($check_query)) {
          $check = tep_db_fetch_array($check_query);

          if ($check['orders_status'] == MODULE_PAYMENT_MONEYBOOKERS_PREPARE_ORDER_STATUS_ID) {
            $sql_data_array = array('orders_id' => $order_id,
                                    'orders_status_id' => MODULE_PAYMENT_MONEYBOOKERS_PREPARE_ORDER_STATUS_ID,
                                    'date_added' => 'now()',
                                    'customer_notified' => '0',
                                    'comments' => '');

            tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
          }
        }

        tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . (MODULE_PAYMENT_MONEYBOOKERS_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_MONEYBOOKERS_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID) . "', last_modified = now() where orders_id = '" . (int)$order_id . "'");

        $sql_data_array = array('orders_id' => $order_id,
                                'orders_status_id' => (MODULE_PAYMENT_MONEYBOOKERS_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_MONEYBOOKERS_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID),
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

        tep_session_unregister($this->_mbcartID);

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
      } else {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
      }
    }

    function after_process() {
      return false;
    }

    function output_error() {
      return false;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_MONEYBOOKERS_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      global $HTTP_GET_VARS;

      if ( !isset($HTTP_GET_VARS['active']) || ($HTTP_GET_VARS['active'] != 'true') ) {
        tep_redirect(tep_href_link('ext/modules/payment/moneybookers/activation.php', 'selected_box=modules&set=payment'));
      }

// Preparing order status
      $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Preparing [Moneybookers]' limit 1");

      if (tep_db_num_rows($check_query) < 1) {
        $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
        $status = tep_db_fetch_array($status_query);

        $preparing_status_id = $status['status_id']+1;

        $languages = tep_get_languages();

        foreach ($languages as $lang) {
          tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $preparing_status_id . "', '" . $lang['id'] . "', 'Preparing [Moneybookers]')");
        }

        $flags_query = tep_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
        if (tep_db_num_rows($flags_query) == 1) {
          tep_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 0 and downloads_flag = 0 where orders_status_id = '" . $preparing_status_id . "'");
        }
      } else {
        $check = tep_db_fetch_array($check_query);

        $preparing_status_id = $check['orders_status_id'];
      }

// Transactions order status
      $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Transaction [Moneybookers]' limit 1");

      if (tep_db_num_rows($check_query) < 1) {
        $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
        $status = tep_db_fetch_array($status_query);

        $transactions_status_id = $status['status_id']+1;

        $languages = tep_get_languages();

        foreach ($languages as $lang) {
          tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $transactions_status_id . "', '" . $lang['id'] . "', 'Transaction [Moneybookers]')");
        }

        $flags_query = tep_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
        if (tep_db_num_rows($flags_query) == 1) {
          tep_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 0 and downloads_flag = 0 where orders_status_id = '" . $transactions_status_id . "'");
        }
      } else {
        $check = tep_db_fetch_array($check_query);

        $transactions_status_id = $check['orders_status_id'];
      }

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Moneybookers eWallet', 'MODULE_PAYMENT_MONEYBOOKERS_STATUS', 'False', 'Do you want to accept Moneybookers eWallet payments?', '6', '3', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('E-Mail Address', 'MODULE_PAYMENT_MONEYBOOKERS_PAY_TO', '" . (isset($HTTP_GET_VARS['email']) ? $HTTP_GET_VARS['email'] : '') . "', 'The Moneybookers seller e-mail address to accept payments for', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant ID', 'MODULE_PAYMENT_MONEYBOOKERS_MERCHANT_ID', '" . (isset($HTTP_GET_VARS['custid']) ? $HTTP_GET_VARS['custid'] : '') . "', 'The Moneybookers merchant ID assigned to the seller e-mail address', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Secret Word', 'MODULE_PAYMENT_MONEYBOOKERS_SECRET_WORD', '', 'The secret word to verify transactions with', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Store Logo Image', 'MODULE_PAYMENT_MONEYBOOKERS_STORE_IMAGE', '" . tep_catalog_href_link('images/store_logo.png', '', 'SSL') . "', 'The URL of the store logo image to display on the gateway transaction page. This must be served through HTTPS otherwise it will not be shown.', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('iFrame Presentation', 'MODULE_PAYMENT_MONEYBOOKERS_IFRAME', 'True', 'Show the Moneybookers payment pages through an iFrame?', '6', '3', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Debug E-Mail Address', 'MODULE_PAYMENT_MONEYBOOKERS_DEBUG_EMAIL', '', 'All parameters of an invalid transaction will be sent to this email address.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_MONEYBOOKERS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_MONEYBOOKERS_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Preparing Order Status', 'MODULE_PAYMENT_MONEYBOOKERS_PREPARE_ORDER_STATUS_ID', '" . $preparing_status_id . "', 'Set the status of prepared orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Transactions Order Status', 'MODULE_PAYMENT_MONEYBOOKERS_TRANSACTIONS_ORDER_STATUS_ID', '" . $transactions_status_id . "', 'Set the status of callback transactions to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_MONEYBOOKERS_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('cURL Program Location', 'MODULE_PAYMENT_MONEYBOOKERS_CURL', '/usr/bin/curl', 'The location to the cURL program application.', '6', '0' , now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_MONEYBOOKERS_STATUS', 'MODULE_PAYMENT_MONEYBOOKERS_PAY_TO', 'MODULE_PAYMENT_MONEYBOOKERS_MERCHANT_ID', 'MODULE_PAYMENT_MONEYBOOKERS_SECRET_WORD', 'MODULE_PAYMENT_MONEYBOOKERS_STORE_IMAGE', 'MODULE_PAYMENT_MONEYBOOKERS_IFRAME', 'MODULE_PAYMENT_MONEYBOOKERS_DEBUG_EMAIL', 'MODULE_PAYMENT_MONEYBOOKERS_ZONE', 'MODULE_PAYMENT_MONEYBOOKERS_PREPARE_ORDER_STATUS_ID', 'MODULE_PAYMENT_MONEYBOOKERS_TRANSACTIONS_ORDER_STATUS_ID', 'MODULE_PAYMENT_MONEYBOOKERS_ORDER_STATUS_ID', 'MODULE_PAYMENT_MONEYBOOKERS_SORT_ORDER', 'MODULE_PAYMENT_MONEYBOOKERS_CURL');
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

    function sendTransactionToGateway($url, $parameters, $with_headers = false) {
      $server = parse_url($url);

      if (!isset($server['port'])) {
        $server['port'] = ($server['scheme'] == 'https') ? 443 : 80;
      }

      if (!isset($server['path'])) {
        $server['path'] = '/';
      }

      if (isset($server['user']) && isset($server['pass'])) {
        $header[] = 'Authorization: Basic ' . base64_encode($server['user'] . ':' . $server['pass']);
      }

      if (function_exists('curl_init')) {
        $curl = curl_init($server['scheme'] . '://' . $server['host'] . $server['path'] . (isset($server['query']) ? '?' . $server['query'] : ''));
        curl_setopt($curl, CURLOPT_PORT, $server['port']);
        curl_setopt($curl, CURLOPT_HEADER, ($with_headers == true) ? 1 : 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);

        $result = curl_exec($curl);

        curl_close($curl);
      } else {
        exec(escapeshellarg(MODULE_PAYMENT_MONEYBOOKERS_CURL) . ' -d ' . escapeshellarg($parameters) . ' "' . $server['scheme'] . '://' . $server['host'] . $server['path'] . (isset($server['query']) ? '?' . $server['query'] : '') . '" -P ' . $server['port'] . ' -k', $result);
        $result = implode("\n", $result);
      }

      return $result;
    }
  }
?>
