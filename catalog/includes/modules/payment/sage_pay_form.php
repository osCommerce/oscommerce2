<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\Mail;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class sage_pay_form {
    var $code, $title, $description, $enabled;

    function __construct() {
      global $order;

      $this->signature = 'sage_pay|sage_pay_form|2.0|2.3';
      $this->api_version = '3.00';

      $this->code = 'sage_pay_form';
      $this->title = OSCOM::getDef('module_payment_sage_pay_form_text_title');
      $this->public_title = OSCOM::getDef('module_payment_sage_pay_form_text_public_title');
      $this->description = OSCOM::getDef('module_payment_sage_pay_form_text_description');
      $this->sort_order = defined('MODULE_PAYMENT_SAGE_PAY_FORM_SORT_ORDER') ? MODULE_PAYMENT_SAGE_PAY_FORM_SORT_ORDER : 0;
      $this->enabled = defined('MODULE_PAYMENT_SAGE_PAY_FORM_STATUS') && (MODULE_PAYMENT_SAGE_PAY_FORM_STATUS == 'True') ? true : false;
      $this->order_status = defined('MODULE_PAYMENT_SAGE_PAY_FORM_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_SAGE_PAY_FORM_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_SAGE_PAY_FORM_ORDER_STATUS_ID : 0;

      if ( defined('MODULE_PAYMENT_SAGE_PAY_FORM_STATUS') ) {
        if ( MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_SERVER == 'Test' ) {
          $this->title .= ' [Test]';
          $this->public_title .= ' (' . $this->code . '; Test)';
        }
      }

      if ( !function_exists('mcrypt_encrypt') ) {
        $this->description = '<div class="secWarning">' . OSCOM::getDef('module_payment_sage_pay_form_error_admin_mcrypt') . '</div>' . $this->description;

        $this->enabled = false;
      }

      if ( $this->enabled === true ) {
        if ( !tep_not_null(MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_LOGIN_NAME) || !tep_not_null(MODULE_PAYMENT_SAGE_PAY_FORM_ENCRYPTION_PASSWORD) ) {
          $this->description = '<div class="secWarning">' . OSCOM::getDef('module_payment_sage_pay_form_error_admin_configuration') . '</div>' . $this->description;

          $this->enabled = false;
        }
      }

      if ( $this->enabled === true ) {
        if ( isset($order) && is_object($order) ) {
          $this->update_status();
        }
      }

      if ( $this->enabled === true ) {
        if ( MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_SERVER == 'Live' ) {
          $this->form_action_url = 'https://live.sagepay.com/gateway/service/vspform-register.vsp';
        } else {
          $this->form_action_url = 'https://test.sagepay.com/gateway/service/vspform-register.vsp';
        }
      }
    }

    function update_status() {
      global $order;

      $OSCOM_Db = Registry::get('Db');

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_SAGE_PAY_FORM_ZONE > 0) ) {
        $check_flag = false;
        $Qcheck = $OSCOM_Db->get('zones_to_geo_zones', 'zone_id', ['geo_zone_id' => MODULE_PAYMENT_SAGE_PAY_FORM_ZONE, 'zone_country_id' => $order->billing['country']['id']], 'zone_id');
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
      global $order;

      $process_button_string = '';

      $params = array('VPSProtocol' => $this->api_version,
                      'Vendor' => substr(MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_LOGIN_NAME, 0, 15));

      if ( MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_METHOD == 'Payment' ) {
        $params['TxType'] = 'PAYMENT';
      } elseif ( MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_METHOD == 'Deferred' ) {
        $params['TxType'] = 'DEFERRED';
      } else {
        $params['TxType'] = 'AUTHENTICATE';
      }

      $crypt = array('ReferrerID' => 'C74D7B82-E9EB-4FBD-93DB-76F0F551C802',
                     'VendorTxCode' => substr(date('YmdHis') . '-' . $_SESSION['customer_id'] . '-' . $_SESSION['cartID'], 0, 40),
                     'Amount' => $this->format_raw($order->info['total']),
                     'Currency' => $_SESSION['currency'],
                     'Description' => substr(STORE_NAME, 0, 100),
                     'SuccessURL' => OSCOM::link('checkout_process.php'),
                     'FailureURL' => OSCOM::link('checkout_payment.php', 'payment_error=' . $this->code),
                     'CustomerName' => substr($order->billing['firstname'] . ' ' . $order->billing['lastname'], 0, 100),
                     'CustomerEMail' => substr($order->customer['email_address'], 0, 255),
                     'BillingSurname' => substr($order->billing['lastname'], 0, 20),
                     'BillingFirstnames' => substr($order->billing['firstname'], 0, 20),
                     'BillingAddress1' => substr($order->billing['street_address'], 0, 100),
                     'BillingCity' => substr($order->billing['city'], 0, 40),
                     'BillingPostCode' => substr($order->billing['postcode'], 0, 10),
                     'BillingCountry' => $order->billing['country']['iso_code_2']);

      if ($crypt['BillingCountry'] == 'US') {
        $crypt['BillingState'] = tep_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], '');
      }

      $crypt['BillingPhone'] = substr($order->customer['telephone'], 0, 20);
      $crypt['DeliverySurname'] = substr($order->delivery['lastname'], 0, 20);
      $crypt['DeliveryFirstnames'] = substr($order->delivery['firstname'], 0, 20);
      $crypt['DeliveryAddress1'] = substr($order->delivery['street_address'], 0, 100);
      $crypt['DeliveryCity'] = substr($order->delivery['city'], 0, 40);
      $crypt['DeliveryPostCode'] = substr($order->delivery['postcode'], 0, 10);
      $crypt['DeliveryCountry'] = $order->delivery['country']['iso_code_2'];

      if ($crypt['DeliveryCountry'] == 'US') {
        $crypt['DeliveryState'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], '');
      }

      if (tep_not_null(MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_EMAIL)) {
        $crypt['VendorEMail'] = substr(MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_EMAIL, 0, 255);
      }

      switch (MODULE_PAYMENT_SAGE_PAY_FORM_SEND_EMAIL) {
        case 'No One':
          $crypt['SendEMail'] = 0;
          break;

        case 'Customer and Vendor':
          $crypt['SendEMail'] = 1;
          break;

        case 'Vendor Only':
          $crypt['SendEMail'] = 2;
          break;
      }

      if (tep_not_null(MODULE_PAYMENT_SAGE_PAY_FORM_CUSTOMER_EMAIL_MESSAGE)) {
        $crypt['eMailMessage'] = substr(MODULE_PAYMENT_SAGE_PAY_FORM_CUSTOMER_EMAIL_MESSAGE, 0, 7500);
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

      foreach ($this->getOrderTotalsSummary() as $ot) {
        $contents[] = str_replace(array(':', "\n", "\r", '&'), '', strip_tags($ot['title'])) . ':---:---:---:---:' . $this->format_raw($ot['value']);
      }

      $crypt['Basket'] = substr(sizeof($contents) . ':' . implode(':', $contents), 0, 7500);
      $crypt['Apply3DSecure'] = '0';

      $crypt_string = '';

      foreach ($crypt as $key => $value) {
        $crypt_string .= $key . '=' . trim($value) . '&';
      }

      $crypt_string = substr($crypt_string, 0, -1);

      $params['Crypt'] = $this->encryptParams($crypt_string);

      foreach ($params as $key => $value) {
        $process_button_string .= HTML::hiddenField($key, $value);
      }

      return $process_button_string;
    }

    function before_process() {
      global $sage_pay_response;

      if (isset($_GET['crypt']) && tep_not_null($_GET['crypt'])) {
        $transaction_response = $this->decryptParams($_GET['crypt']);

        $string_array = explode('&', $transaction_response);
        $sage_pay_response = array('Status' => null);

        foreach ($string_array as $string) {
          if (strpos($string, '=') != false) {
            $parts = explode('=', $string, 2);
            $sage_pay_response[trim($parts[0])] = trim($parts[1]);
          }
        }

        if ( ($sage_pay_response['Status'] != 'OK') && ($sage_pay_response['Status'] != 'AUTHENTICATED') && ($sage_pay_response['Status'] != 'REGISTERED') ) {
          $this->sendDebugEmail($sage_pay_response);

          $error = $this->getErrorMessageNumber($sage_pay_response['StatusDetail']);

          OSCOM::redirect('checkout_payment.php', 'payment_error=' . $this->code . (tep_not_null($error) ? '&error=' . $error : ''));
        }
      } else {
        OSCOM::redirect('checkout_payment.php', 'payment_error=' . $this->code);
      }
    }

    function after_process() {
      global $insert_id, $sage_pay_response;

      $OSCOM_Db = Registry::get('Db');

      $result = array();

      if ( isset($sage_pay_response['VPSTxId']) ) {
        $result['ID'] = $sage_pay_response['VPSTxId'];
      }

      if ( isset($sage_pay_response['CardType']) ) {
        $result['Card'] = $sage_pay_response['CardType'];
      }

      if ( isset($sage_pay_response['AVSCV2']) ) {
        $result['AVS/CV2'] = $sage_pay_response['AVSCV2'];
      }

      if ( isset($sage_pay_response['AddressResult']) ) {
        $result['Address'] = $sage_pay_response['AddressResult'];
      }

      if ( isset($sage_pay_response['PostCodeResult']) ) {
        $result['Post Code'] = $sage_pay_response['PostCodeResult'];
      }

      if ( isset($sage_pay_response['CV2Result']) ) {
        $result['CV2'] = $sage_pay_response['CV2Result'];
      }

      if ( isset($sage_pay_response['3DSecureStatus']) ) {
        $result['3D Secure'] = $sage_pay_response['3DSecureStatus'];
      }

      if ( isset($sage_pay_response['PayerStatus']) ) {
        $result['PayPal Payer Status'] = $sage_pay_response['PayerStatus'];
      }

      if ( isset($sage_pay_response['AddressStatus']) ) {
        $result['PayPal Payer Address'] = $sage_pay_response['AddressStatus'];
      }

      $result_string = '';

      foreach ( $result as $k => $v ) {
        $result_string .= $k . ': ' . $v . "\n";
      }

      $sql_data_array = array('orders_id' => $insert_id,
                              'orders_status_id' => MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_ORDER_STATUS_ID,
                              'date_added' => 'now()',
                              'customer_notified' => '0',
                              'comments' => trim($result_string));

      $OSCOM_Db->save('orders_status_history', $sql_data_array);
    }

    function get_error() {
      $message = OSCOM::getDef('module_payment_sage_pay_form_error_general');

      $error_number = null;

      if ( isset($_GET['error']) && is_numeric($_GET['error']) && $this->errorMessageNumberExists($_GET['error']) ) {
        $error_number = $_GET['error'];
      } elseif (isset($_GET['crypt']) && tep_not_null($_GET['crypt'])) {
        $transaction_response = $this->decryptParams($_GET['crypt']);

        $string_array = explode('&', $transaction_response);
        $return = array('Status' => null);

        foreach ($string_array as $string) {
          if (strpos($string, '=') != false) {
            $parts = explode('=', $string, 2);
            $return[trim($parts[0])] = trim($parts[1]);
          }
        }

        $error = $this->getErrorMessageNumber($return['StatusDetail']);

        if ( is_numeric($error) && $this->errorMessageNumberExists($error) ) {
          $error_number = $error;
        }
      }

      if ( isset($error_number) ) {
// don't show an error message for user cancelled/aborted transactions
        if ( $error_number == '2013' ) {
          return false;
        }

        $message = $this->getErrorMessage($error_number) . ' ' . OSCOM::getDef('module_payment_sage_pay_form_error_general');
      }

      $error = array('title' => OSCOM::getDef('module_payment_sage_pay_form_error_title'),
                     'error' => $message);

      return $error;
    }

    function check() {
      return defined('MODULE_PAYMENT_SAGE_PAY_FORM_STATUS');
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

      if (!defined('MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_ORDER_STATUS_ID')) {
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
        $status_id = MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_ORDER_STATUS_ID;
      }

      $params = array('MODULE_PAYMENT_SAGE_PAY_FORM_STATUS' => array('title' => 'Enable Sage Pay Form Module',
                                                                     'desc' => 'Do you want to accept Sage Pay Form payments?',
                                                                     'value' => 'True',
                                                                     'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_LOGIN_NAME' => array('title' => 'Vendor Login Name',
                                                                                  'desc' => 'The vendor login name to connect to the gateway with.'),
                      'MODULE_PAYMENT_SAGE_PAY_FORM_ENCRYPTION_PASSWORD' => array('title' => 'Encryption Password',
                                                                                  'desc' => 'The encrpytion password to secure and verify transactions with.'),
                      'MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_METHOD' => array('title' => 'Transaction Method',
                                                                                 'desc' => 'The processing method to use for each transaction.',
                                                                                 'value' => 'Authenticate',
                                                                                 'set_func' => 'tep_cfg_select_option(array(\'Authenticate\', \'Deferred\', \'Payment\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_EMAIL' => array('title' => 'Vendor E-Mail Notification',
                                                                           'desc' => 'An e-mail address on which you can be contacted when a transaction completes. NOTE: If you wish to use multiple email addresses, you should add them using the colon character as a separator. e.g. me@mail1.com:me@mail2.com'),
                      'MODULE_PAYMENT_SAGE_PAY_FORM_SEND_EMAIL' => array('title' => 'Send E-Mail Notifications',
                                                                         'desc' => 'Who to send e-mails to.',
                                                                         'value' => 'Customer and Vendor',
                                                                         'set_func' => 'tep_cfg_select_option(array(\'No One\', \'Customer and Vendor\', \'Vendor Only\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_FORM_CUSTOMER_EMAIL_MESSAGE' => array('title' => 'Customer E-Mail Message',
                                                                                     'desc' => 'A message to the customer which is inserted into successful transaction e-mails only.',
                                                                                     'use_func' => 'sage_pay_form_clip_text',
                                                                                     'set_func' => 'sage_pay_form_textarea_field('),
                      'MODULE_PAYMENT_SAGE_PAY_FORM_ORDER_STATUS_ID' => array('title' => 'Set Order Status',
                                                                              'desc' => 'Set the status of orders made with this payment module to this value',
                                                                              'value' => '0',
                                                                              'use_func' => 'tep_get_order_status_name',
                                                                              'set_func' => 'tep_cfg_pull_down_order_statuses('),
                      'MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_ORDER_STATUS_ID' => array('title' => 'Transaction Order Status',
                                                                                          'desc' => 'Include transaction information in this order status level',
                                                                                          'value' => $status_id,
                                                                                          'set_func' => 'tep_cfg_pull_down_order_statuses(',
                                                                                          'use_func' => 'tep_get_order_status_name'),
                      'MODULE_PAYMENT_SAGE_PAY_FORM_ZONE' => array('title' => 'Payment Zone',
                                                                   'desc' => 'If a zone is selected, only enable this payment method for that zone.',
                                                                   'value' => '0',
                                                                   'use_func' => 'tep_get_zone_class_title',
                                                                   'set_func' => 'tep_cfg_pull_down_zone_classes('),
                      'MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_SERVER' => array('title' => 'Transaction Server',
                                                                                 'desc' => 'Perform transactions on the production server or on the testing server.',
                                                                                 'value' => 'Live',
                                                                                 'set_func' => 'tep_cfg_select_option(array(\'Live\', \'Test\'), '),
                      'MODULE_PAYMENT_SAGE_PAY_FORM_DEBUG_EMAIL' => array('title' => 'Debug E-Mail Address',
                                                                          'desc' => 'All parameters of an invalid transaction will be sent to this email address.'),
                      'MODULE_PAYMENT_SAGE_PAY_FORM_SORT_ORDER' => array('title' => 'Sort order of display.',
                                                                         'desc' => 'Sort order of display. Lowest is displayed first.',
                                                                         'value' => '0'));

      return $params;
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

    function getOrderTotalsSummary() {
      global $order_total_modules;

      $order_total_array = array();

      if (is_array($order_total_modules->modules)) {
        foreach ($order_total_modules->modules as $value) {
          $class = substr($value, 0, strrpos($value, '.'));
          if ($GLOBALS[$class]->enabled) {
            for ($i=0, $n=sizeof($GLOBALS[$class]->output); $i<$n; $i++) {
              if (tep_not_null($GLOBALS[$class]->output[$i]['title']) && tep_not_null($GLOBALS[$class]->output[$i]['text'])) {
                $order_total_array[] = array('code' => $GLOBALS[$class]->code,
                                             'title' => $GLOBALS[$class]->output[$i]['title'],
                                             'text' => $GLOBALS[$class]->output[$i]['text'],
                                             'value' => $GLOBALS[$class]->output[$i]['value'],
                                             'sort_order' => $GLOBALS[$class]->sort_order);
              }
            }
          }
        }
      }

      return $order_total_array;
    }

    function encryptParams($string) {
// pad pkcs5
      $blocksize = 16;

      $pad = $blocksize - (strlen($string) % $blocksize);

      $string .= str_repeat(chr($pad), $pad);

// encrypt
      return '@' . strtoupper(bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, MODULE_PAYMENT_SAGE_PAY_FORM_ENCRYPTION_PASSWORD, $string, MCRYPT_MODE_CBC, MODULE_PAYMENT_SAGE_PAY_FORM_ENCRYPTION_PASSWORD)));
	}

    function decryptParams($string) {
      if ( substr($string, 0, 1) == '@' ) {
        $string = substr($string, 1);
      }

      $string = pack('H*', $string);

      return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, MODULE_PAYMENT_SAGE_PAY_FORM_ENCRYPTION_PASSWORD, $string, MCRYPT_MODE_CBC, MODULE_PAYMENT_SAGE_PAY_FORM_ENCRYPTION_PASSWORD);
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

    function sendDebugEmail($response = array()) {
      if (tep_not_null(MODULE_PAYMENT_SAGE_PAY_FORM_DEBUG_EMAIL)) {
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
          $debugEmail = new Mail(MODULE_PAYMENT_SAGE_PAY_FORM_DEBUG_EMAIL, null, STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, 'Sage Pay Form Debug E-Mail');
          $debugEmail->setBody($email_body);
          $debugEmail->send();
        }
      }
    }
  }

  function sage_pay_form_clip_text($value) {
    if ( strlen($value) > 20 ) {
      $value = substr($value, 0, 20) . '..';
    }

    return $value;
  }

  function sage_pay_form_textarea_field($value = '', $key = '') {
    return HTML::textareaField('configuration[' . $key . ']', 60, 5, $value);
  }
?>
