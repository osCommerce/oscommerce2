<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/

  class payquake_cc {
    var $code, $title, $description, $enabled;

// class constructor
    function payquake_cc() {
      global $order;

      $this->code = 'payquake_cc';
      $this->title = MODULE_PAYMENT_PAYQUAKE_CC_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_PAYQUAKE_CC_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_PAYQUAKE_CC_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_PAYQUAKE_CC_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_PAYQUAKE_CC_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_PAYQUAKE_CC_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_PAYQUAKE_CC_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PAYQUAKE_CC_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PAYQUAKE_CC_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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
        $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1,2000)));
      }

      $today = getdate(); 
      for ($i=$today['year']; $i < $today['year']+10; $i++) {
        $expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
      }

      $confirmation = array('fields' => array(array('title' => MODULE_PAYMENT_PAYQUAKE_CC_CREDIT_CARD_OWNER,
                                                    'field' => tep_draw_input_field('payquake_cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'])),
                                              array('title' => MODULE_PAYMENT_PAYQUAKE_CC_CREDIT_CARD_NUMBER,
                                                    'field' => tep_draw_input_field('payquake_cc_number_nh-dns')),
                                              array('title' => MODULE_PAYMENT_PAYQUAKE_CC_CREDIT_CARD_EXPIRES,
                                                    'field' => tep_draw_pull_down_menu('payquake_cc_expires_month', $expires_month) . '&nbsp;' . tep_draw_pull_down_menu('payquake_cc_expires_year', $expires_year))));

      if (MODULE_PAYMENT_PAYQUAKE_CC_VERIFY_WITH_CVC == 'True') {
        $confirmation['fields'][] = array('title' => MODULE_PAYMENT_PAYQUAKE_CC_CREDIT_CARD_CVC,
                                          'field' => tep_draw_input_field('payquake_cc_cvc_nh-dns', '', 'size="5" maxlength="4"'));
      }

      return $confirmation;
    }

    function process_button() {
      return false;
    }

    function before_process() {
      global $customer_id, $order, $HTTP_POST_VARS;

      $this->pre_confirmation_check();

      $params = array('action' => 'ns_quicksale_cc',
                      'acctid' => MODULE_PAYMENT_PAYQUAKE_CC_ACCOUNT_ID,
                      'amount' => number_format($order->info['total'], 2),
                      'ccname' => $HTTP_POST_VARS['payquake_cc_owner'],
                      'expmon' => $HTTP_POST_VARS['payquake_cc_expires_month'],
                      'expyear' => $HTTP_POST_VARS['payquake_cc_expires_year'],
                      'authonly' => '1',
                      'ci_companyname' => $order->billing['company'],
                      'ci_billaddr1' => $order->billing['street_address'],
                      'ci_billcity' => $order->billing['city'],
                      'ci_billstate' => $order->billing['state'],
                      'ci_billzip' => $order->billing['postcode'],
                      'ci_billcountry' => $order->billing['country']['title'],
                      'ci_shipaddr1' => $order->delivery['street_address'],
                      'ci_shipcity' => $order->delivery['city'],
                      'ci_shipstate' => $order->delivery['state'],
                      'ci_shipzip' => $order->delivery['postcode'],
                      'ci_shipcountry' => $order->delivery['country']['title'],
                      'ci_phone' => $order->customer['telephone'],
                      'ci_email' => $order->customer['email_address'],
                      'email_from' => STORE_OWNER_EMAIL_ADDRESS,
                      'ci_ipaddress' => tep_get_ip_address(),
                      'merchantordernumber' => $customer_id);

      if (tep_not_null(MODULE_PAYMENT_PAYQUAKE_CC_3DES)) {
        $key = pack('H48', MODULE_PAYMENT_PAYQUAKE_CC_3DES);
        $data = bin2hex(mcrypt_encrypt(MCRYPT_3DES, $key, $HTTP_POST_VARS['payquake_cc_number_nh-dns'], MCRYPT_MODE_ECB));

        $params['ccnum'] = $data;

        unset($key);
        unset($data);
      } else {
        $params['ccnum'] = $HTTP_POST_VARS['payquake_cc_number_nh-dns'];
      }

      if (MODULE_PAYMENT_PAYQUAKE_CC_VERIFY_WITH_CVC == 'True') {
        $params['cvv2'] = (int)$HTTP_POST_VARS['payquake_cc_cvc_nh-dns'];
      }

      if (tep_not_null(MODULE_PAYMENT_PAYQUAKE_CC_MERCHANT_PIN)) {
        $params['merchantPIN'] = MODULE_PAYMENT_PAYQUAKE_CC_MERCHANT_PIN;
      }

      $post_string = '';

      reset($params);
      while (list($key, $value) = each($params)) {
        $post_string .= $key . '=' . urlencode(trim($value)) . '&';
      }

      $post_string = substr($post_string, 0, -1);

      $transaction_response = $this->sendTransactionToGateway('https://trans.merchantpartners.com/cgi-bin/process.cgi', $post_string);

      $error = false;

      if (!empty($transaction_response)) {
        $regs = explode("\n", trim($transaction_response));
        array_shift($regs);

        $result = array();

        reset($regs);
        while (list($key, $value) = each($regs)) {
          $res = explode('=', $value, 2);

          $result[strtolower(trim($res[0]))] = trim($res[1]);
        }

        if ($result['status'] != 'Accepted') {
          $error = explode(':', $result['reason'], 3);
          $error = $error[2];

          if (empty($error)) {
            $error = MODULE_PAYMENT_PAYQUAKE_CC_ERROR_GENERAL;
          }
        }
      } else {
        $error = MODULE_PAYMENT_PAYQUAKE_CC_ERROR_GENERAL;
      }

      if ($error) {
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=' . urlencode($error), 'SSL'));
      }
    }

    function after_process() {
      return false;
    }

    function get_error() {
      global $HTTP_GET_VARS;

      $error = array('title' => MODULE_PAYMENT_PAYQUAKE_CC_TEXT_ERROR,
                     'error' => stripslashes(urldecode($HTTP_GET_VARS['error'])));

      return $error;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYQUAKE_CC_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable PayQuake Credit Card Module', 'MODULE_PAYMENT_PAYQUAKE_CC_STATUS', 'False', 'Do you want to accept PayQuake credit card payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Account ID', 'MODULE_PAYMENT_PAYQUAKE_CC_ACCOUNT_ID', '', 'The account ID of the PayQuake account to use.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('3DES Encryption', 'MODULE_PAYMENT_PAYQUAKE_CC_3DES', '', 'Use this 3DES encryption key if it is enabled on the PayQuake Online Merchant Center.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant PIN', 'MODULE_PAYMENT_PAYQUAKE_CC_MERCHANT_PIN', '', 'Use this Merchant PIN if it is enabled on the PayQuake Online Merchant Center.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Verify With CVC', 'MODULE_PAYMENT_PAYQUAKE_CC_VERIFY_WITH_CVC', 'True', 'Verify the credit card with the billing address with the Credit Card Verification Checknumber (CVC)?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_PAYQUAKE_CC_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_PAYQUAKE_CC_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_PAYQUAKE_CC_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0' , now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('cURL Program Location', 'MODULE_PAYMENT_PAYQUAKE_CC_CURL', '/usr/bin/curl', 'The location to the cURL program application.', '6', '0' , now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_PAYQUAKE_CC_STATUS', 'MODULE_PAYMENT_PAYQUAKE_CC_ACCOUNT_ID', 'MODULE_PAYMENT_PAYQUAKE_CC_3DES', 'MODULE_PAYMENT_PAYQUAKE_CC_MERCHANT_PIN', 'MODULE_PAYMENT_PAYQUAKE_CC_VERIFY_WITH_CVC', 'MODULE_PAYMENT_PAYQUAKE_CC_ZONE', 'MODULE_PAYMENT_PAYQUAKE_CC_ORDER_STATUS_ID', 'MODULE_PAYMENT_PAYQUAKE_CC_SORT_ORDER', 'MODULE_PAYMENT_PAYQUAKE_CC_CURL');
    }

    function sendTransactionToGateway($url, $parameters) {
      $server = parse_url($url);

      if (isset($server['port']) === false) {
        $server['port'] = ($server['scheme'] == 'https') ? 443 : 80;
      }

      if (isset($server['path']) === false) {
        $server['path'] = '/';
      }

      if (isset($server['user']) && isset($server['pass'])) {
        $header[] = 'Authorization: Basic ' . base64_encode($server['user'] . ':' . $server['pass']);
      }

      $connection_method = 0;

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

        $result = curl_exec($curl);

        curl_close($curl);
      } else {
        exec(escapeshellarg(MODULE_PAYMENT_PAYQUAKE_CC_CURL) . ' -d ' . escapeshellarg($parameters) . ' "' . $server['scheme'] . '://' . $server['host'] . $server['path'] . (isset($server['query']) ? '?' . $server['query'] : '') . '" -P ' . $server['port'] . ' -k', $result);
        $result = implode("\n", $result);
      }

      return $result;
    }
  }
?>