<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class ipayment_cc {
    var $code, $title, $description, $enabled;

// class constructor
    function ipayment_cc() {
      global $order;

      $this->signature = 'ipayment|ipayment_cc|1.1|2.2';
      $this->api_version = '2.0';

      $this->code = 'ipayment_cc';
      $this->title = MODULE_PAYMENT_IPAYMENT_CC_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_IPAYMENT_CC_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_IPAYMENT_CC_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_IPAYMENT_CC_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_IPAYMENT_CC_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_IPAYMENT_CC_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_IPAYMENT_CC_ORDER_STATUS_ID;
      }

      $this->gateway_addresses = array('212.227.34.218', '212.227.34.219', '212.227.34.220');

      if (is_object($order)) $this->update_status();

      $this->form_action_url = 'https://ipayment.de/merchant/' . MODULE_PAYMENT_IPAYMENT_CC_ID . '/processor/2.0/';
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_IPAYMENT_CC_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_IPAYMENT_CC_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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

      $confirmation = array('fields' => array(array('title' => MODULE_PAYMENT_IPAYMENT_CC_TEXT_CREDIT_CARD_OWNER,
                                                    'field' => tep_draw_input_field('addr_name', $order->billing['firstname'] . ' ' . $order->billing['lastname'])),
                                              array('title' => MODULE_PAYMENT_IPAYMENT_CC_TEXT_CREDIT_CARD_NUMBER,
                                                    'field' => tep_draw_input_field('cc_number')),
                                              array('title' => MODULE_PAYMENT_IPAYMENT_CC_TEXT_CREDIT_CARD_EXPIRES,
                                                    'field' => tep_draw_pull_down_menu('cc_expdate_month', $expires_month) . '&nbsp;' . tep_draw_pull_down_menu('cc_expdate_year', $expires_year)),
                                              array('title' => MODULE_PAYMENT_IPAYMENT_CC_TEXT_CREDIT_CARD_CHECKNUMBER,
                                                    'field' => tep_draw_input_field('cc_checkcode', '', 'size="5" maxlength="4"'))));

      return $confirmation;
    }

    function process_button() {
      global $order, $currency;

      $zone_code = '';

      if (is_numeric($order->billing['zone_id']) && ($order->billing['zone_id'] > 0)) {
        $zone_query = tep_db_query("select zone_code from " . TABLE_ZONES . " where zone_id = '" . (int)$order->billing['zone_id'] . "'");
        if (tep_db_num_rows($zone_query)) {
          $zone = tep_db_fetch_array($zone_query);

          $zone_code = $zone['zone_code'];
        }
      }

      $process_button_string = tep_draw_hidden_field('silent', '1') .
                               tep_draw_hidden_field('trx_paymenttyp', 'cc') .
                               tep_draw_hidden_field('trxuser_id', MODULE_PAYMENT_IPAYMENT_CC_USER_ID) .
                               tep_draw_hidden_field('trxpassword', MODULE_PAYMENT_IPAYMENT_CC_PASSWORD) .
                               tep_draw_hidden_field('from_ip', tep_get_ip_address()) .
                               tep_draw_hidden_field('trx_currency', $currency) .
                               tep_draw_hidden_field('trx_amount', $this->format_raw($order->info['total'])*100) .
                               tep_draw_hidden_field('trx_typ', ((MODULE_PAYMENT_IPAYMENT_CC_TRANSACTION_METHOD == 'Capture') ? 'auth' : 'preauth')) .
                               tep_draw_hidden_field('addr_email', $order->customer['email_address']) .
                               tep_draw_hidden_field('addr_street', $order->billing['street_address']) .
                               tep_draw_hidden_field('addr_city', $order->billing['city']) .
                               tep_draw_hidden_field('addr_zip', $order->billing['postcode']) .
                               tep_draw_hidden_field('addr_country', $order->billing['country']['iso_code_2']) .
                               tep_draw_hidden_field('addr_state', $zone_code) .
                               tep_draw_hidden_field('addr_telefon', $order->customer['telephone']) .
                               tep_draw_hidden_field('redirect_url', tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', true)) .
                               tep_draw_hidden_field('silent_error_url', tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code, 'SSL', true)) .
                               tep_draw_hidden_field('hidden_trigger_url', tep_href_link('ext/modules/payment/ipayment/callback_cc.php', '', 'SSL', false)) .
                               tep_draw_hidden_field('client_name', 'oscommerce') .
                               tep_draw_hidden_field('client_version', $this->signature);

      if (tep_not_null(MODULE_PAYMENT_IPAYMENT_CC_SECRET_HASH_PASSWORD)) {
        $process_button_string .= tep_draw_hidden_field('trx_securityhash', md5(MODULE_PAYMENT_IPAYMENT_CC_USER_ID . ($this->format_raw($order->info['total']) * 100) . $currency . MODULE_PAYMENT_IPAYMENT_CC_PASSWORD . MODULE_PAYMENT_IPAYMENT_CC_SECRET_HASH_PASSWORD));
      }

      return $process_button_string;
    }

    function before_process() {
      global $HTTP_GET_VARS, $HTTP_SERVER_VARS, $order, $currency;

      if ($HTTP_GET_VARS['ret_errorcode'] != '0') {
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=' . tep_output_string_protected($HTTP_GET_VARS['ret_errormsg'])));
      }

      if (tep_not_null(MODULE_PAYMENT_IPAYMENT_CC_SECRET_HASH_PASSWORD)) {
        $pass = true;

// verify ret_param_checksum
        if ($HTTP_GET_VARS['ret_param_checksum'] != md5(MODULE_PAYMENT_IPAYMENT_CC_USER_ID . ($this->format_raw($order->info['total']) * 100) . $currency . $HTTP_GET_VARS['ret_authcode'] . $HTTP_GET_VARS['ret_booknr'] . MODULE_PAYMENT_IPAYMENT_CC_SECRET_HASH_PASSWORD)) {
          $pass = false;
        }

// verify ret_url_checksum
        $url= 'http' . (ENABLE_SSL == true ? 's' : '') . '://' . $HTTP_SERVER_VARS['SERVER_NAME'] . $HTTP_SERVER_VARS['REQUEST_URI'];
        $url_without_checksum = substr($url, 0, strpos($url, '&ret_url_checksum')+1);
        if ($HTTP_GET_VARS['ret_url_checksum'] != md5($url_without_checksum . MODULE_PAYMENT_IPAYMENT_CC_SECRET_HASH_PASSWORD)) {
          $pass = false;
        }

        if ($pass != true) {
          tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code));
        }
      }

      return false;
    }

    function after_process() {
      return false;
    }

    function get_error() {
      global $HTTP_GET_VARS;

      $error = array('title' => MODULE_PAYMENT_IPAYMENT_CC_ERROR_HEADING,
                     'error' => ((isset($HTTP_GET_VARS['error'])) ? stripslashes(urldecode($HTTP_GET_VARS['error'])) : MODULE_PAYMENT_IPAYMENT_CC_ERROR_MESSAGE));

      return $error;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_IPAYMENT_CC_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable iPayment', 'MODULE_PAYMENT_IPAYMENT_CC_STATUS', 'False', 'Do you want to accept iPayment payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Account Number', 'MODULE_PAYMENT_IPAYMENT_CC_ID', '99999', 'The account number used for the iPayment service', '6', '2', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('User ID', 'MODULE_PAYMENT_IPAYMENT_CC_USER_ID', '99998', 'The user ID for the iPayment service', '6', '3', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('User Password', 'MODULE_PAYMENT_IPAYMENT_CC_PASSWORD', '0', 'The user password for the iPayment service', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Method', 'MODULE_PAYMENT_IPAYMENT_CC_TRANSACTION_METHOD', 'Authorization', 'The processing method to use for each transaction.', '6', '0', 'tep_cfg_select_option(array(\'Authorization\', \'Capture\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Secret Hash Password', 'MODULE_PAYMENT_IPAYMENT_CC_SECRET_HASH_PASSWORD', 'testtest', 'The secret hash password to validate transactions with', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Transaction Notification (E-Mail)', 'MODULE_PAYMENT_IPAYMENT_CC_DEBUG_EMAIL', '', 'An e-mail address to send transaction notifications to.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_IPAYMENT_CC_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_IPAYMENT_CC_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_IPAYMENT_CC_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_IPAYMENT_CC_STATUS', 'MODULE_PAYMENT_IPAYMENT_CC_ID', 'MODULE_PAYMENT_IPAYMENT_CC_USER_ID', 'MODULE_PAYMENT_IPAYMENT_CC_PASSWORD', 'MODULE_PAYMENT_IPAYMENT_CC_TRANSACTION_METHOD', 'MODULE_PAYMENT_IPAYMENT_CC_SECRET_HASH_PASSWORD', 'MODULE_PAYMENT_IPAYMENT_CC_DEBUG_EMAIL', 'MODULE_PAYMENT_IPAYMENT_CC_ZONE', 'MODULE_PAYMENT_IPAYMENT_CC_ORDER_STATUS_ID', 'MODULE_PAYMENT_IPAYMENT_CC_SORT_ORDER');
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

    function sendDebugEmail($checksum_match = 0) {
      global $HTTP_POST_VARS, $HTTP_GET_VARS;

      if (tep_not_null(MODULE_PAYMENT_IPAYMENT_CC_DEBUG_EMAIL)) {
        $email_body = 'iPayment (Credit Card) Transaction' . "\n\n" .
                      'Date: ' . strftime(DATE_TIME_FORMAT) . "\n" .
                      'Checksum Match: ';

        switch ($checksum_match) {
          case 1:
            $email_body .= 'Valid';
            break;

          case -1:
            $email_body .= '##### Invalid #####';
            break;

          case 0:
          default:
            $email_body .= 'Unknown';
            break;
        }

        $email_body .= "\n\n" .
                       'POST REQUEST:' . "\n\n";

        if (!empty($HTTP_POST_VARS)) {
          foreach ($HTTP_POST_VARS as $key => $value) {
            $email_body .= $key . '=' . $value . "\n";
          }
        } else {
          $email_body .= '(empty)' . "\n";
        }

        $email_body .= "\n" . 'GET REQUEST:' . "\n\n";

        if (!empty($HTTP_GET_VARS)) {
          foreach ($HTTP_GET_VARS as $key => $value) {
            $email_body .= $key . '=' . $value . "\n";
          }
        } else {
          $email_body .= '(empty)' . "\n";
        }

        tep_mail('', MODULE_PAYMENT_IPAYMENT_CC_DEBUG_EMAIL, 'iPayment (Credit Card) Transaction', $email_body, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
      }
    }
  }
?>
