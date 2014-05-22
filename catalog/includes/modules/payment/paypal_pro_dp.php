<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class paypal_pro_dp {
    var $code, $title, $description, $enabled;

    function paypal_pro_dp() {
      global $HTTP_GET_VARS, $PHP_SELF, $order;

      $this->signature = 'paypal|paypal_pro_dp|3.0|2.3';
      $this->api_version = '112';

      $this->code = 'paypal_pro_dp';
      $this->title = MODULE_PAYMENT_PAYPAL_PRO_DP_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_PAYPAL_PRO_DP_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_PAYPAL_PRO_DP_TEXT_DESCRIPTION;
      $this->sort_order = defined('MODULE_PAYMENT_PAYPAL_PRO_DP_SORT_ORDER') ? MODULE_PAYMENT_PAYPAL_PRO_DP_SORT_ORDER : 0;
      $this->enabled = defined('MODULE_PAYMENT_PAYPAL_PRO_DP_STATUS') && (MODULE_PAYMENT_PAYPAL_PRO_DP_STATUS == 'True') ? true : false;
      $this->order_status = defined('MODULE_PAYMENT_PAYPAL_PRO_DP_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_PAYPAL_PRO_DP_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_PAYPAL_PRO_DP_ORDER_STATUS_ID : 0;

      if ( !defined('MODULE_PAYMENT_INSTALLED') || !tep_not_null(MODULE_PAYMENT_INSTALLED) || !in_array('paypal_express.php', explode(';', MODULE_PAYMENT_INSTALLED)) || !defined('MODULE_PAYMENT_PAYPAL_EXPRESS_STATUS') || (MODULE_PAYMENT_PAYPAL_EXPRESS_STATUS != 'True') ) {
        $this->description = '<div class="secWarning">' . MODULE_PAYMENT_PAYPAL_PRO_DP_ERROR_EXPRESS_MODULE . '</div>' . $this->description;

        $this->enabled = false;
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_STATUS') ) {
        if ( MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER == 'Sandbox' ) {
          $this->title .= ' [Sandbox]';
          $this->public_title .= ' (' . $this->code . '; Sandbox)';
        }

        $this->description .= $this->getTestLinkInfo();
      }

      if ( !function_exists('curl_init') ) {
        $this->description = '<div class="secWarning">' . MODULE_PAYMENT_PAYPAL_PRO_DP_ERROR_ADMIN_CURL . '</div>' . $this->description;

        $this->enabled = false;
      }

      if ( $this->enabled === true ) {
        if ( !tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_DP_API_USERNAME) || !tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_DP_API_PASSWORD) || !tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_DP_API_SIGNATURE) ) {
          $this->description = '<div class="secWarning">' . MODULE_PAYMENT_PAYPAL_PRO_DP_ERROR_ADMIN_CONFIGURATION . '</div>' . $this->description;

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

      $this->cc_types = array('VISA' => 'Visa',
                              'MASTERCARD' => 'MasterCard',
                              'DISCOVER' => 'Discover Card',
                              'AMEX' => 'American Express',
                              'MAESTRO' => 'Maestro');
    }

    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PAYPAL_PRO_DP_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PAYPAL_PRO_DP_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->delivery['zone_id']) {
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
      if ( $this->templateClassExists() ) {
        $GLOBALS['oscTemplate']->addBlock($this->getSubmitCardDetailsJavascript(), 'header_tags');
      }
    }

    function confirmation() {
      global $order;

      $types_array = array();
      foreach ( $this->cc_types as $key => $value ) {
        if ($this->isCardAccepted($key)) {
          $types_array[] = array('id' => $key,
                                 'text' => $value);
        }
      }

      $today = getdate();

      $months_array = array();
      for ($i=1; $i<13; $i++) {
        $months_array[] = array('id' => sprintf('%02d', $i), 'text' => sprintf('%02d', $i));
      }

      $year_valid_from_array = array();
      for ($i=$today['year']-10; $i < $today['year']+1; $i++) {
        $year_valid_from_array[] = array('id' => strftime('%Y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
      }

      $year_expires_array = array();
      for ($i=$today['year']; $i < $today['year']+10; $i++) {
        $year_expires_array[] = array('id' => strftime('%Y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
      }

      $content = '<table id="paypal_table_new_card" border="0" width="100%" cellspacing="0" cellpadding="2">' .
                 '<tr>' .
                 '  <td width="30%">' . MODULE_PAYMENT_PAYPAL_PRO_DP_CARD_TYPE . '</td>' .
                 '  <td>' . tep_draw_pull_down_menu('cc_type', $types_array, '', 'id="paypal_card_type"') . '</td>' .
                 '</tr>' .
                 '<tr>' .
                 '  <td width="30%">' . MODULE_PAYMENT_PAYPAL_PRO_DP_CARD_OWNER . '</td>' .
                 '  <td>' . tep_draw_input_field('cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname']) . '</td>' .
                 '</tr>' .
                 '<tr>' .
                 '  <td width="30%">' . MODULE_PAYMENT_PAYPAL_PRO_DP_CARD_NUMBER . '</td>' .
                 '  <td>' . tep_draw_input_field('cc_number_nh-dns', '', 'id="paypal_card_num"') . '</td>' .
                 '</tr>';

      if ( (MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_MAESTRO == 'True') || (MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_AMEX == 'True') ) {
        $content .= '<tr>' .
                    '  <td width="30%">' . MODULE_PAYMENT_PAYPAL_PRO_DP_CARD_VALID_FROM . '</td>' .
                    '  <td>' . tep_draw_pull_down_menu('cc_starts_month', $months_array, '', 'id="paypal_card_date_start"') . '&nbsp;' . tep_draw_pull_down_menu('cc_starts_year', $year_valid_from_array) . '&nbsp;' . MODULE_PAYMENT_PAYPAL_PRO_DP_CARD_VALID_FROM_INFO . '</td>' .
                    '</tr>';
      }

      $content .= '<tr>' .
                  '  <td width="30%">' . MODULE_PAYMENT_PAYPAL_PRO_DP_CARD_EXPIRES . '</td>' .
                  '  <td>' . tep_draw_pull_down_menu('cc_expires_month', $months_array) . '&nbsp;' . tep_draw_pull_down_menu('cc_expires_year', $year_expires_array) . '</td>' .
                  '</tr>';

      if ( MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_MAESTRO == 'True' ) {
        $content .= '<tr>' .
                    '  <td width="30%">' . MODULE_PAYMENT_PAYPAL_PRO_DP_CARD_ISSUE_NUMBER . '</td>' .
                    '  <td>' . tep_draw_input_field('cc_issue_nh-dns', '', 'id="paypal_card_issue" size="3" maxlength="2"') . '&nbsp;' . MODULE_PAYMENT_PAYPAL_PRO_DP_CARD_ISSUE_NUMBER_INFO . '</td>' .
                    '</tr>';
      }

      $content .= '<tr>' .
                  '  <td width="30%">' . MODULE_PAYMENT_PAYPAL_PRO_DP_CARD_CVC . '</td>' .
                  '  <td>' . tep_draw_input_field('cc_cvc_nh-dns', '', 'size="5" maxlength="4"') . '</td>' .
                  '</tr>' .
                  '</table>';

      $content .= !$this->templateClassExists() ? $this->getSubmitCardDetailsJavascript() : '';

      $confirmation = array('title' => $content);

      return $confirmation;
    }

    function process_button() {
      return false;
    }

    function before_process() {
      global $HTTP_POST_VARS, $order, $order_totals, $sendto, $response_array;

      if (isset($HTTP_POST_VARS['cc_owner']) && !empty($HTTP_POST_VARS['cc_owner']) && isset($HTTP_POST_VARS['cc_type']) && $this->isCardAccepted($HTTP_POST_VARS['cc_type']) && isset($HTTP_POST_VARS['cc_number_nh-dns']) && !empty($HTTP_POST_VARS['cc_number_nh-dns'])) {
        if (MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER == 'Live') {
          $api_url = 'https://api-3t.paypal.com/nvp';
        } else {
          $api_url = 'https://api-3t.sandbox.paypal.com/nvp';
        }

        $params = array('USER' => MODULE_PAYMENT_PAYPAL_PRO_DP_API_USERNAME,
                        'PWD' => MODULE_PAYMENT_PAYPAL_PRO_DP_API_PASSWORD,
                        'VERSION' => $this->api_version,
                        'SIGNATURE' => MODULE_PAYMENT_PAYPAL_PRO_DP_API_SIGNATURE,
                        'METHOD' => 'DoDirectPayment',
                        'PAYMENTACTION' => ((MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_METHOD == 'Sale') ? 'Sale' : 'Authorization'),
                        'IPADDRESS' => tep_get_ip_address(),
                        'AMT' => $this->format_raw($order->info['total']),
                        'CREDITCARDTYPE' => $HTTP_POST_VARS['cc_type'],
                        'ACCT' => $HTTP_POST_VARS['cc_number_nh-dns'],
                        'EXPDATE' => $HTTP_POST_VARS['cc_expires_month'] . $HTTP_POST_VARS['cc_expires_year'],
                        'CVV2' => $HTTP_POST_VARS['cc_cvc_nh-dns'],
                        'FIRSTNAME' => substr($HTTP_POST_VARS['cc_owner'], 0, strpos($HTTP_POST_VARS['cc_owner'], ' ')),
                        'LASTNAME' => substr($HTTP_POST_VARS['cc_owner'], strpos($HTTP_POST_VARS['cc_owner'], ' ')+1),
                        'STREET' => $order->billing['street_address'],
                        'CITY' => $order->billing['city'],
                        'STATE' => tep_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], $order->billing['state']),
                        'COUNTRYCODE' => $order->billing['country']['iso_code_2'],
                        'ZIP' => $order->billing['postcode'],
                        'EMAIL' => $order->customer['email_address'],
                        'SHIPTOPHONENUM' => $order->customer['telephone'],
                        'CURRENCYCODE' => $order->info['currency'],
                        'BUTTONSOURCE' => 'OSCOM23_DP');

        if ( $HTTP_POST_VARS['cc_type'] == 'MAESTRO' ) {
          $params['STARTDATE'] = $HTTP_POST_VARS['cc_starts_month'] . $HTTP_POST_VARS['cc_starts_year'];
          $params['ISSUENUMBER'] = $HTTP_POST_VARS['cc_issue_nh-dns'];
        }

        if (is_numeric($sendto) && ($sendto > 0)) {
          $params['SHIPTONAME'] = $order->delivery['firstname'] . ' ' . $order->delivery['lastname'];
          $params['SHIPTOSTREET'] = $order->delivery['street_address'];
          $params['SHIPTOCITY'] = $order->delivery['city'];
          $params['SHIPTOSTATE'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
          $params['SHIPTOCOUNTRYCODE'] = $order->delivery['country']['iso_code_2'];
          $params['SHIPTOZIP'] = $order->delivery['postcode'];
        }

        $item_params = array();

        $line_item_no = 0;

        foreach ($order->products as $product) {
          $item_params['L_NAME' . $line_item_no] = $product['name'];
          $item_params['L_AMT' . $line_item_no] = $this->format_raw($product['final_price']);
          $item_params['L_NUMBER' . $line_item_no] = $product['id'];
          $item_params['L_QTY' . $line_item_no] = $product['qty'];

          $line_item_no++;
        }

        $items_total = $this->format_raw($order->info['subtotal']);

        foreach ($order_totals as $ot) {
          if ( !in_array($ot['code'], array('ot_subtotal', 'ot_shipping', 'ot_tax', 'ot_total')) ) {
            $item_params['L_NAME' . $line_item_no] = $ot['title'];
            $item_params['L_AMT' . $line_item_no] = $this->format_raw($ot['value']);

            $items_total += $this->format_raw($ot['value']);

            $line_item_no++;
          }
        }

        $item_params['ITEMAMT'] = $items_total;
        $item_params['TAXAMT'] = $this->format_raw($order->info['tax']);
        $item_params['SHIPPINGAMT'] = $this->format_raw($order->info['shipping_cost']);

        if ( $this->format_raw($item_params['ITEMAMT'] + $item_params['TAXAMT'] + $item_params['SHIPPINGAMT']) == $params['AMT'] ) {
          $params = array_merge($params, $item_params);
        }

        $post_string = '';

        foreach ($params as $key => $value) {
          $post_string .= $key . '=' . urlencode(utf8_encode(trim($value))) . '&';
        }

        $post_string = substr($post_string, 0, -1);

        $response = $this->sendTransactionToGateway($api_url, $post_string);
        $response_array = array();
        parse_str($response, $response_array);

        if (($response_array['ACK'] != 'Success') && ($response_array['ACK'] != 'SuccessWithWarning')) {
          $this->sendDebugEmail($response_array);

          tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'error_message=' . stripslashes($response_array['L_LONGMESSAGE0']), 'SSL'));
        }
      } else {
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, 'error_message=' . MODULE_PAYMENT_PAYPAL_PRO_DP_ERROR_ALL_FIELDS_REQUIRED, 'SSL'));
      }
    }

    function after_process() {
      global $response_array, $insert_id;

      $result = 'Transaction ID: ' . tep_output_string_protected($response_array['TRANSACTIONID']) . "\n" .
                'AVS Code: ' . tep_output_string_protected($response_array['AVSCODE']) . "\n" .
                'CVV2 Match: ' . tep_output_string_protected($response_array['CVV2MATCH']);

      $sql_data_array = array('orders_id' => $insert_id,
                              'orders_status_id' => MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTIONS_ORDER_STATUS_ID,
                              'date_added' => 'now()',
                              'customer_notified' => '0',
                              'comments' => $result);

      tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
    }

    function get_error() {
      return false;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYPAL_PRO_DP_STATUS'");
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
      if (!defined('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTIONS_ORDER_STATUS_ID')) {
        $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'PayPal [Transactions]' limit 1");

        if (tep_db_num_rows($check_query) < 1) {
          $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
          $status = tep_db_fetch_array($status_query);

          $status_id = $status['status_id']+1;

          $languages = tep_get_languages();

          foreach ($languages as $lang) {
            tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $lang['id'] . "', 'PayPal [Transactions]')");
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
        $status_id = MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTIONS_ORDER_STATUS_ID;
      }

      $params = array('MODULE_PAYMENT_PAYPAL_PRO_DP_STATUS' => array('title' => 'Enable PayPal Payments Pro (Direct Payment)',
                                                                     'desc' => 'Do you want to accept PayPal Payments Pro (Direct Payment) payments?',
                                                                     'value' => 'True',
                                                                     'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_PAYPAL_PRO_DP_API_USERNAME' => array('title' => 'API Username',
                                                                           'desc' => 'The username to use for the PayPal API service.'),
                      'MODULE_PAYMENT_PAYPAL_PRO_DP_API_PASSWORD' => array('title' => 'API Password',
                                                                           'desc' => 'The password to use for the PayPal API service.'),
                      'MODULE_PAYMENT_PAYPAL_PRO_DP_API_SIGNATURE' => array('title' => 'API Signature',
                                                                            'desc' => 'The signature to use for the PayPal API service.'),
                      'MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_METHOD' => array('title' => 'Transaction Method',
                                                                                 'desc' => 'The processing method to use for each transaction.',
                                                                                 'value' => 'Sale',
                                                                                 'set_func' => 'tep_cfg_select_option(array(\'Authorization\', \'Sale\'), '),
                      'MODULE_PAYMENT_PAYPAL_PRO_DP_ORDER_STATUS_ID' => array('title' => 'Set Order Status',
                                                                              'desc' => 'Set the status of orders made with this payment module to this value.',
                                                                              'value' => '0',
                                                                              'set_func' => 'tep_cfg_pull_down_order_statuses(',
                                                                              'use_func' => 'tep_get_order_status_name'),
                      'MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTIONS_ORDER_STATUS_ID' => array('title' => 'PayPal Transactions Order Status Level',
                                                                                           'desc' => 'Include PayPal transaction information in this order status level.',
                                                                                           'value' => $status_id,
                                                                                           'use_func' => 'tep_get_order_status_name',
                                                                                           'set_func' => 'tep_cfg_pull_down_order_statuses('),
                      'MODULE_PAYMENT_PAYPAL_PRO_DP_ZONE' => array('title' => 'Payment Zone',
                                                                   'desc' => 'If a zone is selected, only enable this payment method for that zone.',
                                                                   'value' => '0',
                                                                   'set_func' => 'tep_cfg_pull_down_zone_classes(',
                                                                   'use_func' => 'tep_get_zone_class_title'),
                      'MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER' => array('title' => 'Transaction Server',
                                                                                 'desc' => 'Use the live or testing (sandbox) gateway server to process transactions?',
                                                                                 'value' => 'Live',
                                                                                 'set_func' => 'tep_cfg_select_option(array(\'Live\', \'Sandbox\'), '),
                      'MODULE_PAYMENT_PAYPAL_PRO_DP_VERIFY_SSL' => array('title' => 'Verify SSL Certificate',
                                                                         'desc' => 'Verify gateway server SSL certificate on connection?',
                                                                         'value' => 'True',
                                                                         'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_PAYPAL_PRO_DP_PROXY' => array('title' => 'Proxy Server',
                                                                    'desc' => 'Send API requests through this proxy server. (host:port, eg: 123.45.67.89:8080 or proxy.example.com:8080)'),
                      'MODULE_PAYMENT_PAYPAL_PRO_DP_DEBUG_EMAIL' => array('title' => 'Debug E-Mail Address',
                                                                          'desc' => 'All parameters of an invalid transaction will be sent to this email address.'),
                      'MODULE_PAYMENT_PAYPAL_PRO_DP_SORT_ORDER' => array('title' => 'Sort order of display.',
                                                                         'desc' => 'Sort order of display. Lowest is displayed first.',
                                                                         'value' => '0'),
                      'MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_VISA' => array('title' => 'Accept Visa',
                                                                            'desc' => 'Accept Visa card payments?',
                                                                            'value' => 'True',
                                                                            'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_MASTERCARD' => array('title' => 'Accept MasterCard',
                                                                                  'desc' => 'Accept MasterCard card payments?',
                                                                                  'value' => 'True',
                                                                                  'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_DISCOVER' => array('title' => 'Accept Discover',
                                                                                'desc' => 'Accept Discover card payments?',
                                                                                'value' => 'True',
                                                                                'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_AMEX' => array('title' => 'Accept American Express',
                                                                            'desc' => 'Accept American Express card payments?',
                                                                            'value' => 'True',
                                                                            'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_MAESTRO' => array('title' => 'Accept Maestro',
                                                                               'desc' => 'Accept Maestro card payments?',
                                                                               'value' => 'True',
                                                                               'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '));

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

      if ( MODULE_PAYMENT_PAYPAL_PRO_DP_VERIFY_SSL == 'True' ) {
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

      if ( tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_DP_PROXY) ) {
        curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($curl, CURLOPT_PROXY, MODULE_PAYMENT_PAYPAL_PRO_DP_PROXY);
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

    function isCardAccepted($card) {
      return (isset($this->cc_types[$card]) && defined('MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_' . $card) && (constant('MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_' . $card) == 'True'));
    }

    function getTestLinkInfo() {
      $dialog_title = MODULE_PAYMENT_PAYPAL_PRO_DP_DIALOG_CONNECTION_TITLE;
      $dialog_button_close = MODULE_PAYMENT_PAYPAL_PRO_DP_DIALOG_CONNECTION_BUTTON_CLOSE;
      $dialog_success = MODULE_PAYMENT_PAYPAL_PRO_DP_DIALOG_CONNECTION_SUCCESS;
      $dialog_failed = MODULE_PAYMENT_PAYPAL_PRO_DP_DIALOG_CONNECTION_FAILED;
      $dialog_error = MODULE_PAYMENT_PAYPAL_PRO_DP_DIALOG_CONNECTION_ERROR;
      $dialog_connection_time = MODULE_PAYMENT_PAYPAL_PRO_DP_DIALOG_CONNECTION_TIME;

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

      $info = '<p><img src="images/icons/locked.gif" border="0">&nbsp;<a href="javascript:openTestConnectionDialog();" style="text-decoration: underline; font-weight: bold;">' . MODULE_PAYMENT_PAYPAL_PRO_DP_DIALOG_CONNECTION_LINK_TITLE . '</a></p>' .
              '<div id="testConnectionDialog" style="display: none;"><p>';

      if ( MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER == 'Live' ) {
        $info .= 'Live Server:<br />https://api-3t.paypal.com/nvp';
      } else {
        $info .= 'Sandbox Server:<br />https://api-3t.sandbox.paypal.com/nvp';
      }

      $info .= '</p><div id="testConnectionDialogProgress"><p>' . MODULE_PAYMENT_PAYPAL_PRO_DP_DIALOG_CONNECTION_GENERAL_TEXT . '</p><div id="tcdprogressbar"></div></div></div>' .
               $js;

      return $info;
    }

    function getTestConnectionResult() {
      if (MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER == 'Live') {
        $api_url = 'https://api-3t.paypal.com/nvp';
      } else {
        $api_url = 'https://api-3t.sandbox.paypal.com/nvp';
      }

      $params = array('USER' => MODULE_PAYMENT_PAYPAL_PRO_DP_API_USERNAME,
                      'PWD' => MODULE_PAYMENT_PAYPAL_PRO_DP_API_PASSWORD,
                      'VERSION' => $this->api_version,
                      'SIGNATURE' => MODULE_PAYMENT_PAYPAL_PRO_DP_API_SIGNATURE,
                      'METHOD' => 'DoDirectPayment',
                      'PAYMENTACTION' => ((MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_METHOD == 'Sale') ? 'Sale' : 'Authorization'),
                      'IPADDRESS' => tep_get_ip_address());

      $post_string = '';

      foreach ($params as $key => $value) {
        $post_string .= $key . '=' . urlencode(utf8_encode(trim($value))) . '&';
      }

      $post_string = substr($post_string, 0, -1);

      $response = $this->sendTransactionToGateway($api_url, $post_string);
      $response_array = array();
      parse_str($response, $response_array);

      if ( is_array($response_array) && isset($response_array['ACK']) ) {
        return 1;
      }

      return -1;
    }

    function templateClassExists() {
      return class_exists('oscTemplate') && isset($GLOBALS['oscTemplate']) && is_object($GLOBALS['oscTemplate']) && (get_class($GLOBALS['oscTemplate']) == 'oscTemplate');
    }

    function getSubmitCardDetailsJavascript() {
      $test_visa = '';

      if ( MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER == 'Sandbox' ) {
        $test_visa = <<<EOD
    if ( (selected == 'VISA') && ($('#paypal_card_num').val().length < 1) ) {
      $('#paypal_card_num').val('4641631486853053');
    }
EOD;
      }

      $js = <<<EOD
<script type="text/javascript">
$(function() {
  if ( typeof($('#paypal_table_new_card').parent().closest('table').attr('width')) == 'undefined' ) {
    $('#paypal_table_new_card').parent().closest('table').attr('width', '100%');
  }

  paypalShowNewCardFields();

  $('#paypal_card_type').change(function() {
    var selected = $(this).val();

    {$test_visa}

    if ( $('#paypal_card_date_start').length > 0 ) {
      if ( selected == 'MAESTRO' || selected == 'AMEX' ) {
        $('#paypal_card_date_start').parent().parent().show();
      } else {
        $('#paypal_card_date_start').parent().parent().hide();
      }
    }

    if ( $('#paypal_card_issue').length > 0 ) {
      if ( selected == 'MAESTRO' ) {
        $('#paypal_card_issue').parent().parent().show();
      } else {
        $('#paypal_card_issue').parent().parent().hide();
      }
    }
  });
});

function paypalShowNewCardFields() {
  var selected = $('#paypal_card_type').val();

  {$test_visa}

  if ( $('#paypal_card_date_start').length > 0 ) {
    if ( selected != 'MAESTRO' || selected != 'AMEX' ) {
      $('#paypal_card_date_start').parent().parent().hide();
    }
  }

  if ( $('#paypal_card_issue').length > 0 ) {
    if ( selected != 'MAESTRO' ) {
      $('#paypal_card_issue').parent().parent().hide();
    }
  }
}
</script>
EOD;

      return $js;
    }

    function sendDebugEmail($response = array()) {
      global $HTTP_POST_VARS, $HTTP_GET_VARS;

      if (tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_DP_DEBUG_EMAIL)) {
        $email_body = '';

        if (!empty($response)) {
          $email_body .= 'RESPONSE:' . "\n\n" . print_r($response, true) . "\n\n";
        }

        if (!empty($HTTP_POST_VARS)) {
          if (isset($HTTP_POST_VARS['cc_number_nh-dns'])) {
            $HTTP_POST_VARS['cc_number_nh-dns'] = 'XXXX' . substr($HTTP_POST_VARS['cc_number_nh-dns'], -4);
          }

          if (isset($HTTP_POST_VARS['cc_cvc_nh-dns'])) {
            $HTTP_POST_VARS['cc_cvc_nh-dns'] = 'XXX';
          }

          if (isset($HTTP_POST_VARS['cc_issue_nh-dns'])) {
            $HTTP_POST_VARS['cc_issue_nh-dns'] = 'XXX';
          }

          if (isset($HTTP_POST_VARS['cc_expires_month'])) {
            $HTTP_POST_VARS['cc_expires_month'] = 'XX';
          }

          if (isset($HTTP_POST_VARS['cc_expires_year'])) {
            $HTTP_POST_VARS['cc_expires_year'] = 'XX';
          }

          if (isset($HTTP_POST_VARS['cc_starts_month'])) {
            $HTTP_POST_VARS['cc_starts_month'] = 'XX';
          }

          if (isset($HTTP_POST_VARS['cc_starts_year'])) {
            $HTTP_POST_VARS['cc_starts_year'] = 'XX';
          }

          $email_body .= '$HTTP_POST_VARS:' . "\n\n" . print_r($HTTP_POST_VARS, true) . "\n\n";
        }

        if (!empty($HTTP_GET_VARS)) {
          $email_body .= '$HTTP_GET_VARS:' . "\n\n" . print_r($HTTP_GET_VARS, true) . "\n\n";
        }

        if (!empty($email_body)) {
          tep_mail('', MODULE_PAYMENT_PAYPAL_PRO_DP_DEBUG_EMAIL, 'PayPal Payments Pro (Direct Payment) Debug E-Mail', trim($email_body), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
        }
      }
    }
  }
?>
