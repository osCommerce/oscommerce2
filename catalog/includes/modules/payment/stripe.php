<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class stripe {
    var $code, $title, $description, $enabled;

    function stripe() {
      global $HTTP_GET_VARS, $PHP_SELF, $order, $payment;

      $this->signature = 'stripe|stripe|1.0|2.3';
      $this->api_version = '2014-05-19';

      $this->code = 'stripe';
      $this->title = MODULE_PAYMENT_STRIPE_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_STRIPE_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_STRIPE_TEXT_DESCRIPTION;
      $this->sort_order = defined('MODULE_PAYMENT_STRIPE_SORT_ORDER') ? MODULE_PAYMENT_STRIPE_SORT_ORDER : 0;
      $this->enabled = defined('MODULE_PAYMENT_STRIPE_STATUS') && (MODULE_PAYMENT_STRIPE_STATUS == 'True') ? true : false;
      $this->order_status = defined('MODULE_PAYMENT_STRIPE_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_STRIPE_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_STRIPE_ORDER_STATUS_ID : 0;

      if ( defined('MODULE_PAYMENT_STRIPE_STATUS') ) {
        if ( MODULE_PAYMENT_STRIPE_TRANSACTION_SERVER == 'Test' ) {
          $this->title .= ' [Test]';
          $this->public_title .= ' (' . $this->code . '; Test)';
        }

        $this->description .= $this->getTestLinkInfo();
      }

      if ( !function_exists('curl_init') ) {
        $this->description = '<div class="secWarning">' . MODULE_PAYMENT_STRIPE_ERROR_ADMIN_CURL . '</div>' . $this->description;

        $this->enabled = false;
      }

      if ( $this->enabled === true ) {
        if ( !tep_not_null(MODULE_PAYMENT_STRIPE_PUBLISHABLE_KEY) || !tep_not_null(MODULE_PAYMENT_STRIPE_SECRET_KEY) ) {
          $this->description = '<div class="secWarning">' . MODULE_PAYMENT_STRIPE_ERROR_ADMIN_CONFIGURATION . '</div>' . $this->description;

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

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_STRIPE_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_STRIPE_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
      global $customer_id, $payment;

      if ( (MODULE_PAYMENT_STRIPE_TOKENS == 'True') && !tep_session_is_registered('payment') ) {
        $tokens_query = tep_db_query("select 1 from customers_stripe_tokens where customers_id = '" . (int)$customer_id . "' limit 1");

        if ( tep_db_num_rows($tokens_query) ) {
          $payment = $this->code;
          tep_session_register('payment');
        }
      }

      return array('id' => $this->code,
                   'module' => $this->public_title);
    }

    function pre_confirmation_check() {
      global $oscTemplate;

      if ( $this->templateClassExists() ) {
        $oscTemplate->addBlock($this->getSubmitCardDetailsJavascript(), 'header_tags');
      }
    }

    function confirmation() {
      global $customer_id, $order, $currencies, $currency;

      $months_array = array();

      for ($i=1; $i<13; $i++) {
        $months_array[] = array('id' => tep_output_string(sprintf('%02d', $i)),
                                'text' => tep_output_string_protected(sprintf('%02d', $i)));
      }

      $today = getdate(); 
      $years_array = array();

      for ($i=$today['year']; $i < $today['year']+10; $i++) {
        $years_array[] = array('id' => tep_output_string(strftime('%Y',mktime(0,0,0,1,1,$i))),
                               'text' => tep_output_string_protected(strftime('%Y',mktime(0,0,0,1,1,$i))));
      }

      $months_string = '<select data-stripe="exp_month">';
      foreach ( $months_array as $m ) {
        $months_string .= '<option value="' . tep_output_string($m['id']) . '">' . tep_output_string($m['text']) . '</option>';
      }
      $months_string .= '</select>';

      $years_string = '<select data-stripe="exp_year">';
      foreach ( $years_array as $y ) {
        $years_string .= '<option value="' . tep_output_string($y['id']) . '">' . tep_output_string($y['text']) . '</option>';
      }
      $years_string .= '</select>';

      $content = '';

      if ( MODULE_PAYMENT_STRIPE_TOKENS == 'True' ) {
        $tokens_query = tep_db_query("select id, card_type, number_filtered, expiry_date from customers_stripe_tokens where customers_id = '" . (int)$customer_id . "' order by date_added");

        if ( tep_db_num_rows($tokens_query) > 0 ) {
          $content .= '<table id="stripe_table" border="0" width="100%" cellspacing="0" cellpadding="2">';

          while ( $tokens = tep_db_fetch_array($tokens_query) ) {
            $content .= '<tr class="moduleRow" id="stripe_card_' . (int)$tokens['id'] . '">' .
                        '  <td width="40" valign="top"><input type="radio" name="stripe_card" value="' . (int)$tokens['id'] . '" /></td>' .
                        '  <td valign="top"><strong>' . tep_output_string_protected($tokens['card_type']) . '</strong>&nbsp;&nbsp;****' . tep_output_string_protected($tokens['number_filtered']) . '&nbsp;&nbsp;' . tep_output_string_protected(substr($tokens['expiry_date'], 0, 2) . '/' . substr($tokens['expiry_date'], 2)) . '</td>' .
                        '</tr>';
          }

          $content .= '<tr class="moduleRow" id="stripe_card_0">' .
                      '  <td width="40" valign="top"><input type="radio" name="stripe_card" value="0" /></td>' .
                      '  <td valign="top">' . MODULE_PAYMENT_STRIPE_CREDITCARD_NEW . '</td>' .
                      '</tr>' .
                      '</table>';
        }
      }

      $content .= '<div class="messageStackError payment-errors"></div>' .
                  '<table id="stripe_table_new_card" border="0" width="100%" cellspacing="0" cellpadding="2">' .
                  '<tr>' .
                  '  <td width="30%">' . MODULE_PAYMENT_STRIPE_CREDITCARD_OWNER . '</td>' .
                  '  <td><input type="text" data-stripe="name" value="' . tep_output_string($order->billing['firstname'] . ' ' . $order->billing['lastname']) . '" /></td>' .
                  '</tr>' .
                  '<tr>' .
                  '  <td width="30%">' . MODULE_PAYMENT_STRIPE_CREDITCARD_NUMBER . '</td>' .
                  '  <td><input type="text" maxlength="20" autocomplete="off" data-stripe="number" /></td>' .
                  '</tr>' .
                  '<tr>' .
                  '  <td width="30%">' . MODULE_PAYMENT_STRIPE_CREDITCARD_EXPIRY . '</td>' .
                  '  <td>' . $months_string . ' / ' . $years_string . '</td>' .
                  '</tr>';

      if ( MODULE_PAYMENT_STRIPE_VERIFY_WITH_CVC == 'True' ) {
        $content .= '<tr>' .
                    '  <td width="30%">' . MODULE_PAYMENT_STRIPE_CREDITCARD_CVC . '</td>' .
                    '  <td><input type="text" size="5" maxlength="4" autocomplete="off" data-stripe="cvc" /></td>' .
                    '</tr>';
      }

      if ( MODULE_PAYMENT_STRIPE_TOKENS == 'True' ) {
        $content .= '<tr>' .
                    '  <td width="30%">&nbsp;</td>' .
                    '  <td>' . tep_draw_checkbox_field('cc_save', 'true') . ' ' . MODULE_PAYMENT_STRIPE_CREDITCARD_SAVE . '</td>' .
                    '</tr>';
      }

      $content .= '</table>';

      $address = array('address_line1' => $order->billing['street_address'],
                       'address_city' => $order->billing['city'],
                       'address_zip' => $order->billing['postcode'],
                       'address_state' => tep_get_zone_name($order->billing['country_id'], $order->billing['zone_id'], $order->billing['state']),
                       'address_country' => $order->billing['country']['iso_code_2']);

      foreach ( $address as $k => $v ) {
        $content .= '<input type="hidden" data-stripe="' . tep_output_string($k) . '" value="' . tep_output_string($v) . '" />';
      }

      if ( !$this->templateClassExists() ) {
        $content .= $this->getSubmitCardDetailsJavascript();
      }

      $confirmation = array('title' => $content);

      return $confirmation;
    }

    function process_button() {
      return false;
    }

    function before_process() {
      global $customer_id, $order, $currency, $HTTP_POST_VARS, $stripe_result, $stripe_error;

      $stripe_result = null;

      $params = array();

      if ( MODULE_PAYMENT_STRIPE_TOKENS == 'True' ) {
        if ( isset($HTTP_POST_VARS['stripe_card']) && is_numeric($HTTP_POST_VARS['stripe_card']) && ($HTTP_POST_VARS['stripe_card'] > 0) ) {
          $token_query = tep_db_query("select stripe_token from customers_stripe_tokens where id = '" . (int)$HTTP_POST_VARS['stripe_card'] . "' and customers_id = '" . (int)$customer_id . "'");

          if ( tep_db_num_rows($token_query) === 1 ) {
            $token = tep_db_fetch_array($token_query);

            $stripe_token_array = explode(':|:', $token['stripe_token'], 2);

            $params['customer'] = $stripe_token_array[0];
            $params['card'] = $stripe_token_array[1];
          } else {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=cardstored', 'SSL'));
          }
        }
      }

      if ( empty($params) && isset($HTTP_POST_VARS['stripeToken']) && !empty($HTTP_POST_VARS['stripeToken']) ) {
        if ( (MODULE_PAYMENT_STRIPE_TOKENS == 'True') && isset($HTTP_POST_VARS['cc_save']) && ($HTTP_POST_VARS['cc_save'] == 'true') ) {
          $stripe_customer_id = $this->getCustomerID();
          $stripe_card_id = false;

          if ( $stripe_customer_id === false ) {
            $stripe_customer_array = $this->createCustomer($HTTP_POST_VARS['stripeToken']);

            if ( ($stripe_customer_array !== false) && isset($stripe_customer_array['id']) ) {
              $stripe_customer_id = $stripe_customer_array['id'];
              $stripe_card_id = $stripe_customer_array['card_id'];
            }
          } else {
            $stripe_card_id = $this->addCard($HTTP_POST_VARS['stripeToken'], $stripe_customer_id);
          }

          if ( ($stripe_customer_id !== false) && ($stripe_card_id !== false) ) {
            $params['customer'] = $stripe_customer_id;
            $params['card'] = $stripe_card_id;
          }
        } else {
          $params['card'] = $HTTP_POST_VARS['stripeToken'];
        }
      }

      if ( !empty($params) ) {
        $params['amount'] = $this->format_raw($order->info['total']);
        $params['currency'] = $currency;
        $params['capture'] = (MODULE_PAYMENT_STRIPE_TRANSACTION_METHOD == 'Capture') ? 'true' : 'false';

        $stripe_result = json_decode($this->sendTransactionToGateway('https://api.stripe.com/v1/charges', $params), true);

        if ( is_array($stripe_result) && !empty($stripe_result) ) {
          if ( isset($stripe_result['object']) && ($stripe_result['object'] == 'charge') ) {
            return true;
          }
        }
      }

      if ( isset($stripe_result['error']['message']) ) {
        tep_session_register('stripe_error');

        $stripe_error = $stripe_result['error']['message'];
      }

      $this->sendDebugEmail($stripe_result);

      tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code, 'SSL'));
    }

    function after_process() {
      global $insert_id, $customer_id, $stripe_result, $HTTP_POST_VARS;

      $status_comment = array('Transaction ID: ' . $stripe_result['id'],
                              'CVC: ' . $stripe_result['card']['cvc_check']);

      if ( !empty($stripe_result['card']['address_line1_check']) ) {
        $status_comment[] = 'Address Check: ' . $stripe_result['card']['address_line1_check'];
      }

      if ( !empty($stripe_result['card']['address_zip_check']) ) {
        $status_comment[] = 'ZIP Check: ' . $stripe_result['card']['address_zip_check'];
      }

      if ( MODULE_PAYMENT_STRIPE_TOKENS == 'True' ) {
        if ( isset($HTTP_POST_VARS['cc_save']) && ($HTTP_POST_VARS['cc_save'] == 'true') ) {
          $status_comment[] = 'Token Saved: Yes';
        } elseif ( isset($HTTP_POST_VARS['stripe_card']) && is_numeric($HTTP_POST_VARS['stripe_card']) && ($HTTP_POST_VARS['stripe_card'] > 0) ) {
          $status_comment[] = 'Token Used: Yes';
        }
      }

      $sql_data_array = array('orders_id' => $insert_id,
                              'orders_status_id' => MODULE_PAYMENT_STRIPE_TRANSACTION_ORDER_STATUS_ID,
                              'date_added' => 'now()',
                              'customer_notified' => '0',
                              'comments' => implode("\n", $status_comment));

      tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

      if ( tep_session_is_registered('stripe_error') ) {
        tep_session_unregister('stripe_error');
      }
    }

    function get_error() {
      global $HTTP_GET_VARS, $stripe_error;

      $message = MODULE_PAYMENT_STRIPE_ERROR_GENERAL;

      if ( tep_session_is_registered('stripe_error') ) {
        $message = $stripe_error . ' ' . $message;

        tep_session_unregister('stripe_error');
      }

      if ( isset($HTTP_GET_VARS['error']) && !empty($HTTP_GET_VARS['error']) ) {
        switch ($HTTP_GET_VARS['error']) {
          case 'cardstored':
            $message = MODULE_PAYMENT_STRIPE_ERROR_CARDSTORED;
            break;
        }
      }

      $error = array('title' => MODULE_PAYMENT_STRIPE_ERROR_TITLE,
                     'error' => $message);

      return $error;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_STRIPE_STATUS'");
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
      if ( tep_db_num_rows(tep_db_query("show tables like 'customers_stripe_tokens'")) != 1 ) {
        $sql = <<<EOD
CREATE TABLE customers_stripe_tokens (
  id int NOT NULL auto_increment,
  customers_id int NOT NULL,
  stripe_token varchar(255) NOT NULL,
  card_type varchar(32) NOT NULL,
  number_filtered varchar(20) NOT NULL,
  expiry_date char(6) NOT NULL,
  date_added datetime NOT NULL,
  PRIMARY KEY (id),
  KEY idx_cstripet_customers_id (customers_id),
  KEY idx_cstripet_token (stripe_token)
);
EOD;

        tep_db_query($sql);
      }

      if (!defined('MODULE_PAYMENT_STRIPE_TRANSACTION_ORDER_STATUS_ID')) {
        $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Stripe [Transactions]' limit 1");

        if (tep_db_num_rows($check_query) < 1) {
          $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
          $status = tep_db_fetch_array($status_query);

          $status_id = $status['status_id']+1;

          $languages = tep_get_languages();

          foreach ($languages as $lang) {
            tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $lang['id'] . "', 'Stripe [Transactions]')");
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
        $status_id = MODULE_PAYMENT_STRIPE_TRANSACTION_ORDER_STATUS_ID;
      }

      $params = array('MODULE_PAYMENT_STRIPE_STATUS' => array('title' => 'Enable Stripe Module',
                                                              'desc' => 'Do you want to accept Stripe payments?',
                                                              'value' => 'True',
                                                              'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_STRIPE_PUBLISHABLE_KEY' => array('title' => 'Publishable API Key',
                                                                       'desc' => 'The Stripe account publishable API key to use.',
                                                                       'value' => ''),
                      'MODULE_PAYMENT_STRIPE_SECRET_KEY' => array('title' => 'Secret API Key',
                                                                  'desc' => 'The Stripe account secret API key to use with the publishable key.',
                                                                  'value' => ''),
                      'MODULE_PAYMENT_STRIPE_TOKENS' => array('title' => 'Create Tokens',
                                                              'desc' => 'Create and store tokens for card payments customers can use on their next purchase?',
                                                              'value' => 'False',
                                                              'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_STRIPE_VERIFY_WITH_CVC' => array('title' => 'Verify With CVC',
                                                                       'desc' => 'Verify the credit card billing address with the Card Verification Code (CVC)?',
                                                                       'value' => 'True',
                                                                       'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_STRIPE_TRANSACTION_METHOD' => array('title' => 'Transaction Method',
                                                                          'desc' => 'The processing method to use for each transaction.',
                                                                          'value' => 'Authorize',
                                                                          'set_func' => 'tep_cfg_select_option(array(\'Authorize\', \'Capture\'), '),
                      'MODULE_PAYMENT_STRIPE_ORDER_STATUS_ID' => array('title' => 'Set Order Status',
                                                                       'desc' => 'Set the status of orders made with this payment module to this value',
                                                                       'value' => '0',
                                                                       'use_func' => 'tep_get_order_status_name',
                                                                       'set_func' => 'tep_cfg_pull_down_order_statuses('),
                      'MODULE_PAYMENT_STRIPE_TRANSACTION_ORDER_STATUS_ID' => array('title' => 'Transaction Order Status',
                                                                                   'desc' => 'Include transaction information in this order status level',
                                                                                   'value' => $status_id,
                                                                                   'set_func' => 'tep_cfg_pull_down_order_statuses(',
                                                                                   'use_func' => 'tep_get_order_status_name'),
                      'MODULE_PAYMENT_STRIPE_ZONE' => array('title' => 'Payment Zone',
                                                            'desc' => 'If a zone is selected, only enable this payment method for that zone.',
                                                            'value' => '0',
                                                            'use_func' => 'tep_get_zone_class_title',
                                                            'set_func' => 'tep_cfg_pull_down_zone_classes('),
                      'MODULE_PAYMENT_STRIPE_TRANSACTION_SERVER' => array('title' => 'Transaction Server',
                                                                          'desc' => 'Perform transactions on the production server or on the testing server.',
                                                                          'value' => 'Live',
                                                                          'set_func' => 'tep_cfg_select_option(array(\'Live\', \'Test\'), '),
                      'MODULE_PAYMENT_STRIPE_VERIFY_SSL' => array('title' => 'Verify SSL Certificate',
                                                                  'desc' => 'Verify gateway server SSL certificate on connection?',
                                                                  'value' => 'True',
                                                                  'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_STRIPE_PROXY' => array('title' => 'Proxy Server',
                                                             'desc' => 'Send API requests through this proxy server. (host:port, eg: 123.45.67.89:8080 or proxy.example.com:8080)'),
                      'MODULE_PAYMENT_STRIPE_DEBUG_EMAIL' => array('title' => 'Debug E-Mail Address',
                                                                   'desc' => 'All parameters of an invalid transaction will be sent to this email address.'),
                      'MODULE_PAYMENT_STRIPE_SORT_ORDER' => array('title' => 'Sort order of display.',
                                                                  'desc' => 'Sort order of display. Lowest is displayed first.',
                                                                  'value' => '0'));

      return $params;
    }

    function sendTransactionToGateway($url, $parameters = null, $curl_opts = array()) {
      $server = parse_url($url);

      if (isset($server['port']) === false) {
        $server['port'] = ($server['scheme'] == 'https') ? 443 : 80;
      }

      if (isset($server['path']) === false) {
        $server['path'] = '/';
      }

      $header = array('Stripe-Version: ' . $this->api_version,
                      'User-Agent: OSCOM ' . tep_get_version());

      if ( is_array($parameters) && !empty($parameters) ) {
        $post_string = '';

        foreach ($parameters as $key => $value) {
          $post_string .= $key . '=' . urlencode(utf8_encode(trim($value))) . '&';
        }

        $post_string = substr($post_string, 0, -1);

        $parameters = $post_string;
      }

      $curl = curl_init($server['scheme'] . '://' . $server['host'] . $server['path'] . (isset($server['query']) ? '?' . $server['query'] : ''));
      curl_setopt($curl, CURLOPT_PORT, $server['port']);
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
      curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
      curl_setopt($curl, CURLOPT_USERPWD, MODULE_PAYMENT_STRIPE_SECRET_KEY . ':');
      curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

      if ( !empty($parameters) ) {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
      }

      if ( MODULE_PAYMENT_STRIPE_VERIFY_SSL == 'True' ) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

        if ( file_exists(DIR_FS_CATALOG . 'ext/modules/payment/stripe/stripe.com.crt') ) {
          curl_setopt($curl, CURLOPT_CAINFO, DIR_FS_CATALOG . 'ext/modules/payment/stripe/stripe.com.crt');
        } elseif ( file_exists(DIR_FS_CATALOG . 'includes/cacert.pem') ) {
          curl_setopt($curl, CURLOPT_CAINFO, DIR_FS_CATALOG . 'includes/cacert.pem');
        }
      } else {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      }

      if ( tep_not_null(MODULE_PAYMENT_STRIPE_PROXY) ) {
        curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($curl, CURLOPT_PROXY, MODULE_PAYMENT_STRIPE_PROXY);
      }

      if ( !empty($curl_opts) ) {
        foreach ( $curl_opts as $key => $value ) {
          curl_setopt($curl, $key, $value);
        }
      }

      $result = curl_exec($curl);

      curl_close($curl);

      return $result;
    }

    function getTestLinkInfo() {
      $dialog_title = MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_TITLE;
      $dialog_button_close = MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_BUTTON_CLOSE;
      $dialog_success = MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_SUCCESS;
      $dialog_failed = MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_FAILED;
      $dialog_error = MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_ERROR;
      $dialog_connection_time = MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_TIME;

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

      $info = '<p><img src="images/icons/locked.gif" border="0">&nbsp;<a href="javascript:openTestConnectionDialog();" style="text-decoration: underline; font-weight: bold;">' . MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_LINK_TITLE . '</a></p>' .
              '<div id="testConnectionDialog" style="display: none;"><p>Server:<br />https://api.stripe.com/v1/</p><div id="testConnectionDialogProgress"><p>' . MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_GENERAL_TEXT . '</p><div id="tcdprogressbar"></div></div></div>' .
               $js;

      return $info;
    }

    function getTestConnectionResult() {
      $stripe_result = json_decode($this->sendTransactionToGateway('https://api.stripe.com/v1/charges/oscommerce_connection_test'), true);

      if ( is_array($stripe_result) && !empty($stripe_result) && isset($stripe_result['error']) ) {
        return 1;
      }

      return -1;
    }

    function format_raw($number, $currency_code = '', $currency_value = '') {
      global $currencies, $currency;

      if (empty($currency_code) || !$currencies->is_set($currency_code)) {
        $currency_code = $currency;
      }

      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
      }

      return number_format(tep_round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '', '');
    }

    function templateClassExists() {
      return class_exists('oscTemplate') && isset($GLOBALS['oscTemplate']) && is_object($GLOBALS['oscTemplate']) && (get_class($GLOBALS['oscTemplate']) == 'oscTemplate');
    }

    function getSubmitCardDetailsJavascript() {
      $stripe_publishable_key = MODULE_PAYMENT_STRIPE_PUBLISHABLE_KEY;

      $js = <<<EOD
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script type="text/javascript">
$(function() {
  Stripe.setPublishableKey('{$stripe_publishable_key}');

  $('form[name="checkout_confirmation"]').attr('id', 'payment-form');

  $('#payment-form').submit(function(event) {
    var \$form = $(this);

    if ( ($('#stripe_table').length < 1) || ($('form[name="checkout_confirmation"] input[name="stripe_card"]:radio:checked').val() == '0') ) {
      // Disable the submit button to prevent repeated clicks
      \$form.find('button').prop('disabled', true);

      try {
        Stripe.card.createToken(\$form, stripeResponseHandler);
      } catch ( error ) {
        \$form.find('.payment-errors').text(error);
      }

      // Prevent the form from submitting with the default action
      return false;
    }
  });

  var stripeResponseHandler = function(status, response) {
    var \$form = $('#payment-form');

    if (response.error) {
      // Show the errors on the form
      \$form.find('.payment-errors').text(response.error.message);
      \$form.find('button').prop('disabled', false);
    } else {
      // token contains id, last4, and card type
      var token = response.id;
      // Insert the token into the form so it gets submitted to the server
      \$form.append($('<input type="hidden" name="stripeToken" />').val(token));
      // and submit
      \$form.get(0).submit();
    }
  };

  if ( $('#stripe_table').length > 0 ) {
    if ( typeof($('#stripe_table').parent().closest('table').attr('width')) == 'undefined' ) {
      $('#stripe_table').parent().closest('table').attr('width', '100%');
    }

    $('#stripe_table .moduleRowExtra').hide();

    $('#stripe_table_new_card').hide();

    $('form[name="checkout_confirmation"] input[name="stripe_card"]').change(function() {
      var selected = $(this).val();

      if ( selected == '0' ) {
        stripeShowNewCardFields();
      } else {
        $('#stripe_table_new_card').hide();
      }

      $('tr[id^="stripe_card_"]').removeClass('moduleRowSelected');
      $('#stripe_card_' + selected).addClass('moduleRowSelected');
    });

    $('form[name="checkout_confirmation"] input[name="stripe_card"]:first').prop('checked', true).trigger('change');

    $('#stripe_table .moduleRow').hover(function() {
      $(this).addClass('moduleRowOver');
    }, function() {
      $(this).removeClass('moduleRowOver');
    }).click(function(event) {
      var target = $(event.target);

      if ( !target.is('input:radio') ) {
        $(this).find('input:radio').each(function() {
          if ( $(this).prop('checked') == false ) {
            $(this).prop('checked', true).trigger('change');
          }
        });
      }
    });
  } else {
    if ( typeof($('#stripe_table_new_card').parent().closest('table').attr('width')) == 'undefined' ) {
      $('#stripe_table_new_card').parent().closest('table').attr('width', '100%');
    }
  }
});

function stripeShowNewCardFields() {
  $('#stripe_table_new_card').show();
}
</script>
EOD;

      return $js;
    }

    function sendDebugEmail($response = array()) {
      global $HTTP_POST_VARS, $HTTP_GET_VARS;

      if (tep_not_null(MODULE_PAYMENT_STRIPE_DEBUG_EMAIL)) {
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
          tep_mail('', MODULE_PAYMENT_STRIPE_DEBUG_EMAIL, 'Stripe Debug E-Mail', trim($email_body), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
        }
      }
    }

    function getCustomerID() {
      global $customer_id;

      $token_check_query = tep_db_query("select stripe_token from customers_stripe_tokens where customers_id = '" . (int)$customer_id . "' limit 1");

      if ( tep_db_num_rows($token_check_query) === 1 ) {
        $token_check = tep_db_fetch_array($token_check_query);

        $stripe_token_array = explode(':|:', $token_check['stripe_token'], 2);

        return $stripe_token_array[0];
      }

      return false;
    }

    function createCustomer($token) {
      global $customer_id;

      $params = array('card' => $token);

      $result = json_decode($this->sendTransactionToGateway('https://api.stripe.com/v1/customers', $params), true);

      if ( is_array($result) && !empty($result) && isset($result['object']) && ($result['object'] == 'customer') ) {
        $token = tep_db_prepare_input($result['id'] . ':|:' . $result['cards']['data'][0]['id']);
        $type = tep_db_prepare_input($result['cards']['data'][0]['type']);
        $number = tep_db_prepare_input($result['cards']['data'][0]['last4']);
        $expiry = tep_db_prepare_input(str_pad($result['cards']['data'][0]['exp_month'], 2, '0', STR_PAD_LEFT) . $result['cards']['data'][0]['exp_year']);

        $sql_data_array = array('customers_id' => (int)$customer_id,
                                'stripe_token' => $token,
                                'card_type' => $type,
                                'number_filtered' => $number,
                                'expiry_date' => $expiry,
                                'date_added' => 'now()');

        tep_db_perform('customers_stripe_tokens', $sql_data_array);

        return array('id' => $result['id'],
                     'card_id' => $result['cards']['data'][0]['id']);
      }

      $this->sendDebugEmail($result);

      return false;
    }

    function addCard($token, $customer) {
      global $customer_id;

      $params = array('card' => $token);

      $result = json_decode($this->sendTransactionToGateway('https://api.stripe.com/v1/customers/' . $customer . '/cards', $params), true);

      if ( is_array($result) && !empty($result) && isset($result['object']) && ($result['object'] == 'card') ) {
        $token = tep_db_prepare_input($customer . ':|:' . $result['id']);
        $type = tep_db_prepare_input($result['type']);
        $number = tep_db_prepare_input($result['last4']);
        $expiry = tep_db_prepare_input(str_pad($result['exp_month'], 2, '0', STR_PAD_LEFT) . $result['exp_year']);

        $sql_data_array = array('customers_id' => (int)$customer_id,
                                'stripe_token' => $token,
                                'card_type' => $type,
                                'number_filtered' => $number,
                                'expiry_date' => $expiry,
                                'date_added' => 'now()');

        tep_db_perform('customers_stripe_tokens', $sql_data_array);

        return $result['id'];
      }

      $this->sendDebugEmail($result);

      return false;
    }

    function deleteCard($card, $customer, $token_id) {
      global $customer_id;

      $result = $this->sendTransactionToGateway('https://api.stripe.com/v1/customers/' . $customer . '/cards/' . $card, null, array(CURLOPT_CUSTOMREQUEST => 'DELETE'));

      if ( !is_array($result) || !isset($result['object']) || ($result['object'] != 'card') ) {
        $this->sendDebugEmail($result);
      }

      tep_db_query("delete from customers_stripe_tokens where id = '" . (int)$token_id . "' and customers_id = '" . (int)$customer_id . "' and stripe_token = '" . tep_db_prepare_input(tep_db_input($customer . ':|:' . $card)) . "'");

      return (tep_db_affected_rows() === 1);
    }
  }
?>
