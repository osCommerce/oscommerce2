<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Hash;
  use OSC\OM\HTML;
  use OSC\OM\HTTP;
  use OSC\OM\Mail;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class sage_pay_server {
    var $code, $title, $description, $enabled;

    function __construct() {
      global $PHP_SELF, $order;

      $this->signature = 'sage_pay|sage_pay_server|2.1|2.3';
      $this->api_version = '3.00';

      $this->code = 'sage_pay_server';
      $this->title = OSCOM::getDef('module_payment_sage_pay_server_text_title');
      $this->public_title = OSCOM::getDef('module_payment_sage_pay_server_text_public_title');
      $this->description = OSCOM::getDef('module_payment_sage_pay_server_text_description');
      $this->sort_order = defined('MODULE_PAYMENT_SAGE_PAY_SERVER_SORT_ORDER') ? MODULE_PAYMENT_SAGE_PAY_SERVER_SORT_ORDER : 0;
      $this->enabled = defined('MODULE_PAYMENT_SAGE_PAY_SERVER_STATUS') && (MODULE_PAYMENT_SAGE_PAY_SERVER_STATUS == 'True') ? true : false;
      $this->order_status = defined('MODULE_PAYMENT_SAGE_PAY_SERVER_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_SAGE_PAY_SERVER_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_SAGE_PAY_SERVER_ORDER_STATUS_ID : 0;

      if ( defined('MODULE_PAYMENT_SAGE_PAY_SERVER_STATUS') ) {
        if ( MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_SERVER == 'Test' ) {
          $this->title .= ' [Test]';
          $this->public_title .= ' (' . $this->code . '; Test)';
        }

        $this->description .= $this->getTestLinkInfo();
      }

      if ( !function_exists('curl_init') ) {
        $this->description = '<div class="secWarning">' . OSCOM::getDef('module_payment_sage_pay_server_error_admin_curl') . '</div>' . $this->description;

        $this->enabled = false;
      }

      if ( $this->enabled === true ) {
        if ( !tep_not_null(MODULE_PAYMENT_SAGE_PAY_SERVER_VENDOR_LOGIN_NAME) ) {
          $this->description = '<div class="secWarning">' . OSCOM::getDef('module_payment_sage_pay_server_error_admin_configuration') . '</div>' . $this->description;

          $this->enabled = false;
        }
      }

      if ( $this->enabled === true ) {
        if ( isset($order) && is_object($order) ) {
          $this->update_status();
        }
      }

      if ( defined('FILENAME_MODULES') && (basename($PHP_SELF) == 'modules.php') && isset($_GET['action']) && ($_GET['action'] == 'install') && isset($_GET['subaction']) && ($_GET['subaction'] == 'conntest') ) {
        echo $this->getTestConnectionResult();
        exit;
      }
    }

    function update_status() {
      global $order;

      $OSCOM_Db = Registry::get('Db');

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_SAGE_PAY_SERVER_ZONE > 0) ) {
        $check_flag = false;
        $Qcheck = $OSCOM_Db->get('zones_to_geo_zones', 'zone_id', ['geo_zone_id' => MODULE_PAYMENT_SAGE_PAY_SERVER_ZONE, 'zone_country_id' => $order->billing['country']['id']], 'zone_id');
        while ($Qcheck->fetch()) {
          if ($Qcheck->valueInt('zone_id') < 1) {
            $check_flag = true;
            break;
          } elseif ($Qcheck->valueInt('zone_id') == $order->billing['zone_id']) {
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
      return false;
    }

    function process_button() {
      return false;
    }

    function before_process() {
      global $sagepay_server_transaction_details, $order, $order_totals;

      $OSCOM_Db = Registry::get('Db');

      $sagepay_server_transaction_details = null;

      $error = null;

      if (isset($_GET['check']) && ($_GET['check'] == 'PROCESS')) {
        if ( isset($_GET['skcode']) && isset($_SESSION['sagepay_server_skey_code']) && ($_GET['skcode'] == $_SESSION['sagepay_server_skey_code']) ) {
          $skcode = HTML::sanitize($_GET['skcode']);

          $Qsp = $OSCOM_Db->get('sagepay_server_securitykeys', ['verified', 'transaction_details'], ['code' => $skcode], null, 1);

          if ($Qsp->fetch() !== false) {
            unset($_SESSION['sagepay_server_skey_code']);

            $OSCOM_Db->delete('sagepay_server_securitykeys', ['code' => $skcode]);

            if ( $Qsp->value('verified') == '1' ) {
              $sagepay_server_transaction_details = $Qsp->value('transaction_details');

              return true;
            }
          }
        }
      } else {
        if ( !isset($_SESSION['sagepay_server_skey_code']) ) {
          $_SESSION['sagepay_server_skey_code'] = Hash::getRandomString(16);
        }

        $params = array('VPSProtocol' => $this->api_version,
                        'ReferrerID' => 'C74D7B82-E9EB-4FBD-93DB-76F0F551C802',
                        'Vendor' => substr(MODULE_PAYMENT_SAGE_PAY_SERVER_VENDOR_LOGIN_NAME, 0, 15),
                        'VendorTxCode' => substr(date('YmdHis') . '-' . $_SESSION['customer_id'] . '-' . $_SESSION['cartID'], 0, 40),
                        'Amount' => $this->format_raw($order->info['total']),
                        'Currency' => $_SESSION['currency'],
                        'Description' => substr(STORE_NAME, 0, 100),
                        'NotificationURL' => $this->formatURL(OSCOM::link('ext/modules/payment/sage_pay/server.php', 'check=SERVER&skcode=' . $_SESSION['sagepay_server_skey_code'], false)),
                        'BillingSurname' => substr($order->billing['lastname'], 0, 20),
                        'BillingFirstnames' => substr($order->billing['firstname'], 0, 20),
                        'BillingAddress1' => substr($order->billing['street_address'], 0, 100),
                        'BillingCity' => substr($order->billing['city'], 0, 40),
                        'BillingPostCode' => substr($order->billing['postcode'], 0, 10),
                        'BillingCountry' => $order->billing['country']['iso_code_2'],
                        'BillingPhone' => substr($order->customer['telephone'], 0, 20),
                        'DeliverySurname' => substr($order->delivery['lastname'], 0, 20),
                        'DeliveryFirstnames' => substr($order->delivery['firstname'], 0, 20),
                        'DeliveryAddress1' => substr($order->delivery['street_address'], 0, 100),
                        'DeliveryCity' => substr($order->delivery['city'], 0, 40),
                        'DeliveryPostCode' => substr($order->delivery['postcode'], 0, 10),
                        'DeliveryCountry' => $order->delivery['country']['iso_code_2'],
                        'DeliveryPhone' => substr($order->customer['telephone'], 0, 20),
                        'CustomerEMail' => substr($order->customer['email_address'], 0, 255),
                        'Apply3DSecure' => '0');

        $ip_address = HTTP::getIpAddress();

        if ( (ip2long($ip_address) != -1) && (ip2long($ip_address) != false) ) {
          $params['ClientIPAddress']= $ip_address;
        }

        if ( MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_METHOD == 'Payment' ) {
          $params['TxType'] = 'PAYMENT';
        } elseif ( MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_METHOD == 'Deferred' ) {
          $params['TxType'] = 'DEFERRED';
        } else {
          $params['TxType'] = 'AUTHENTICATE';
        }

        if ($params['BillingCountry'] == 'US') {
          $params['BillingState'] = tep_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], '');
        }

        if ($params['DeliveryCountry'] == 'US') {
          $params['DeliveryState'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], '');
        }

        if ( MODULE_PAYMENT_SAGE_PAY_SERVER_PROFILE_PAGE != 'Normal' ) {
          $params['Profile'] = 'LOW';
        }

        $contents = array();

        foreach ($order->products as $product) {
          $product_name = $product['name'];

          if (isset($product['attributes'])) {
            foreach ($product['attributes'] as $att) {
              $product_name .= '; ' . $att['option'] . '=' . $att['value'];
            }
          }

          $contents[] = str_replace(array(':', "\n", "\r", '&'), '', $product_name) . ':' . $product['qty'] . ':' . $this->format_raw($product['final_price']) . ':' . $this->format_raw(($product['tax'] / 100) * $product['final_price']) . ':' . $this->format_raw((($product['tax'] / 100) * $product['final_price']) + $product['final_price']) . ':' . $this->format_raw(((($product['tax'] / 100) * $product['final_price']) + $product['final_price']) * $product['qty']);
        }

        foreach ($order_totals as $ot) {
          $contents[] = str_replace(array(':', "\n", "\r", '&'), '', strip_tags($ot['title'])) . ':---:---:---:---:' . $this->format_raw($ot['value']);
        }

        $params['Basket'] = substr(sizeof($contents) . ':' . implode(':', $contents), 0, 7500);

        $post_string = '';

        foreach ($params as $key => $value) {
          $post_string .= $key . '=' . urlencode(trim($value)) . '&';
        }

        if ( MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_SERVER == 'Live' ) {
          $gateway_url = 'https://live.sagepay.com/gateway/service/vspserver-register.vsp';
        } else {
          $gateway_url = 'https://test.sagepay.com/gateway/service/vspserver-register.vsp';
        }

        $transaction_response = $this->sendTransactionToGateway($gateway_url, $post_string);

        $string_array = explode(chr(10), $transaction_response);
        $return = array();

        foreach ($string_array as $string) {
          if (strpos($string, '=') != false) {
            $parts = explode('=', $string, 2);
            $return[trim($parts[0])] = trim($parts[1]);
          }
        }

        if ($return['Status'] == 'OK') {
          $Qsp = $OSCOM_Db->get('sagepay_server_securitykeys', ['id', 'securitykey'], ['code' => $_SESSION['sagepay_server_skey_code']], null, 1);

          if ($Qsp->fetch() !== false) {
            if ( $Qsp->value('securitykey') != $return['SecurityKey'] ) {
              $OSCOM_Db->save('sagepay_server_securitykeys', ['securitykey' => $return['SecurityKey'], 'date_added' => 'now()'], ['id' => $Qsp->valueInt('id')]);
            }
          } else {
            $OSCOM_Db->save('sagepay_server_securitykeys', [
              'code' => $_SESSION['sagepay_server_skey_code'],
              'securitykey' => $return['SecurityKey'],
              'date_added' => 'now()'
            ]);
          }

          if ( MODULE_PAYMENT_SAGE_PAY_SERVER_PROFILE_PAGE == 'Normal' ) {
            HTTP::redirect($return['NextURL']);
          } else {
            $_SESSION['sage_pay_server_nexturl'] = $return['NextURL'];

            OSCOM::redirect('ext/modules/payment/sage_pay/checkout.php');
          }
        } else {
          $error = $this->getErrorMessageNumber($return['StatusDetail']);

          $this->sendDebugEmail($return);
        }
      }

      OSCOM::redirect('checkout_payment.php', 'payment_error=' . $this->code . (tep_not_null($error) ? '&error=' . $error : ''));
    }

    function after_process() {
      global $insert_id, $sagepay_server_transaction_details;

      $OSCOM_Db = Registry::get('Db');

      $sql_data_array = array('orders_id' => $insert_id,
                              'orders_status_id' => DEFAULT_ORDERS_STATUS_ID,
                              'date_added' => 'now()',
                              'customer_notified' => '0',
                              'comments' => trim($sagepay_server_transaction_details));

      $OSCOM_Db->save('orders_status_history', $sql_data_array);

      if ( MODULE_PAYMENT_SAGE_PAY_SERVER_PROFILE_PAGE == 'Low' ) {
        $_SESSION['cart']->reset(true);

// unregister session variables used during checkout
        unset($_SESSION['sendto']);
        unset($_SESSION['billto']);
        unset($_SESSION['shipping']);
        unset($_SESSION['payment']);
        unset($_SESSION['comments']);

        unset($_SESSION['sage_pay_server_nexturl']);

        OSCOM::redirect('ext/modules/payment/sage_pay/redirect.php');
      }
    }

    function get_error() {
      $message = OSCOM::getDef('module_payment_sage_pay_server_error_general');

      $error_number = null;

      if ( isset($_GET['error']) && is_numeric($_GET['error']) && $this->errorMessageNumberExists($_GET['error']) ) {
        $error_number = $_GET['error'];
      }

      if ( isset($error_number) ) {
// don't show an error message for user cancelled/aborted transactions
        if ( $error_number == '2013' ) {
          return false;
        }

        $message = $this->getErrorMessage($error_number) . ' ' . OSCOM::getDef('module_payment_sage_pay_server_error_general');
      }

      $error = array('title' => OSCOM::getDef('module_payment_sage_pay_server_error_title'),
                     'error' => $message);

      return $error;
    }

    function check() {
      return defined('MODULE_PAYMENT_SAGE_PAY_SERVER_STATUS');
    }

    function install($parameter = null) {
      $OSCOM_Db = Registry::get('Db');

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

        $OSCOM_Db->save('configuration', $sql_data_array);
      }
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
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
      $OSCOM_Db = Registry::get('Db');

      $Qcheck = $OSCOM_Db->query('show tables like "sagepay_server_securitykeys"');

      if ($Qcheck->fetch() === false) {
        $sql = <<<EOD
CREATE TABLE sagepay_server_securitykeys (
  id int NOT NULL auto_increment,
  code char(16) NOT NULL,
  securitykey char(10) NOT NULL,
  date_added datetime NOT NULL,
  verified char(1) DEFAULT 0,
  transaction_details text,
  PRIMARY KEY (id),
  KEY idx_sagepay_server_securitykeys_code (code),
  KEY idx_sagepay_server_securitykeys_securitykey (securitykey)
);
EOD;

        $OSCOM_Db->exec($sql);
      }

      if (!defined('MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_ORDER_STATUS_ID')) {
        $Qcheck = $OSCOM_Db->get('orders_status', 'orders_status_id', ['orders_status_name' => 'Sage Pay [Transactions]'], null, 1);

        if ($Qcheck->fetch() === false) {
          $Qstatus = $OSCOM_Db->get('orders_status', 'max(orders_status_id) as status_id');

          $status_id = $Qstatus->valueInt('status_id') + 1;

          $languages = tep_get_languages();

          foreach ($languages as $lang) {
            $OSCOM_Db->save('orders_status', [
              'orders_status_id' => $status_id,
              'language_id' => $lang['id'],
              'orders_status_name' => 'Sage Pay [Transactions]',
              'public_flag' => 0,
              'downloads_flag' => 0
            ]);
          }
        } else {
          $status_id = $Qcheck->valueInt('orders_status_id');
        }
      } else {
        $status_id = MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_ORDER_STATUS_ID;
      }

      $params = array('MODULE_PAYMENT_SAGE_PAY_SERVER_STATUS' => array('title' => 'Enable Sage Pay Server Module',
                                                                       'desc' => 'Do you want to accept Sage Pay Server payments?',
                                                                       'value' => 'True',
                                                                       'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_SERVER_VENDOR_LOGIN_NAME' => array('title' => 'Vendor Login Name',
                                                                                  'desc' => 'The vendor login name to connect to the gateway with.'),
                      'MODULE_PAYMENT_SAGE_PAY_SERVER_PROFILE_PAGE' => array('title' => 'Profile Payment Page',
                                                                             'desc' => 'Profile page to use for the payment page, Normal is a full redirect to Sage Pay and Low loads through an iframe.',
                                                                             'value' => 'Normal',
                                                                             'set_func' => 'tep_cfg_select_option(array(\'Normal\', \'Low\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_METHOD' => array('title' => 'Transaction Method',
                                                                                   'desc' => 'The processing method to use for each transaction.',
                                                                                   'value' => 'Authenticate',
                                                                                   'set_func' => 'tep_cfg_select_option(array(\'Authenticate\', \'Deferred\', \'Payment\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_SERVER_ORDER_STATUS_ID' => array('title' => 'Set Order Status',
                                                                                'desc' => 'Set the status of orders made with this payment module to this value',
                                                                                'value' => '0',
                                                                                'use_func' => 'tep_get_order_status_name',
                                                                                'set_func' => 'tep_cfg_pull_down_order_statuses('),
                      'MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_ORDER_STATUS_ID' => array('title' => 'Transaction Order Status',
                                                                                            'desc' => 'Include transaction information in this order status level',
                                                                                            'value' => $status_id,
                                                                                            'set_func' => 'tep_cfg_pull_down_order_statuses(',
                                                                                            'use_func' => 'tep_get_order_status_name'),
                      'MODULE_PAYMENT_SAGE_PAY_SERVER_ZONE' => array('title' => 'Payment Zone',
                                                                     'desc' => 'If a zone is selected, only enable this payment method for that zone.',
                                                                     'value' => '0',
                                                                     'use_func' => 'tep_get_zone_class_title',
                                                                     'set_func' => 'tep_cfg_pull_down_zone_classes('),
                      'MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_SERVER' => array('title' => 'Transaction Server',
                                                                                   'desc' => 'Perform transactions on the production server or on the testing server.',
                                                                                   'value' => 'Live',
                                                                                   'set_func' => 'tep_cfg_select_option(array(\'Live\', \'Test\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_SERVER_VERIFY_SSL' => array('title' => 'Verify SSL Certificate',
                                                                           'desc' => 'Verify transaction server SSL certificate on connection?',
                                                                           'value' => 'True',
                                                                           'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_SERVER_PROXY' => array('title' => 'Proxy Server',
                                                                      'desc' => 'Send API requests through this proxy server. (host:port, eg: 123.45.67.89:8080 or proxy.example.com:8080)'),
                      'MODULE_PAYMENT_SAGE_PAY_SERVER_DEBUG_EMAIL' => array('title' => 'Debug E-Mail Address',
                                                                            'desc' => 'All parameters of an invalid transaction will be sent to this email address.'),
                      'MODULE_PAYMENT_SAGE_PAY_SERVER_SORT_ORDER' => array('title' => 'Sort order of display.',
                                                                           'desc' => 'Sort order of display. Lowest is displayed first.',
                                                                           'value' => '0'));

      return $params;
    }

    function sendTransactionToGateway($url, $parameters) {
      $server = parse_url($url);

      if (isset($server['port']) === false) {
        $server['port'] = ($server['scheme'] == 'https') ? 443 : 80;
      }

      if (isset($server['path']) === false) {
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

      if ( MODULE_PAYMENT_SAGE_PAY_SERVER_VERIFY_SSL == 'True' ) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

        if ( is_file(OSCOM::getConfig('dir_root', 'Shop') . 'ext/modules/payment/sage_pay/sagepay.com.crt') ) {
          curl_setopt($curl, CURLOPT_CAINFO, OSCOM::getConfig('dir_root', 'Shop') . 'ext/modules/payment/sage_pay/sagepay.com.crt');
        } elseif ( is_file(OSCOM::getConfig('dir_root', 'Shop') . 'includes/cacert.pem') ) {
          curl_setopt($curl, CURLOPT_CAINFO, OSCOM::getConfig('dir_root', 'Shop') . 'includes/cacert.pem');
        }
      } else {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      }

      if ( tep_not_null(MODULE_PAYMENT_SAGE_PAY_SERVER_PROXY) ) {
        curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($curl, CURLOPT_PROXY, MODULE_PAYMENT_SAGE_PAY_SERVER_PROXY);
      }

      $result = curl_exec($curl);

      curl_close($curl);

      return $result;
    }

// format prices without currency formatting
    function format_raw($number, $currency_code = '', $currency_value = '') {
      global $currencies;

      if (empty($currency_code) || !$currencies->is_set($currency_code)) {
        $currency_code = $_SESSION['currency'];
      }

      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
      }

      return number_format(tep_round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }

    function loadErrorMessages() {
      $errors = array();

      if (is_file(dirname(__FILE__) . '/../../../ext/modules/payment/sage_pay/errors.php')) {
        include(dirname(__FILE__) . '/../../../ext/modules/payment/sage_pay/errors.php');
      }

      $this->_error_messages = $errors;
    }

    function getErrorMessageNumber($string) {
      if (!isset($this->_error_messages)) {
        $this->loadErrorMessages();
      }

      $error = explode(' ', $string, 2);

      if (is_numeric($error[0]) && $this->errorMessageNumberExists($error[0])) {
        return $error[0];
      }

      return false;
    }

    function getErrorMessage($number) {
      if (!isset($this->_error_messages)) {
        $this->loadErrorMessages();
      }

      if (is_numeric($number) && $this->errorMessageNumberExists($number)) {
        return $this->_error_messages[$number];
      }

      return false;
    }

    function errorMessageNumberExists($number) {
      if (!isset($this->_error_messages)) {
        $this->loadErrorMessages();
      }

      return (is_numeric($number) && isset($this->_error_messages[$number]));
    }

    function formatURL($url) {
      return str_replace('&amp;', '&', $url);
    }

    function getTestLinkInfo() {
      $dialog_title = OSCOM::getDef('module_payment_sage_pay_server_dialog_connection_title');
      $dialog_button_close = OSCOM::getDef('module_payment_sage_pay_server_dialog_connection_button_close');
      $dialog_success = OSCOM::getDef('module_payment_sage_pay_server_dialog_connection_success');
      $dialog_failed = OSCOM::getDef('module_payment_sage_pay_server_dialog_connection_failed');
      $dialog_error = OSCOM::getDef('module_payment_sage_pay_server_dialog_connection_error');
      $dialog_connection_time = OSCOM::getDef('module_payment_sage_pay_server_dialog_connection_time');

      $test_url = OSCOM::link('modules.php', 'set=payment&module=' . $this->code . '&action=install&subaction=conntest');

      $js = <<<EOD
<script>
if ( typeof jQuery == 'undefined' ) {
  document.write('<scr' + 'ipt src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></scr' + 'ipt>');
  document.write('<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/redmond/jquery-ui.css" />');
  document.write('<scr' + 'ipt src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></scr' + 'ipt>');
}
</script>

<script>
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

      $info = '<p><img src="images/icons/locked.gif" border="0">&nbsp;<a href="javascript:openTestConnectionDialog();" style="text-decoration: underline; font-weight: bold;">' . OSCOM::getDef('module_payment_sage_pay_server_dialog_connection_link_title') . '</a></p>' .
              '<div id="testConnectionDialog" style="display: none;"><p>';

      if ( MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_SERVER == 'Live' ) {
        $info .= 'Live Server:<br />https://live.sagepay.com/gateway/service/vspserver-register.vsp';
      } else {
        $info .= 'Test Server:<br />https://test.sagepay.com/gateway/service/vspserver-register.vsp';
      }

      $info .= '</p><div id="testConnectionDialogProgress"><p>' . OSCOM::getDef('module_payment_sage_pay_server_dialog_connection_general_text') . '</p><div id="tcdprogressbar"></div></div></div>' .
               $js;

      return $info;
    }

    function getTestConnectionResult() {
      if ( MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_SERVER == 'Live' ) {
        $gateway_url = 'https://live.sagepay.com/gateway/service/vspserver-register.vsp';
      } else {
        $gateway_url = 'https://test.sagepay.com/gateway/service/vspserver-register.vsp';
      }

      $params = array('VPSProtocol' => $this->api_version,
                      'ReferrerID' => 'C74D7B82-E9EB-4FBD-93DB-76F0F551C802',
                      'Vendor' => substr(MODULE_PAYMENT_SAGE_PAY_DIRECT_VENDOR_LOGIN_NAME, 0, 15),
                      'Amount' => 0,
                      'Currency' => DEFAULT_CURRENCY);

      $ip_address = HTTP::getIpAddress();

      if ( !empty($ip_address) && (ip2long($ip_address) != -1) && (ip2long($ip_address) != false) ) {
        $params['ClientIPAddress']= $ip_address;
      }

      $post_string = '';

      foreach ($params as $key => $value) {
        $post_string .= $key . '=' . urlencode(trim($value)) . '&';
      }

      $response = $this->sendTransactionToGateway($gateway_url, $post_string);

      if ( $response != false ) {
        return 1;
      }

      return -1;
    }

    function sendDebugEmail($response = array()) {
      if (tep_not_null(MODULE_PAYMENT_SAGE_PAY_SERVER_DEBUG_EMAIL)) {
        $email_body = '';

        if (!empty($response)) {
          $email_body .= 'RESPONSE:' . "\n\n" . print_r($response, true) . "\n\n";
        }

        if (!empty($_POST)) {
          $email_body .= '$_POST:' . "\n\n" . print_r($_POST, true) . "\n\n";
        }

        if (!empty($_GET)) {
          $email_body .= '$_GET:' . "\n\n" . print_r($_GET, true) . "\n\n";
        }

        if (!empty($email_body)) {
          $debugEmail = new Mail(MODULE_PAYMENT_SAGE_PAY_SERVER_DEBUG_EMAIL, null, STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, 'Sage Pay Server Debug E-Mail');
          $debugEmail->setBody($email_body);
          $debugEmail->send();
        }
      }
    }
  }
?>
