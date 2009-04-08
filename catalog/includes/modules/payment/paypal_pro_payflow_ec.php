<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2009 osCommerce

  Released under the GNU General Public License
*/

  class paypal_pro_payflow_ec {
    var $code, $title, $description, $enabled;

// class constructor
    function paypal_pro_payflow_ec() {
      global $order;

      $this->signature = 'paypal|paypal_pro_payflow_ec|1.1|2.2';

      $this->code = 'paypal_pro_payflow_ec';
      $this->title = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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

    function checkout_initialization_method() {
      global $language;

      if (file_exists(DIR_FS_CATALOG . 'ext/modules/payment/paypal/images/btn_express_' . basename($language) . '.gif')) {
        $image = 'ext/modules/payment/paypal/images/btn_express_' . basename($language) . '.gif';
      } else {
        $image = 'ext/modules/payment/paypal/images/btn_express.gif';
      }

      $string = '<a href="' . tep_href_link('ext/modules/payment/paypal/express_payflow.php', '', 'SSL') . '"><img src="' . $image . '" border="0" alt="" title="' . tep_output_string_protected(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TEXT_BUTTON) . '" /></a>';

      return $string;
    }

    function javascript_validation() {
      return false;
    }

    function selection() {
      return array('id' => $this->code,
                   'module' => $this->public_title);
    }

    function pre_confirmation_check() {
      if (!tep_session_is_registered('ppeuk_token')) {
        tep_redirect(tep_href_link('ext/modules/payment/paypal/express_payflow.php', '', 'SSL'));
      }
    }

    function confirmation() {
      global $comments;

      if (!isset($comments)) {
        $comments = null;
      }

      $confirmation = false;

      if (empty($comments)) {
        $confirmation = array('fields' => array(array('title' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TEXT_COMMENTS,
                                                      'field' => tep_draw_textarea_field('ppecomments', 'soft', '60', '5', $comments))));
      }

      return $confirmation;
    }

    function process_button() {
      return false;
    }

    function before_process() {
      global $order, $sendto, $ppeuk_token, $ppeuk_payerid, $HTTP_POST_VARS, $comments;

      if (empty($comments)) {
        if (isset($HTTP_POST_VARS['ppecomments']) && tep_not_null($HTTP_POST_VARS['ppecomments'])) {
          $comments = tep_db_prepare_input($HTTP_POST_VARS['ppecomments']);

          $order->info['comments'] = $comments;
        }
      }

      if (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTION_SERVER == 'Live') {
        $api_url = 'https://payflowpro.verisign.com/transaction';
      } else {
        $api_url = 'https://pilot-payflowpro.verisign.com/transaction';
      }

      $params = array('USER' => (tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_USERNAME) ? MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_USERNAME : MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_VENDOR),
                      'VENDOR' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_VENDOR,
                      'PARTNER' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PARTNER,
                      'PWD' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PASSWORD,
                      'TENDER' => 'P',
                      'TRXTYPE' => ((MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTION_METHOD == 'Sale') ? 'S' : 'A'),
                      'EMAIL' => $order->customer['email_address'],
                      'TOKEN' => $ppeuk_token,
                      'ACTION' => 'D',
                      'PAYERID' => $ppeuk_payerid,
                      'AMT' => $this->format_raw($order->info['total']),
                      'CURRENCY' => $order->info['currency'],
                      'BUTTONSOURCE' => 'osCommerce22_Default_PRO2EC');

      if (is_numeric($sendto) && ($sendto > 0)) {
        $params['SHIPTOSTREET'] = $order->delivery['street_address'];
        $params['SHIPTOCITY'] = $order->delivery['city'];
        $params['SHIPTOSTATE'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
        $params['SHIPTOCOUNTRY'] = $order->delivery['country']['iso_code_2'];
        $params['SHIPTOZIP'] = $order->delivery['postcode'];
      }

      $post_string = '';

      foreach ($params as $key => $value) {
        $post_string .= $key . '[' . strlen(urlencode(utf8_encode(trim($value)))) . ']=' . urlencode(utf8_encode(trim($value))) . '&';
      }

      $post_string = substr($post_string, 0, -1);

      $response = $this->sendTransactionToGateway($api_url, $post_string, array('X-VPS-REQUEST-ID: ' . md5($cartID . tep_session_id() . rand())));
      $response_array = array();
      parse_str($response, $response_array);

      if ($response_array['RESULT'] != '0') {
        switch ($response_array['RESULT']) {
          case '1':
          case '26':
            $error_message = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_CFG_ERROR;
            break;

          case '7':
            $error_message = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_ADDRESS;
            break;

          case '12':
            $error_message = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_DECLINED;
            break;

          case '1000':
            $error_message = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_EXPRESS_DISABLED;
            break;

          default:
            $error_message = MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_GENERAL;
            break;
        }

        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'error_message=' . urlencode($error_message), 'SSL'));
      }
    }

    function after_process() {
      tep_session_unregister('ppeuk_token');
      tep_session_unregister('ppeuk_payerid');
    }

    function get_error() {
      return false;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable PayPal Express Checkout (UK)', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_STATUS', 'False', 'Do you want to accept PayPal Express Checkout (UK) payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Vendor', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_VENDOR', '', 'Your merchant login ID that you created when you registered for the Website Payments Pro account.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('User', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_USERNAME', '', 'If you set up one or more additional users on the account, this value is the ID of the user authorised to process transactions. If, however, you have not set up additional users on the account, USER has the same value as VENDOR.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Password', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PASSWORD', '', 'The 6- to 32-character password that you defined while registering for the account.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Partner', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PARTNER', 'PayPalUK', 'The ID provided to you by the authorised PayPal Reseller who registered you for the Payflow SDK. If you purchased your account directly from PayPal, use PayPalUK.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Server', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTION_SERVER', 'Live', 'Use the live or testing (sandbox) gateway server to process transactions?', '6', '0', 'tep_cfg_select_option(array(\'Live\', \'Sandbox\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Method', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTION_METHOD', 'Sale', 'The processing method to use for each transaction.', '6', '0', 'tep_cfg_select_option(array(\'Authorization\', \'Sale\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value.', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('cURL Program Location', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_CURL', '/usr/bin/curl', 'The location to the cURL program application.', '6', '0' , now())");
   }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_STATUS', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_VENDOR', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_USERNAME', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PASSWORD', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PARTNER', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTION_SERVER', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTION_METHOD', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ZONE', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ORDER_STATUS_ID', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_SORT_ORDER', 'MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_CURL');
    }

    function sendTransactionToGateway($url, $parameters, $headers = null) {
      $header = array();

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

      if (!empty($headers) && is_array($headers)) {
        $header = array_merge($header, $headers);
      }

      if (function_exists('curl_init')) {
        $curl = curl_init($server['scheme'] . '://' . $server['host'] . $server['path'] . (isset($server['query']) ? '?' . $server['query'] : ''));
        curl_setopt($curl, CURLOPT_PORT, $server['port']);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);

        if (!empty($header)) {
          curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }

        $result = curl_exec($curl);

        curl_close($curl);
      } else {
        exec(escapeshellarg(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_CURL) . ' -d ' . escapeshellarg($parameters) . ' "' . $server['scheme'] . '://' . $server['host'] . $server['path'] . (isset($server['query']) ? '?' . $server['query'] : '') . '" -P ' . $server['port'] . ' -k' . (!empty($header) ? ' -H ' . escapeshellarg(implode("\r\n", $header)) : ''), $result);
        $result = implode("\n", $result);
      }

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
  }
?>
