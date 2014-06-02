<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class authorizenet_cc_aim {
    var $code, $title, $description, $enabled;

    function authorizenet_cc_aim() {
      global $HTTP_GET_VARS, $PHP_SELF, $order;

      $this->signature = 'authorizenet|authorizenet_cc_aim|2.0|2.3';
      $this->api_version = '3.1';

      $this->code = 'authorizenet_cc_aim';
      $this->title = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_DESCRIPTION;
      $this->sort_order = defined('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_SORT_ORDER') ? MODULE_PAYMENT_AUTHORIZENET_CC_AIM_SORT_ORDER : 0;
      $this->enabled = defined('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_STATUS') && (MODULE_PAYMENT_AUTHORIZENET_CC_AIM_STATUS == 'True') ? true : false;
      $this->order_status = defined('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ORDER_STATUS_ID : 0;

      if ( defined('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_STATUS') ) {
        if ( (MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_SERVER == 'Test') || (MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_MODE == 'Test') ) {
          $this->title .= ' [Test]';
          $this->public_title .= ' (' . $this->code . '; Test)';
        }

        $this->description .= $this->getTestLinkInfo();
      }

      if ( !function_exists('curl_init') ) {
        $this->description = '<div class="secWarning">' . MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ERROR_ADMIN_CURL . '</div>' . $this->description;

        $this->enabled = false;
      }

      if ( $this->enabled === true ) {
        if ( !tep_not_null(MODULE_PAYMENT_AUTHORIZENET_CC_AIM_LOGIN_ID) || !tep_not_null(MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_KEY) ) {
          $this->description = '<div class="secWarning">' . MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ERROR_ADMIN_CONFIGURATION . '</div>' . $this->description;

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

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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
      return array('id' => $this->code,
                   'module' => $this->public_title);
    }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {
      global $order;

      for ($i=1; $i<13; $i++) {
        $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => sprintf('%02d', $i));
      }

      $today = getdate(); 
      for ($i=$today['year']; $i < $today['year']+10; $i++) {
        $expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
      }

      $confirmation = array('fields' => array(array('title' => MODULE_PAYMENT_AUTHORIZENET_CC_AIM_CREDIT_CARD_OWNER_FIRSTNAME,
                                                    'field' => tep_draw_input_field('cc_owner_firstname', $order->billing['firstname'])),
                                              array('title' => MODULE_PAYMENT_AUTHORIZENET_CC_AIM_CREDIT_CARD_OWNER_LASTNAME,
                                                    'field' => tep_draw_input_field('cc_owner_lastname', $order->billing['lastname'])),
                                              array('title' => MODULE_PAYMENT_AUTHORIZENET_CC_AIM_CREDIT_CARD_NUMBER,
                                                    'field' => tep_draw_input_field('cc_number_nh-dns')),
                                              array('title' => MODULE_PAYMENT_AUTHORIZENET_CC_AIM_CREDIT_CARD_EXPIRES,
                                                    'field' => tep_draw_pull_down_menu('cc_expires_month', $expires_month) . '&nbsp;' . tep_draw_pull_down_menu('cc_expires_year', $expires_year)),
                                              array('title' => MODULE_PAYMENT_AUTHORIZENET_CC_AIM_CREDIT_CARD_CCV,
                                                    'field' => tep_draw_input_field('cc_ccv_nh-dns', '', 'size="5" maxlength="4"'))));

      return $confirmation;
    }

    function process_button() {
      return false;
    }

    function before_process() {
      global $HTTP_POST_VARS, $customer_id, $order, $sendto, $currency, $response;

      $params = array('x_login' => substr(MODULE_PAYMENT_AUTHORIZENET_CC_AIM_LOGIN_ID, 0, 20),
                      'x_tran_key' => substr(MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_KEY, 0, 16),
                      'x_version' => $this->api_version,
                      'x_type' => ((MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_METHOD == 'Capture') ? 'AUTH_CAPTURE' : 'AUTH_ONLY'),
                      'x_method' => 'CC',
                      'x_amount' => substr($this->format_raw($order->info['total']), 0, 15),
                      'x_currency_code' => substr($currency, 0, 3),
                      'x_card_num' => substr(preg_replace('/[^0-9]/', '', $HTTP_POST_VARS['cc_number_nh-dns']), 0, 22),
                      'x_exp_date' => $HTTP_POST_VARS['cc_expires_month'] . $HTTP_POST_VARS['cc_expires_year'],
                      'x_card_code' => substr($HTTP_POST_VARS['cc_ccv_nh-dns'], 0, 4),
                      'x_description' => substr(STORE_NAME, 0, 255),
                      'x_first_name' => substr($order->billing['firstname'], 0, 50),
                      'x_last_name' => substr($order->billing['lastname'], 0, 50),
                      'x_company' => substr($order->billing['company'], 0, 50),
                      'x_address' => substr($order->billing['street_address'], 0, 60),
                      'x_city' => substr($order->billing['city'], 0, 40),
                      'x_state' => substr($order->billing['state'], 0, 40),
                      'x_zip' => substr($order->billing['postcode'], 0, 20),
                      'x_country' => substr($order->billing['country']['title'], 0, 60),
                      'x_phone' => substr($order->customer['telephone'], 0, 25),
                      'x_email' => substr($order->customer['email_address'], 0, 255),
                      'x_cust_id' => substr($customer_id, 0, 20),
                      'x_customer_ip' => tep_get_ip_address(),
                      'x_relay_response' => 'FALSE',
                      'x_delim_data' => 'TRUE',
                      'x_delim_char' => ',',
                      'x_encap_char' => '|');

      if (is_numeric($sendto) && ($sendto > 0)) {
        $params['x_ship_to_first_name'] = substr($order->delivery['firstname'], 0, 50);
        $params['x_ship_to_last_name'] = substr($order->delivery['lastname'], 0, 50);
        $params['x_ship_to_company'] = substr($order->delivery['company'], 0, 50);
        $params['x_ship_to_address'] = substr($order->delivery['street_address'], 0, 60);
        $params['x_ship_to_city'] = substr($order->delivery['city'], 0, 40);
        $params['x_ship_to_state'] = substr($order->delivery['state'], 0, 40);
        $params['x_ship_to_zip'] = substr($order->delivery['postcode'], 0, 20);
        $params['x_ship_to_country'] = substr($order->delivery['country']['title'], 0, 60);
      }

      if (MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_MODE == 'Test') {
        $params['x_test_request'] = 'TRUE';
      }

      $tax_value = 0;

      foreach ($order->info['tax_groups'] as $key => $value) {
        if ($value > 0) {
          $tax_value += $this->format_raw($value);
        }
      }

      if ($tax_value > 0) {
        $params['x_tax'] = $this->format_raw($tax_value);
      }

      $params['x_freight'] = $this->format_raw($order->info['shipping_cost']);

      $post_string = '';

      foreach ($params as $key => $value) {
        $post_string .= $key . '=' . urlencode(trim($value)) . '&';
      }

      $post_string = substr($post_string, 0, -1);

      for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
        $post_string .= '&x_line_item=' . urlencode($i+1) . '<|>' . urlencode(substr($order->products[$i]['name'], 0, 31)) . '<|>' . urlencode(substr($order->products[$i]['name'], 0, 255)) . '<|>' . urlencode($order->products[$i]['qty']) . '<|>' . urlencode($this->format_raw($order->products[$i]['final_price'])) . '<|>' . urlencode($order->products[$i]['tax'] > 0 ? 'YES' : 'NO');
      }

      if ( MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_SERVER == 'Live' ) {
        $gateway_url = 'https://secure.authorize.net/gateway/transact.dll';
      } else {
        $gateway_url = 'https://test.authorize.net/gateway/transact.dll';
      }

      $transaction_response = $this->sendTransactionToGateway($gateway_url, $post_string);

      $response = array('x_response_code' => '-1',
                        'x_response_subcode' => '-1',
                        'x_response_reason_code' => '-1');

      if ( !empty($transaction_response) ) {
        $raw = explode('|,|', substr($transaction_response, 1, -1));

        if ( count($raw) > 54 ) {
          $response = array('x_response_code' => $raw[0],
                            'x_response_subcode' => $raw[1],
                            'x_response_reason_code' => $raw[2],
                            'x_response_reason_text' => $raw[3],
                            'x_auth_code' => $raw[4],
                            'x_avs_code' => $raw[5],
                            'x_trans_id' => $raw[6],
                            'x_invoice_num' => $raw[7],
                            'x_description' => $raw[8],
                            'x_amount' => $raw[9],
                            'x_method' => $raw[10],
                            'x_type' => $raw[11],
                            'x_cust_id' => $raw[12],
                            'x_first_name' => $raw[13],
                            'x_last_name' => $raw[14],
                            'x_company' => $raw[15],
                            'x_address' => $raw[16],
                            'x_city' => $raw[17],
                            'x_state' => $raw[18],
                            'x_zip' => $raw[19],
                            'x_country' => $raw[20],
                            'x_phone' => $raw[21],
                            'x_fax' => $raw[22],
                            'x_email' => $raw[23],
                            'x_ship_to_first_name' => $raw[24],
                            'x_ship_to_last_name' => $raw[25],
                            'x_ship_to_company' => $raw[26],
                            'x_ship_to_address' => $raw[27],
                            'x_ship_to_city' => $raw[28],
                            'x_ship_to_state' => $raw[29],
                            'x_ship_to_zip' => $raw[30],
                            'x_ship_to_country' => $raw[31],
                            'x_tax' => $raw[32],
                            'x_duty' => $raw[33],
                            'x_freight' => $raw[34],
                            'x_tax_exempt' => $raw[35],
                            'x_po_num' => $raw[36],
                            'x_MD5_Hash' => $raw[37],
                            'x_cvv2_resp_code' => $raw[38],
                            'x_cavv_response' => $raw[39],
                            'x_account_number' => $raw[50],
                            'x_card_type' => $raw[51],
                            'x_split_tender_id' => $raw[52],
                            'x_prepaid_requested_amount' => $raw[53],
                            'x_prepaid_balance_on_card' => $raw[54]);

          unset($raw);
        }
      }

      $error = false;

      if ( ($response['x_response_code'] == '1') || ($response['x_response_code'] == '4') ) {
        if ( (tep_not_null(MODULE_PAYMENT_AUTHORIZENET_CC_AIM_MD5_HASH) && (strtoupper($response['x_MD5_Hash']) != strtoupper(md5(MODULE_PAYMENT_AUTHORIZENET_CC_AIM_MD5_HASH . MODULE_PAYMENT_AUTHORIZENET_CC_AIM_LOGIN_ID . $response['x_trans_id'] . $this->format_raw($order->info['total']))))) || ($response['x_amount'] != $this->format_raw($order->info['total'])) ) {
          if ( MODULE_PAYMENT_AUTHORIZENET_CC_AIM_REVIEW_ORDER_STATUS_ID > 0 ) {
            $order->info['order_status'] = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_REVIEW_ORDER_STATUS_ID;
          }
        }

        if ( $response['x_response_code'] == '4' ) {
          if ( MODULE_PAYMENT_AUTHORIZENET_CC_AIM_REVIEW_ORDER_STATUS_ID > 0 ) {
            $order->info['order_status'] = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_REVIEW_ORDER_STATUS_ID;
          }
        }
      } elseif ($response['x_response_code'] == '2') {
        $error = 'declined';
      } else {
        $error = 'general';
      }

      if ( $error !== false ) {
        switch ($response['x_response_reason_code']) {
          case '7':
            $error = 'invalid_expiration_date';
            break;

          case '8':
            $error = 'expired';
            break;

          case '13':
            $error = 'merchant_account';
            break;

          case '6':
          case '17':
          case '28':
            $error = 'declined';
            break;

          case '39':
            $error = 'currency';
            break;

          case '78':
            $error = 'ccv';
            break;
        }
      }

      if ($error !== false) {
        $this->sendDebugEmail($response);

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=' . $error, 'SSL'));
      }
    }

    function after_process() {
      global $response, $order, $insert_id;

      $status = array();

      if ( tep_not_null(MODULE_PAYMENT_AUTHORIZENET_CC_AIM_MD5_HASH) ) {
        if ( strtoupper($response['x_MD5_Hash']) == strtoupper(md5(MODULE_PAYMENT_AUTHORIZENET_CC_AIM_MD5_HASH . MODULE_PAYMENT_AUTHORIZENET_CC_AIM_LOGIN_ID . $response['x_trans_id'] . $this->format_raw($order->info['total']))) ) {
          $status[] = 'MD5 Hash: Match';
        } else {
          $status[] = '*** MD5 Hash Does Not Match ***';
        }
      }

      if ( $response['x_amount'] != $this->format_raw($order->info['total']) ) {
        $status[] = '*** Order Total Does Not Match Transaction Total ***';
      }

      $status[] = 'Response: ' . tep_db_prepare_input($response['x_response_reason_text']) . ' (' . tep_db_prepare_input($response['x_response_reason_code']) . ')';
      $status[] = 'Transaction ID: ' . tep_db_prepare_input($response['x_trans_id']);

      $avs_response = '?';

      if ( !empty($response['x_avs_code']) ) {
        if ( defined('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_AVS_' . $response['x_avs_code']) ) {
          $avs_response = constant('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_AVS_' . $response['x_avs_code']) . ' (' . $response['x_avs_code'] . ')';
        } else {
          $avs_response = $response['x_avs_code'];
        }
      }

      $status[] = 'AVS: ' . tep_db_prepare_input($avs_response);

      $cvv2_response = '?';

      if ( !empty($response['x_cvv2_resp_code']) ) {
        if ( defined('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CVV2_' . $response['x_cvv2_resp_code']) ) {
          $cvv2_response = constant('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CVV2_' . $response['x_cvv2_resp_code']) . ' (' . $response['x_cvv2_resp_code'] . ')';
        } else {
          $cvv2_response = $response['x_cvv2_resp_code'];
        }
      }

      $status[] = 'Card Code: ' . tep_db_prepare_input($cvv2_response);

      $cavv_response = '?';

      if ( !empty($response['x_cavv_response']) ) {
        if ( defined('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CAVV_' . $response['x_cavv_response']) ) {
          $cavv_response = constant('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CAVV_' . $response['x_cavv_response']) . ' (' . $response['x_cavv_response'] . ')';
        } else {
          $cavv_response = $response['x_cavv_response'];
        }
      }

      $status[] = 'Card Holder: ' . tep_db_prepare_input($cavv_response);

      $sql_data_array = array('orders_id' => $insert_id,
                              'orders_status_id' => MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_ORDER_STATUS_ID,
                              'date_added' => 'now()',
                              'customer_notified' => '0',
                              'comments' => implode("\n", $status));

      tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
    }

    function get_error() {
      global $HTTP_GET_VARS;

      $error_message = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ERROR_GENERAL;

      switch ($HTTP_GET_VARS['error']) {
        case 'invalid_expiration_date':
          $error_message = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ERROR_INVALID_EXP_DATE;
          break;

        case 'expired':
          $error_message = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ERROR_EXPIRED;
          break;

        case 'declined':
          $error_message = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ERROR_DECLINED;
          break;

        case 'ccv':
          $error_message = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ERROR_CCV;
          break;

        case 'merchant_account':
          $error_message = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ERROR_MERCHANT_ACCOUNT;
          break;

        case 'currency':
          $error_message = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ERROR_CURRENCY;
          break;

        default:
          $error_message = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ERROR_GENERAL;
          break;
      }

      $error = array('title' => MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ERROR_TITLE,
                     'error' => $error_message);

      return $error;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_STATUS'");
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
      if (!defined('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_ORDER_STATUS_ID')) {
        $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Authorize.net [Transactions]' limit 1");

        if (tep_db_num_rows($check_query) < 1) {
          $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
          $status = tep_db_fetch_array($status_query);

          $status_id = $status['status_id']+1;

          $languages = tep_get_languages();

          foreach ($languages as $lang) {
            tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $lang['id'] . "', 'Authorize.net [Transactions]')");
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
        $status_id = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_ORDER_STATUS_ID;
      }

      $params = array('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_STATUS' => array('title' => 'Enable Authorize.net Advanced Integration Method',
                                                                           'desc' => 'Do you want to accept Authorize.net Advanced Integration Method payments?',
                                                                           'value' => 'True',
                                                                           'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_LOGIN_ID' => array('title' => 'API Login ID',
                                                                             'desc' => 'The API Login ID used for the Authorize.net service'),
                      'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_KEY' => array('title' => 'API Transaction Key',
                                                                                    'desc' => 'The API Transaction Key used for the Authorize.net service'),
                      'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_MD5_HASH' => array('title' => 'MD5 Hash',
                                                                             'desc' => 'The MD5 Hash value to verify transactions with'),
                      'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_METHOD' => array('title' => 'Transaction Method',
                                                                                       'desc' => 'The processing method to use for each transaction.',
                                                                                       'value' => 'Authorization',
                                                                                       'set_func' => 'tep_cfg_select_option(array(\'Authorization\', \'Capture\'), '),
                      'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ORDER_STATUS_ID' => array('title' => 'Set Order Status',
                                                                                    'desc' => 'Set the status of orders made with this payment module to this value',
                                                                                    'value' => '0',
                                                                                    'use_func' => 'tep_get_order_status_name',
                                                                                    'set_func' => 'tep_cfg_pull_down_order_statuses('),
                      'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_REVIEW_ORDER_STATUS_ID' => array('title' => 'Review Order Status',
                                                                                           'desc' => 'Set the status of orders flagged as being under review to this value',
                                                                                           'value' => '0',
                                                                                           'use_func' => 'tep_get_order_status_name',
                                                                                           'set_func' => 'tep_cfg_pull_down_order_statuses('),
                      'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_ORDER_STATUS_ID' => array('title' => 'Transaction Order Status',
                                                                                                'desc' => 'Include transaction information in this order status level',
                                                                                                'value' => $status_id,
                                                                                                'use_func' => 'tep_get_order_status_name',
                                                                                                'set_func' => 'tep_cfg_pull_down_order_statuses('),
                      'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ZONE' => array('title' => 'Payment Zone',
                                                                         'desc' => 'If a zone is selected, only enable this payment method for that zone.',
                                                                         'value' => '0',
                                                                         'set_func' => 'tep_cfg_pull_down_zone_classes(',
                                                                         'use_func' => 'tep_get_zone_class_title'),
                      'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_SERVER' => array('title' => 'Transaction Server',
                                                                                       'desc' => 'Perform transactions on the live or test server. The test server should only be used by developers with Authorize.net test accounts.',
                                                                                       'value' => 'Live',
                                                                                       'set_func' => 'tep_cfg_select_option(array(\'Live\', \'Test\'), '),
                      'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_MODE' => array('title' => 'Transaction Mode',
                                                                                     'desc' => 'Transaction mode used for processing orders',
                                                                                     'value' => 'Live',
                                                                                     'set_func' => 'tep_cfg_select_option(array(\'Live\', \'Test\'), '),
                      'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_VERIFY_SSL' => array('title' => 'Verify SSL Certificate',
                                                                               'desc' => 'Verify transaction server SSL certificate on connection?',
                                                                               'value' => 'True',
                                                                               'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_PROXY' => array('title' => 'Proxy Server',
                                                                          'desc' => 'Send API requests through this proxy server. (host:port, eg: 123.45.67.89:8080 or proxy.example.com:8080)'),
                      'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_DEBUG_EMAIL' => array('title' => 'Debug E-Mail Address',
                                                                                'desc' => 'All parameters of an invalid transaction will be sent to this email address.'),
                      'MODULE_PAYMENT_AUTHORIZENET_CC_AIM_SORT_ORDER' => array('title' => 'Sort order of display.',
                                                                               'desc' => 'Sort order of display. Lowest is displayed first.',
                                                                               'value' => '0'));

      return $params;
    }

    function _hmac($key, $data) {
      if (function_exists('hash_hmac')) {
        return hash_hmac('md5', $data, $key);
      } elseif (function_exists('mhash') && defined('MHASH_MD5')) {
        return bin2hex(mhash(MHASH_MD5, $data, $key));
      }

// RFC 2104 HMAC implementation for php.
// Creates an md5 HMAC.
// Eliminates the need to install mhash to compute a HMAC
// Hacked by Lance Rushing

      $b = 64; // byte length for md5
      if (strlen($key) > $b) {
        $key = pack("H*",md5($key));
      }

      $key = str_pad($key, $b, chr(0x00));
      $ipad = str_pad('', $b, chr(0x36));
      $opad = str_pad('', $b, chr(0x5c));
      $k_ipad = $key ^ $ipad ;
      $k_opad = $key ^ $opad;

      return md5($k_opad . pack("H*",md5($k_ipad . $data)));
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

      if ( MODULE_PAYMENT_AUTHORIZENET_CC_AIM_VERIFY_SSL == 'True' ) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

        if ( file_exists(DIR_FS_CATALOG . 'ext/modules/payment/authorizenet/authorize.net.crt') ) {
          curl_setopt($curl, CURLOPT_CAINFO, DIR_FS_CATALOG . 'ext/modules/payment/authorizenet/authorize.net.crt');
        } elseif ( file_exists(DIR_FS_CATALOG . 'includes/cacert.pem') ) {
          curl_setopt($curl, CURLOPT_CAINFO, DIR_FS_CATALOG . 'includes/cacert.pem');
        }
      } else {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      }

      if ( tep_not_null(MODULE_PAYMENT_AUTHORIZENET_CC_AIM_PROXY) ) {
        curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($curl, CURLOPT_PROXY, MODULE_PAYMENT_AUTHORIZENET_CC_AIM_PROXY);
      }

      $result = curl_exec($curl);

      curl_close($curl);

      return $result;
    }

    function getTestLinkInfo() {
      $dialog_title = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_DIALOG_CONNECTION_TITLE;
      $dialog_button_close = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_DIALOG_CONNECTION_BUTTON_CLOSE;
      $dialog_success = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_DIALOG_CONNECTION_SUCCESS;
      $dialog_failed = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_DIALOG_CONNECTION_FAILED;
      $dialog_error = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_DIALOG_CONNECTION_ERROR;
      $dialog_connection_time = MODULE_PAYMENT_AUTHORIZENET_CC_AIM_DIALOG_CONNECTION_TIME;

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

      $info = '<p><img src="images/icons/locked.gif" border="0">&nbsp;<a href="javascript:openTestConnectionDialog();" style="text-decoration: underline; font-weight: bold;">' . MODULE_PAYMENT_AUTHORIZENET_CC_AIM_DIALOG_CONNECTION_LINK_TITLE . '</a></p>' .
              '<div id="testConnectionDialog" style="display: none;"><p>';

      if ( MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_SERVER == 'Live' ) {
        $info .= 'Live Server:<br />https://secure.authorize.net/gateway/transact.dll';
      } else {
        $info .= 'Test Server:<br />https://test.authorize.net/gateway/transact.dll';
      }

      $info .= '</p><div id="testConnectionDialogProgress"><p>' . MODULE_PAYMENT_AUTHORIZENET_CC_AIM_DIALOG_CONNECTION_GENERAL_TEXT . '</p><div id="tcdprogressbar"></div></div></div>' .
               $js;

      return $info;
    }

    function getTestConnectionResult() {
      if ( MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_SERVER == 'Live' ) {
        $api_url = 'https://secure.authorize.net/gateway/transact.dll';
      } else {
        $api_url = 'https://test.authorize.net/gateway/transact.dll';
      }

      $params = array('x_login' => substr(MODULE_PAYMENT_AUTHORIZENET_CC_AIM_LOGIN_ID, 0, 20),
                      'x_tran_key' => substr(MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_KEY, 0, 16),
                      'x_version' => $this->api_version,
                      'x_customer_ip' => tep_get_ip_address(),
                      'x_relay_response' => 'FALSE',
                      'x_delim_data' => 'TRUE',
                      'x_delim_char' => ',',
                      'x_encap_char' => '|');

      $post_string = '';

      foreach ($params as $key => $value) {
        $post_string .= $key . '=' . urlencode(trim($value)) . '&';
      }

      $post_string = substr($post_string, 0, -1);

      $result = $this->sendTransactionToGateway($api_url, $post_string);

      $response = array('x_response_code' => '-1');

      if ( !empty($result) ) {
        $raw = explode('|,|', substr($result, 1, -1));

        if ( count($raw) > 54 ) {
          $response['x_response_code'] = $raw[0];
        }
      }

      if ( $response['x_response_code'] != '-1' ) {
        return 1;
      }

      return -1;
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

      if (tep_not_null(MODULE_PAYMENT_AUTHORIZENET_CC_AIM_DEBUG_EMAIL)) {
        $email_body = '';

        if (!empty($response)) {
          $email_body .= 'RESPONSE:' . "\n\n" . print_r($response, true) . "\n\n";
        }

        if (!empty($HTTP_POST_VARS)) {
          if (isset($HTTP_POST_VARS['cc_number_nh-dns'])) {
            $HTTP_POST_VARS['cc_number_nh-dns'] = 'XXXX' . substr($HTTP_POST_VARS['cc_number_nh-dns'], -4);
          }

          if (isset($HTTP_POST_VARS['cc_ccv_nh-dns'])) {
            $HTTP_POST_VARS['cc_ccv_nh-dns'] = 'XXX';
          }

          if (isset($HTTP_POST_VARS['cc_expires_month'])) {
            $HTTP_POST_VARS['cc_expires_month'] = 'XX';
          }

          if (isset($HTTP_POST_VARS['cc_expires_year'])) {
            $HTTP_POST_VARS['cc_expires_year'] = 'XX';
          }

          $email_body .= '$HTTP_POST_VARS:' . "\n\n" . print_r($HTTP_POST_VARS, true) . "\n\n";
        }

        if (!empty($HTTP_GET_VARS)) {
          $email_body .= '$HTTP_GET_VARS:' . "\n\n" . print_r($HTTP_GET_VARS, true) . "\n\n";
        }

        if (!empty($email_body)) {
          tep_mail('', MODULE_PAYMENT_AUTHORIZENET_CC_AIM_DEBUG_EMAIL, 'Authorize.net AIM Debug E-Mail', trim($email_body), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
        }
      }
    }
  }
?>
