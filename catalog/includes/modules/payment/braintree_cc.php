<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class braintree_cc {
    var $code, $title, $description, $enabled;

    function braintree_cc() {
      global $order;

      $this->signature = 'braintree|braintree_cc|1.1|2.3';
      $this->api_version = '1';

      $this->code = 'braintree_cc';
      $this->title = MODULE_PAYMENT_BRAINTREE_CC_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_BRAINTREE_CC_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_BRAINTREE_CC_TEXT_DESCRIPTION;
      $this->sort_order = defined('MODULE_PAYMENT_BRAINTREE_CC_SORT_ORDER') ? MODULE_PAYMENT_BRAINTREE_CC_SORT_ORDER : 0;
      $this->enabled = defined('MODULE_PAYMENT_BRAINTREE_CC_STATUS') && (MODULE_PAYMENT_BRAINTREE_CC_STATUS == 'True') ? true : false;
      $this->order_status = defined('MODULE_PAYMENT_BRAINTREE_CC_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_BRAINTREE_CC_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_BRAINTREE_CC_ORDER_STATUS_ID : 0;

      if ( defined('MODULE_PAYMENT_BRAINTREE_CC_STATUS') ) {
        if ( MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_SERVER == 'Sandbox' ) {
          $this->title .= ' [Sandbox]';
          $this->public_title .= ' (' . $this->code . '; Sandbox)';
        }
      }

      $braintree_error = null;

      if ( !isset($braintree_error) ) {
        $requiredExtensions = array('xmlwriter', 'SimpleXML', 'openssl', 'dom', 'hash', 'curl');

        $exts = array();

        foreach ( $requiredExtensions as $ext ) {
          if ( !extension_loaded($ext) ) {
            $exts[] = $ext;
          }
        }

        if ( !empty($exts) ) {
          $braintree_error = sprintf(MODULE_PAYMENT_BRAINTREE_CC_ERROR_ADMIN_PHP_EXTENSIONS, implode('<br />', $exts));
        }
      }

      if ( !isset($braintree_error) && defined('MODULE_PAYMENT_BRAINTREE_CC_STATUS') ) {
        if ( !tep_not_null(MODULE_PAYMENT_BRAINTREE_CC_MERCHANT_ID) || !tep_not_null(MODULE_PAYMENT_BRAINTREE_CC_PUBLIC_KEY) || !tep_not_null(MODULE_PAYMENT_BRAINTREE_CC_PRIVATE_KEY) || !tep_not_null(MODULE_PAYMENT_BRAINTREE_CC_CLIENT_KEY) ) {
          $braintree_error = MODULE_PAYMENT_BRAINTREE_CC_ERROR_ADMIN_CONFIGURATION;
        }
      }

      if ( !isset($braintree_error) && defined('MODULE_PAYMENT_BRAINTREE_CC_STATUS') ) {
        $ma_error = true;

        if ( tep_not_null(MODULE_PAYMENT_BRAINTREE_CC_MERCHANT_ACCOUNTS) ) {
          $mas = explode(';', MODULE_PAYMENT_BRAINTREE_CC_MERCHANT_ACCOUNTS);

          foreach ( $mas as $a ) {
            $ac = explode(':', $a, 2);

            if ( isset($ac[1]) && ($ac[1] == DEFAULT_CURRENCY) ) {
              $ma_error = false;
              break;
            }
          }
        }

        if ( $ma_error === true ) {
          $braintree_error = sprintf(MODULE_PAYMENT_BRAINTREE_CC_ERROR_ADMIN_MERCHANT_ACCOUNTS, DEFAULT_CURRENCY);
        }
      }

      if ( !isset($braintree_error) ) {
        if ( !class_exists('Braintree') ) {
          include('braintree_cc/Braintree.php');
        }

        spl_autoload_register('tep_braintree_autoloader');

        $this->api_version .= ' [' . Braintree_Version::get() . ']';
      } else {
        $this->description = '<div class="secWarning">' . $braintree_error . '</div>' . $this->description;

        $this->enabled = false;
      }

      if ( isset($order) && is_object($order) ) {
        $this->update_status();
      }
    }

    function update_status() {
      global $order;

      $OSCOM_Db = Registry::get('Db');

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_BRAINTREE_CC_ZONE > 0) ) {
        $check_flag = false;
        $Qcheck = $OSCOM_Db->get('zones_to_geo_zones', 'zone_id', ['geo_zone_id' => MODULE_PAYMENT_BRAINTREE_CC_ZONE, 'zone_country_id' => $order->billing['country']['id']], 'zone_id');
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
      if ( $this->templateClassExists() ) {
        $GLOBALS['oscTemplate']->addBlock($this->getSubmitCardDetailsJavascript(), 'header_tags');
      }
    }

    function confirmation() {
      global $order, $currencies;

      $OSCOM_Db = Registry::get('Db');

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

      $content = '';

      if ( !$this->isValidCurrency($_SESSION['currency']) ) {
        $content .= sprintf(MODULE_PAYMENT_BRAINTREE_CC_CURRENCY_CHARGE, $currencies->format($order->info['total'], true, DEFAULT_CURRENCY), DEFAULT_CURRENCY, $_SESSION['currency']);
      }

      if ( MODULE_PAYMENT_BRAINTREE_CC_TOKENS == 'True' ) {
        $Qtokens = $OSCOM_Db->get('customers_braintree_tokens', ['id', 'card_type', 'number_filtered', 'expiry_date'], ['customers_id' => $_SESSION['customer_id']], 'date_added');

        if ($Qtokens->fetch() !== false) {
          $content .= '<table id="braintree_table" border="0" width="100%" cellspacing="0" cellpadding="2">';

          do {
            $content .= '<tr class="moduleRow" id="braintree_card_' . $Qtokens->valueInt('id') . '">' .
                        '  <td width="40" valign="top"><input type="radio" name="braintree_card" value="' . $Qtokens->valueInt('id') . '" /></td>' .
                        '  <td valign="top">' . MODULE_PAYMENT_BRAINTREE_CC_CREDITCARD_LAST_4 . '&nbsp;' . $Qtokens->valueProtected('number_filtered') . '&nbsp;&nbsp;' . tep_output_string_protected(substr($Qtokens->value('expiry_date'), 0, 2) . '/' . substr($Qtokens->value('expiry_date'), 2)) . '&nbsp;&nbsp;' . $Qtokens->valueProtected('card_type') . '</td>' .
                        '</tr>';

            if ( MODULE_PAYMENT_BRAINTREE_CC_VERIFY_WITH_CVV == 'True' ) {
              $content .= '<tr class="moduleRowExtra" id="braintree_card_cvv_' . $Qtokens->valueInt('id') . '">' .
                          '  <td width="40" valign="top">&nbsp;</td>' .
                          '  <td valign="top">' . MODULE_PAYMENT_BRAINTREE_CC_CREDITCARD_CVV . '&nbsp;<input type="text" size="5" maxlength="4" autocomplete="off" data-encrypted-name="token_cvv[' . $Qtokens->valueInt('id') . ']" /></td>' .
                          '</tr>';
            }
          } while ($Qtokens->fetch());

          $content .= '<tr class="moduleRow" id="braintree_card_0">' .
                      '  <td width="40" valign="top"><input type="radio" name="braintree_card" value="0" /></td>' .
                      '  <td valign="top">' . MODULE_PAYMENT_BRAINTREE_CC_CREDITCARD_NEW . '</td>' .
                      '</tr>' .
                      '</table>';
        }
      }

      $content .= '<table id="braintree_table_new_card" border="0" width="100%" cellspacing="0" cellpadding="2">' .
                  '<tr>' .
                  '  <td width="30%">' . MODULE_PAYMENT_BRAINTREE_CC_CREDITCARD_OWNER . '</td>' .
                  '  <td>' . HTML::inputField('name', $order->billing['firstname'] . ' ' . $order->billing['lastname']) . '</td>' .
                  '</tr>' .
                  '<tr>' .
                  '  <td width="30%">' . MODULE_PAYMENT_BRAINTREE_CC_CREDITCARD_NUMBER . '</td>' .
                  '  <td><input type="text" maxlength="20" autocomplete="off" data-encrypted-name="number" /></td>' .
                  '</tr>' .
                  '<tr>' .
                  '  <td width="30%">' . MODULE_PAYMENT_BRAINTREE_CC_CREDITCARD_EXPIRY . '</td>' .
                  '  <td>' . tep_draw_pull_down_menu('month', $months_array) . ' / ' . tep_draw_pull_down_menu('year', $years_array) . '</td>' .
                  '</tr>';

      if ( MODULE_PAYMENT_BRAINTREE_CC_VERIFY_WITH_CVV == 'True' ) {
        $content .= '<tr>' .
                    '  <td width="30%">' . MODULE_PAYMENT_BRAINTREE_CC_CREDITCARD_CVV . '</td>' .
                    '  <td><input type="text" size="5" maxlength="4" autocomplete="off" data-encrypted-name="cvv" /></td>' .
                    '</tr>';
      }

      if ( MODULE_PAYMENT_BRAINTREE_CC_TOKENS == 'True' ) {
        $content .= '<tr>' .
                    '  <td width="30%">&nbsp;</td>' .
                    '  <td>' . tep_draw_checkbox_field('cc_save', 'true') . ' ' . MODULE_PAYMENT_BRAINTREE_CC_CREDITCARD_SAVE . '</td>' .
                    '</tr>';
      }

      $content .= '</table>';

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
      global $order, $braintree_result, $braintree_token;

      $OSCOM_Db = Registry::get('Db');

      $braintree_token = null;
      $braintree_token_cvv = null;
      $braintree_error = null;

      if ( MODULE_PAYMENT_BRAINTREE_CC_TOKENS == 'True' ) {
        if ( isset($_POST['braintree_card']) && is_numeric($_POST['braintree_card']) && ($_POST['braintree_card'] > 0) ) {
          $Qtoken = $OSCOM_Db->get('customers_braintree_tokens', 'braintree_token', ['id' => (int)$_POST['braintree_card'], 'customers_id' => $_SESSION['customer_id']]);

          if ($Qtoken->fetch() !== false) {
            $braintree_token = $Qtoken->value('braintree_token');

            if ( MODULE_PAYMENT_BRAINTREE_CC_VERIFY_WITH_CVV == 'True' ) {
              if ( isset($_POST['token_cvv']) && is_array($_POST['token_cvv']) && isset($_POST['token_cvv'][$_POST['braintree_card']]) ) {
                $braintree_token_cvv = $_POST['token_cvv'][$_POST['braintree_card']];
              }

              if ( !isset($braintree_token_cvv) || empty($braintree_token_cvv) ) {
                tep_redirect(OSCOM::link('checkout_payment.php', 'payment_error=' . $this->code . '&error=cardcvv', 'SSL'));
              }
            }
          }
        }
      }

      if ( !isset($braintree_token) ) {
        $cc_owner = isset($_POST['name']) ? $_POST['name'] : null;
        $cc_number = isset($_POST['number']) ? $_POST['number'] : null;
        $cc_expires_month = isset($_POST['month']) ? $_POST['month'] : null;
        $cc_expires_year = isset($_POST['year']) ? $_POST['year'] : null;

        if ( MODULE_PAYMENT_BRAINTREE_CC_VERIFY_WITH_CVV == 'True' ) {
          $cc_cvv = isset($_POST['cvv']) ? $_POST['cvv'] : null;
        }

        $months_array = array();

        for ($i=1; $i<13; $i++) {
          $months_array[] = sprintf('%02d', $i);
        }

        $today = getdate();
        $years_array = array();

        for ($i=$today['year']; $i < $today['year']+10; $i++) {
          $years_array[] = strftime('%Y',mktime(0,0,0,1,1,$i));
        }

        if ( !isset($cc_owner) || empty($cc_owner) ) {
          tep_redirect(OSCOM::link('checkout_payment.php', 'payment_error=' . $this->code . '&error=cardowner', 'SSL'));
        }

        if ( !isset($cc_number) || empty($cc_number) ) {
          tep_redirect(OSCOM::link('checkout_payment.php', 'payment_error=' . $this->code . '&error=cardnumber', 'SSL'));
        }

        if ( !isset($cc_expires_month) || !in_array($cc_expires_month, $months_array) ) {
          tep_redirect(OSCOM::link('checkout_payment.php', 'payment_error=' . $this->code . '&error=cardexpires', 'SSL'));
        }

        if ( !isset($cc_expires_year) || !in_array($cc_expires_year, $years_array) ) {
          tep_redirect(OSCOM::link('checkout_payment.php', 'payment_error=' . $this->code . '&error=cardexpires', 'SSL'));
        }

        if ( ($cc_expires_year == date('Y')) && ($cc_expires_month < date('m')) ) {
          tep_redirect(OSCOM::link('checkout_payment.php', 'payment_error=' . $this->code . '&error=cardexpires', 'SSL'));
        }

        if ( MODULE_PAYMENT_BRAINTREE_CC_VERIFY_WITH_CVV == 'True' ) {
          if ( !isset($cc_cvv) || empty($cc_cvv) ) {
            tep_redirect(OSCOM::link('checkout_payment.php', 'payment_error=' . $this->code . '&error=cardcvv', 'SSL'));
          }
        }
      }

      $braintree_result = null;

      Braintree_Configuration::environment(MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_SERVER == 'Live' ? 'production' : 'sandbox');
      Braintree_Configuration::merchantId(MODULE_PAYMENT_BRAINTREE_CC_MERCHANT_ID);
      Braintree_Configuration::publicKey(MODULE_PAYMENT_BRAINTREE_CC_PUBLIC_KEY);
      Braintree_Configuration::privateKey(MODULE_PAYMENT_BRAINTREE_CC_PRIVATE_KEY);

      $currency = $this->getTransactionCurrency();

      $data = array('amount' => $this->format_raw($order->info['total'], $currency),
                    'merchantAccountId' => $this->getMerchantAccountId($currency),
                    'creditCard' => array('cardholderName' => $cc_owner),
                    'customer' => array('firstName' => $order->customer['firstname'],
                                        'lastName' => $order->customer['lastname'],
                                        'company' => $order->customer['company'],
                                        'phone' => $order->customer['telephone'],
                                        'email' => $order->customer['email_address']),
                    'billing' => array('firstName' => $order->billing['firstname'],
                                       'lastName' => $order->billing['lastname'],
                                       'company' => $order->billing['company'],
                                       'streetAddress' => $order->billing['street_address'],
                                       'extendedAddress' => $order->billing['suburb'],
                                       'locality' => $order->billing['city'],
                                       'region' => tep_get_zone_name($order->billing['country_id'], $order->billing['zone_id'], $order->billing['state']),
                                       'postalCode' => $order->billing['postcode'],
                                       'countryCodeAlpha2' => $order->billing['country']['iso_code_2']),
                    'options' => array());

      if ( MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_METHOD == 'Payment' ) {
        $data['options']['submitForSettlement'] = true;
      }

      if ( $order->content_type != 'virtual' ) {
        $data['shipping'] = array('firstName' => $order->delivery['firstname'],
                                  'lastName' => $order->delivery['lastname'],
                                  'company' => $order->delivery['company'],
                                  'streetAddress' => $order->delivery['street_address'],
                                  'extendedAddress' => $order->delivery['suburb'],
                                  'locality' => $order->delivery['city'],
                                  'region' => tep_get_zone_name($order->delivery['country_id'], $order->delivery['zone_id'], $order->delivery['state']),
                                  'postalCode' => $order->delivery['postcode'],
                                  'countryCodeAlpha2' => $order->delivery['country']['iso_code_2']);
      }

      if ( !isset($braintree_token) ) {
        $data['creditCard']['number'] = $cc_number;
        $data['creditCard']['expirationMonth'] = $cc_expires_month;
        $data['creditCard']['expirationYear'] = $cc_expires_year;

        if ( MODULE_PAYMENT_BRAINTREE_CC_VERIFY_WITH_CVV == 'True' ) {
          $data['creditCard']['cvv'] = $cc_cvv;
        }

        if ( (MODULE_PAYMENT_BRAINTREE_CC_TOKENS == 'True') && isset($_POST['cc_save']) && ($_POST['cc_save'] == 'true') ) {
          $data['options']['storeInVaultOnSuccess'] = true;
        }
      } else {
        $data['paymentMethodToken'] = $braintree_token;

        if ( MODULE_PAYMENT_BRAINTREE_CC_VERIFY_WITH_CVV == 'True' ) {
          $data['creditCard']['cvv'] = $braintree_token_cvv;
        }
      }

      $error = false;

      try {
        $braintree_result = Braintree_Transaction::sale($data);
      } catch ( Exception $e ) {
        $error = true;
      }

      if ( ($error === false) && ($braintree_result->success) ) {
        return true;
      }

      if ( $braintree_result->transaction ) {
        $braintree_error = $braintree_result->message;
      } else {
        $braintree_error = '';

        if ( isset($braintree_result->errors) ) {
          foreach ( $braintree_result->errors->deepAll() as $error ) {
            $braintree_error .= $error->message . ' ';
          }

          if ( !empty($braintree_error) ) {
            $braintree_error = substr($braintree_error, 0, -1);
          }
        }
      }

      if (!empty($braintree_error)) {
        $_SESSION['braintree_error'] = $braintree_error;
      }

      tep_redirect(OSCOM::link('checkout_payment.php', 'payment_error=' . $this->code, 'SSL'));
    }

    function after_process() {
      global $insert_id, $braintree_result, $braintree_token;

      $OSCOM_Db = Registry::get('Db');

      $status_comment = array('Transaction ID: ' . $braintree_result->transaction->id);

      if ( (MODULE_PAYMENT_BRAINTREE_CC_TOKENS == 'True') && isset($_POST['cc_save']) && ($_POST['cc_save'] == 'true') && !isset($braintree_token) && isset($braintree_result->transaction->creditCard['token']) ) {
        $token = $braintree_result->transaction->creditCard['token'];
        $type = $braintree_result->transaction->creditCard['cardType'];
        $number = $braintree_result->transaction->creditCard['last4'];
        $expiry = $braintree_result->transaction->creditCard['expirationMonth'] . $braintree_result->transaction->creditCard['expirationYear'];

        $Qcheck = $OSCOM_Db->get('customers_braintree_tokens', 'id', ['customers_id' => $_SESSION['customer_id'], 'braintree_token' => $token], null, 1);

        if ($Qcheck->fetch() === false) {
          $sql_data_array = array('customers_id' => (int)$_SESSION['customer_id'],
                                  'braintree_token' => $token,
                                  'card_type' => $type,
                                  'number_filtered' => $number,
                                  'expiry_date' => $expiry,
                                  'date_added' => 'now()');

          $OSCOM_Db->save('customers_braintree_tokens', $sql_data_array);
        }

        $status_comment[] = 'Token Created: Yes';
      } elseif ( isset($braintree_token) ) {
        $status_comment[] = 'Token Used: Yes';
      }

      $sql_data_array = array('orders_id' => $insert_id,
                              'orders_status_id' => MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_ORDER_STATUS_ID,
                              'date_added' => 'now()',
                              'customer_notified' => '0',
                              'comments' => implode("\n", $status_comment));

      $OSCOM_Db->save('orders_status_history', $sql_data_array);
    }

    function get_error() {
      $message = MODULE_PAYMENT_BRAINTREE_CC_ERROR_GENERAL;

      if ( isset($_GET['error']) && !empty($_GET['error']) ) {
        switch ($_GET['error']) {
          case 'cardowner':
            $message = MODULE_PAYMENT_BRAINTREE_CC_ERROR_CARDOWNER;
            break;

          case 'cardnumber':
            $message = MODULE_PAYMENT_BRAINTREE_CC_ERROR_CARDNUMBER;
            break;

          case 'cardexpires':
            $message = MODULE_PAYMENT_BRAINTREE_CC_ERROR_CARDEXPIRES;
            break;

          case 'cardcvv':
            $message = MODULE_PAYMENT_BRAINTREE_CC_ERROR_CARDCVV;
            break;
        }
      } elseif ( isset($_SESSION['braintree_error']) ) {
        $message = $_SESSION['braintree_error'] . ' ' . $message;

        unset($_SESSION['braintree_error']);
      }

      $error = array('title' => MODULE_PAYMENT_BRAINTREE_CC_ERROR_TITLE,
                     'error' => $message);

      return $error;
    }

    function check() {
      return defined('MODULE_PAYMENT_BRAINTREE_CC_STATUS');
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
      return Registry::get('Db')->query('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")')->rowCount();
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

      $Qcheck = $OSCOM_Db->query('show tables like "customers_braintree_tokens"');

      if ($Qcheck->fetch() === false) {
        $sql = <<<EOD
CREATE TABLE customers_braintree_tokens (
  id int NOT NULL auto_increment,
  customers_id int NOT NULL,
  braintree_token varchar(255) NOT NULL,
  card_type varchar(32) NOT NULL,
  number_filtered varchar(20) NOT NULL,
  expiry_date char(6) NOT NULL,
  date_added datetime NOT NULL,
  PRIMARY KEY (id),
  KEY idx_cbraintreet_customers_id (customers_id),
  KEY idx_cbraintreet_token (braintree_token)
);
EOD;

        $OSCOM_Db->exec($sql);
      }

      if (!defined('MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_ORDER_STATUS_ID')) {
        $Qcheck = $OSCOM_Db->get('orders_status', 'orders_status_id', ['orders_status_name' => 'Braintree [Transactions]'], null, 1);

        if ($Qcheck->fetch() === false) {
          $Qstatus = $OSCOM_Db->get('orders_status', 'max(orders_status_id) as status_id');

          $status_id = $Qstatus->valueInt('status_id') + 1;

          $languages = tep_get_languages();

          foreach ($languages as $lang) {
            $OSCOM_Db->save('orders_status', [
              'orders_status_id' => $status_id,
              'language_id' => $lang['id'],
              'orders_status_name' => 'Braintree [Transactions]',
              'public_flag' => 0,
              'downloads_flag' => 0
            ]);
          }
        } else {
          $status_id = $Qcheck->valueInt('orders_status_id');
        }
      } else {
        $status_id = MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_ORDER_STATUS_ID;
      }

      $params = array('MODULE_PAYMENT_BRAINTREE_CC_STATUS' => array('title' => 'Enable Braintree Module',
                                                                    'desc' => 'Do you want to accept Braintree payments?',
                                                                    'value' => 'True',
                                                                    'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_BRAINTREE_CC_MERCHANT_ID' => array('title' => 'Merchant ID',
                                                                         'desc' => 'The Braintree account Merchant ID to use.'),
                      'MODULE_PAYMENT_BRAINTREE_CC_PUBLIC_KEY' => array('title' => 'Public Key',
                                                                        'desc' => 'The Braintree account public key to use.'),
                      'MODULE_PAYMENT_BRAINTREE_CC_PRIVATE_KEY' => array('title' => 'Private Key',
                                                                         'desc' => 'The Braintree account private key to use.'),
                      'MODULE_PAYMENT_BRAINTREE_CC_CLIENT_KEY' => array('title' => 'Client Side Encryption Key',
                                                                        'desc' => 'The client side encryption key to use.',
                                                                        'set_func' => 'tep_cfg_braintree_cc_set_client_key(',
                                                                        'use_func' => 'tep_cfg_braintree_cc_show_client_key'),
                      'MODULE_PAYMENT_BRAINTREE_CC_MERCHANT_ACCOUNTS' => array('title' => 'Merchant Accounts',
                                                                               'desc' => 'Merchant accounts and defined currencies.',
                                                                               'set_func' => 'tep_cfg_braintree_cc_set_merchant_accounts(',
                                                                               'use_func' => 'tep_cfg_braintree_cc_show_merchant_accounts'),
                      'MODULE_PAYMENT_BRAINTREE_CC_TOKENS' => array('title' => 'Create Tokens',
                                                                       'desc' => 'Create and store tokens for card payments customers can use on their next purchase?',
                                                                       'value' => 'False',
                                                                       'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_BRAINTREE_CC_VERIFY_WITH_CVV' => array('title' => 'Verify With CVV',
                                                                                'desc' => 'Verify the credit card with the billing address with the Card Verification Value (CVV)?',
                                                                                'value' => 'True',
                                                                                'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
                      'MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_METHOD' => array('title' => 'Transaction Method',
                                                                                'desc' => 'The processing method to use for each transaction.',
                                                                                'value' => 'Authorize',
                                                                                'set_func' => 'tep_cfg_select_option(array(\'Authorize\', \'Payment\'), '),
                      'MODULE_PAYMENT_BRAINTREE_CC_ORDER_STATUS_ID' => array('title' => 'Set Order Status',
                                                                             'desc' => 'Set the status of orders made with this payment module to this value',
                                                                             'value' => '0',
                                                                             'use_func' => 'tep_get_order_status_name',
                                                                             'set_func' => 'tep_cfg_pull_down_order_statuses('),
                      'MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_ORDER_STATUS_ID' => array('title' => 'Transaction Order Status',
                                                                                         'desc' => 'Include transaction information in this order status level',
                                                                                         'value' => $status_id,
                                                                                         'set_func' => 'tep_cfg_pull_down_order_statuses(',
                                                                                         'use_func' => 'tep_get_order_status_name'),
                      'MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_SERVER' => array('title' => 'Transaction Server',
                                                                                'desc' => 'Perform transactions on the production server or on the testing server.',
                                                                                'value' => 'Live',
                                                                                'set_func' => 'tep_cfg_select_option(array(\'Live\', \'Sandbox\'), '),
                      'MODULE_PAYMENT_BRAINTREE_CC_ZONE' => array('title' => 'Payment Zone',
                                                                  'desc' => 'If a zone is selected, only enable this payment method for that zone.',
                                                                  'value' => '0',
                                                                  'use_func' => 'tep_get_zone_class_title',
                                                                  'set_func' => 'tep_cfg_pull_down_zone_classes('),
                      'MODULE_PAYMENT_BRAINTREE_CC_SORT_ORDER' => array('title' => 'Sort order of display.',
                                                                        'desc' => 'Sort order of display. Lowest is displayed first.',
                                                                        'value' => '0'));

      return $params;
    }

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

    function getTransactionCurrency() {
      return $this->isValidCurrency($_SESSION['currency']) ? $_SESSION['currency'] : DEFAULT_CURRENCY;
    }

    function getMerchantAccountId($currency) {
      foreach ( explode(';', MODULE_PAYMENT_BRAINTREE_CC_MERCHANT_ACCOUNTS) as $ma ) {
        list($a, $c) = explode(':', $ma);

        if ( $c == $currency ) {
          return $a;
        }
      }

      return '';
    }

    function isValidCurrency($currency) {
      global $currencies;

      foreach ( explode(';', MODULE_PAYMENT_BRAINTREE_CC_MERCHANT_ACCOUNTS) as $combo ) {
        list($id, $c) = explode(':', $combo);

        if ( $c == $currency ) {
          return $currencies->is_set($c);
        }
      }

      return false;
    }

    function deleteCard($token, $token_id) {
      $OSCOM_Db = Registry::get('Db');

      Braintree_Configuration::environment(MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_SERVER == 'Live' ? 'production' : 'sandbox');
      Braintree_Configuration::merchantId(MODULE_PAYMENT_BRAINTREE_CC_MERCHANT_ID);
      Braintree_Configuration::publicKey(MODULE_PAYMENT_BRAINTREE_CC_PUBLIC_KEY);
      Braintree_Configuration::privateKey(MODULE_PAYMENT_BRAINTREE_CC_PRIVATE_KEY);

      try {
        Braintree_CreditCard::delete($token);
      } catch ( Exception $e ) {
      }

      return $OSCOM_Db->delete('customers_braintree_tokens', ['id' => $token_id, 'customers_id' => $_SESSION['customer_id'], 'braintree_token' => $token]) === 1;
    }

    function templateClassExists() {
      return class_exists('oscTemplate') && isset($GLOBALS['oscTemplate']) && is_object($GLOBALS['oscTemplate']) && (get_class($GLOBALS['oscTemplate']) == 'oscTemplate');
    }

    function getSubmitCardDetailsJavascript() {
      $braintree_client_key = MODULE_PAYMENT_BRAINTREE_CC_CLIENT_KEY;

      $js = <<<EOD
<script src="https://js.braintreegateway.com/v1/braintree.js"></script>
<script>
$(function() {
  $('form[name="checkout_confirmation"]').attr('id', 'braintree-payment-form');

  var braintree = Braintree.create('{$braintree_client_key}');
  braintree.onSubmitEncryptForm('braintree-payment-form');

  if ( $('#braintree_table').length > 0 ) {
    if ( typeof($('#braintree_table').parent().closest('table').attr('width')) == 'undefined' ) {
      $('#braintree_table').parent().closest('table').attr('width', '100%');
    }

    $('#braintree_table .moduleRowExtra').hide();

    $('#braintree_table_new_card').hide();

    $('form[name="checkout_confirmation"] input[name="braintree_card"]').change(function() {
      var selected = $(this).val();

      if ( selected == '0' ) {
        braintreeShowNewCardFields();
      } else {
        $('#braintree_table_new_card').hide();

        $('[id^="braintree_card_cvv_"]').hide();

        $('#braintree_card_cvv_' + selected).show();
      }

      $('tr[id^="braintree_card_"]').removeClass('moduleRowSelected');
      $('#braintree_card_' + selected).addClass('moduleRowSelected');
    });

    $('form[name="checkout_confirmation"] input[name="braintree_card"]:first').prop('checked', true).trigger('change');

    $('#braintree_table .moduleRow').hover(function() {
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
    if ( typeof($('#braintree_table_new_card').parent().closest('table').attr('width')) == 'undefined' ) {
      $('#braintree_table_new_card').parent().closest('table').attr('width', '100%');
    }
  }
});

function braintreeShowNewCardFields() {
  $('[id^="braintree_card_cvv_"]').hide();

  $('#braintree_table_new_card').show();
}
</script>
EOD;

      return $js;
    }
  }

  function tep_cfg_braintree_cc_set_client_key($value, $name) {
    return tep_draw_textarea_field('configuration[' . $name . ']', '', '50', '12', $value);
  }

  function tep_cfg_braintree_cc_show_client_key($key) {
    $string = '';

    if ( strlen($key) > 0 ) {
      $string = substr($key, 0, 20) . ' ...';
    }

    return $string;
  }

  function tep_cfg_braintree_cc_set_merchant_accounts($value, $key) {
    if ( !class_exists('currencies') ) {
      include(DIR_WS_CLASSES . 'currencies.php');
    }

    $data = array();

    foreach ( explode(';', $value) as $ma ) {
      list($a, $currency) = explode(':', $ma);

      $data[$currency] = $a;
    }

    $currencies = new currencies();

    $c_array = array_keys($currencies->currencies);
    sort($c_array);

    $result = '';

    foreach ( $c_array as $c ) {
      if ( $c == DEFAULT_CURRENCY ) {
        $result .= '<strong>';
      }

      $result .= $c . ':';

      if ( $c == DEFAULT_CURRENCY ) {
        $result .= '</strong>';
      }

      $result .= '&nbsp;' . HTML::inputField('braintree_ma[' . $c . ']', (isset($data[$c]) ? $data[$c] : '')) . '<br />';
    }

    if ( !empty($result) ) {
      $result = substr($result, 0, -6);
    }

    $result .= tep_draw_hidden_field('configuration[' . $key . ']', $value);

    $result .= <<<EOD
<script>
$(function() {
  $('form[name="modules"]').submit(function() {
    var ma_string = '';

    $('form[name="modules"] input[name^="braintree_ma["]').each(function() {
      if ( $(this).val().length > 0 ) {
        ma_string += $(this).val() + ':' + $(this).attr('name').slice(13, -1) + ';';
      }
    });

    if ( ma_string.length > 0 ) {
      ma_string = ma_string.slice(0, -1);
    }

    $('form[name="modules"] input[name="configuration[{$key}]"]').val(ma_string);
  })
});
</script>
EOD;

    return $result;
  }

  function tep_cfg_braintree_cc_show_merchant_accounts($value) {
    if ( !class_exists('currencies') ) {
      include(DIR_WS_CLASSES . 'currencies.php');
    }

    $data = array();

    foreach ( explode(';', $value) as $ma ) {
      list($a, $currency) = explode(':', $ma);

      $data[$currency] = $a;
    }

    $currencies = new currencies();

    $c_array = array_keys($currencies->currencies);
    sort($c_array);

    $result = '';

    foreach ( $c_array as $c ) {
      if ( $c == DEFAULT_CURRENCY ) {
        $result .= '<strong>';
      }

      $result .= $c . ':';

      if ( $c == DEFAULT_CURRENCY ) {
        $result .= '</strong>';
      }

      $result .= '&nbsp;' . (isset($data[$c]) ? $data[$c] : '') . '<br />';
    }

    if ( !empty($result) ) {
      $result = substr($result, 0, -6);
    }

    return $result;
  }

  function tep_braintree_autoloader($class) {
    if ( substr($class, 0, 10) == 'Braintree_' ) {
      $file = dirname(__FILE__) . '/braintree_cc/' . str_replace('_', '/', $class) . '.php';

      if ( file_exists($file) ) {
        include($file);
      }
    }
  }
?>
