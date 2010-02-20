<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2008 osCommerce

  Released under the GNU General Public License
*/

  class authorizenet_cc_sim {
    var $code, $title, $description, $enabled;

// class constructor
    function authorizenet_cc_sim() {
      global $order;

      $this->signature = 'authorizenet|authorizenet_cc_sim|1.0|2.2';
      $this->api_version = '3.1';

      $this->code = 'authorizenet_cc_sim';
      $this->title = MODULE_PAYMENT_AUTHORIZENET_CC_SIM_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_AUTHORIZENET_CC_SIM_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_AUTHORIZENET_CC_SIM_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_AUTHORIZENET_CC_SIM_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_AUTHORIZENET_CC_SIM_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_AUTHORIZENET_CC_SIM_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_AUTHORIZENET_CC_SIM_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

      switch (MODULE_PAYMENT_AUTHORIZENET_CC_SIM_TRANSACTION_SERVER) {
        case 'Live':
          $this->form_action_url = 'https://secure.authorize.net/gateway/transact.dll';
          break;

        default:
          $this->form_action_url = 'https://test.authorize.net/gateway/transact.dll';
          break;
      }
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_AUTHORIZENET_CC_SIM_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_AUTHORIZENET_CC_SIM_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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
      return false;
    }

    function process_button() {
      global $customer_id, $order, $sendto, $currency;

      $process_button_string = $this->_InsertFP(MODULE_PAYMENT_AUTHORIZENET_CC_SIM_LOGIN_ID, MODULE_PAYMENT_AUTHORIZENET_CC_SIM_TRANSACTION_KEY, $this->format_raw($order->info['total']), rand(1, 1000), $currency);

      $process_button_string .= tep_draw_hidden_field('x_login', substr(MODULE_PAYMENT_AUTHORIZENET_CC_SIM_LOGIN_ID, 0, 20)) .
                                tep_draw_hidden_field('x_version', '3.1') .
                                tep_draw_hidden_field('x_show_form', 'PAYMENT_FORM') .
                                tep_draw_hidden_field('x_relay_response', 'TRUE') .
                                tep_draw_hidden_field('x_relay_url', tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', false)) .
                                tep_draw_hidden_field('x_first_name', substr($order->billing['firstname'], 0, 50)) .
                                tep_draw_hidden_field('x_last_name', substr($order->billing['lastname'], 0, 50)) .
                                tep_draw_hidden_field('x_company', substr($order->billing['company'], 0, 50)) .
                                tep_draw_hidden_field('x_address', substr($order->billing['street_address'], 0, 60)) .
                                tep_draw_hidden_field('x_city', substr($order->billing['city'], 0, 40)) .
                                tep_draw_hidden_field('x_state', substr($order->billing['state'], 0, 40)) .
                                tep_draw_hidden_field('x_zip', substr($order->billing['postcode'], 0, 20)) .
                                tep_draw_hidden_field('x_country', substr($order->billing['country']['title'], 0, 60)) .
                                tep_draw_hidden_field('x_phone', substr($order->customer['telephone'], 0, 25)) .
                                tep_draw_hidden_field('x_cust_id', substr($customer_id, 0, 20)) .
                                tep_draw_hidden_field('x_customer_ip', tep_get_ip_address()) .
                                tep_draw_hidden_field('x_email', substr($order->customer['email_address'], 0, 255)) .
                                tep_draw_hidden_field('x_description', substr(STORE_NAME, 0, 255)) .
                                tep_draw_hidden_field('x_amount', substr($this->format_raw($order->info['total']), 0, 15)) .
                                tep_draw_hidden_field('x_currency_code', substr($currency, 0, 3)) .
                                tep_draw_hidden_field('x_method', 'CC') .
                                tep_draw_hidden_field('x_type', ((MODULE_PAYMENT_AUTHORIZENET_CC_SIM_TRANSACTION_METHOD == 'Capture') ? 'AUTH_CAPTURE' : 'AUTH_ONLY'));

      if (is_numeric($sendto) && ($sendto > 0)) {
        $process_button_string .= tep_draw_hidden_field('x_ship_to_first_name', substr($order->delivery['firstname'], 0, 50)) .
                                  tep_draw_hidden_field('x_ship_to_last_name', substr($order->delivery['lastname'], 0, 50)) .
                                  tep_draw_hidden_field('x_ship_to_company', substr($order->delivery['company'], 0, 50)) .
                                  tep_draw_hidden_field('x_ship_to_address', substr($order->delivery['street_address'], 0, 60)) .
                                  tep_draw_hidden_field('x_ship_to_city', substr($order->delivery['city'], 0, 40)) .
                                  tep_draw_hidden_field('x_ship_to_state', substr($order->delivery['state'], 0, 40)) .
                                  tep_draw_hidden_field('x_ship_to_zip', substr($order->delivery['postcode'], 0, 20)) .
                                  tep_draw_hidden_field('x_ship_to_country', substr($order->delivery['country']['title'], 0, 60));
      }

      if (MODULE_PAYMENT_AUTHORIZENET_CC_SIM_TRANSACTION_MODE == 'Test') {
        $process_button_string .= tep_draw_hidden_field('x_test_request', 'TRUE');
      }

      for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
        $process_button_string .= tep_draw_hidden_field('x_line_item', ($i+1) . '<|>' . substr($order->products[$i]['name'], 0, 31) . '<|>' . substr($order->products[$i]['name'], 0, 255) . '<|>' . $order->products[$i]['qty'] . '<|>' . $this->format_raw($order->products[$i]['final_price']) . '<|>' . ($order->products[$i]['tax'] > 0 ? 'YES' : 'NO'));
      }

      $tax_value = 0;

      reset($order->info['tax_groups']);
      while (list($key, $value) = each($order->info['tax_groups'])) {
        if ($value > 0) {
          $tax_value += $this->format_raw($value);
        }
      }

      if ($tax_value > 0) {
        $process_button_string .= tep_draw_hidden_field('x_tax', $this->format_raw($tax_value));
      }

      $process_button_string .= tep_draw_hidden_field('x_freight', $this->format_raw($order->info['shipping_cost'])) .
                                tep_draw_hidden_field(tep_session_name(), tep_session_id());

      return $process_button_string;
    }

    function before_process() {
      global $HTTP_POST_VARS, $order;

      $error = false;

      if ($HTTP_POST_VARS['x_response_code'] == '1') {
        if (tep_not_null(MODULE_PAYMENT_AUTHORIZENET_CC_SIM_MD5_HASH) && ($HTTP_POST_VARS['x_MD5_Hash'] != strtoupper(md5(MODULE_PAYMENT_AUTHORIZENET_CC_SIM_MD5_HASH . MODULE_PAYMENT_AUTHORIZENET_CC_SIM_LOGIN_ID . $HTTP_POST_VARS['x_trans_id'] . $this->format_raw($order->info['total']))))) {
          $error = 'verification';
        } elseif ($HTTP_POST_VARS['x_amount'] != $this->format_raw($order->info['total'])) {
          $error = 'verification';
        }
      } elseif ($HTTP_POST_VARS['x_response_code'] == '2') {
        $error = 'declined';
      } else {
        $error = 'general';
      }

      if ($error != false) {
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=' . $error, 'SSL', true, false));
      }
    }

    function after_process() {
      return false;
    }

    function get_error() {
      global $HTTP_GET_VARS;

      $error_message = MODULE_PAYMENT_AUTHORIZENET_CC_SIM_ERROR_GENERAL;

      switch ($HTTP_GET_VARS['error']) {
        case 'verification':
          $error_message = MODULE_PAYMENT_AUTHORIZENET_CC_SIM_ERROR_VERIFICATION;
          break;

        case 'declined':
          $error_message = MODULE_PAYMENT_AUTHORIZENET_CC_SIM_ERROR_DECLINED;
          break;

        default:
          $error_message = MODULE_PAYMENT_AUTHORIZENET_CC_SIM_ERROR_GENERAL;
          break;
      }

      $error = array('title' => MODULE_PAYMENT_AUTHORIZENET_CC_SIM_ERROR_TITLE,
                     'error' => $error_message);

      return $error;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Authorize.net Credit Card SIM', 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_STATUS', 'False', 'Do you want to accept Authorize.net Credit Card SIM payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Login ID', 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_LOGIN_ID', '', 'The login ID used for the Authorize.net service', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Transaction Key', 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_TRANSACTION_KEY', '', 'Transaction key used for encrypting data', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('MD5 Hash', 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_MD5_HASH', '', 'The MD5 hash value to verify transactions with', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Server', 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_TRANSACTION_SERVER', 'Live', 'Perform transactions on the live or test server. The test server should only be used by developers with Authorize.net test accounts.', '6', '0', 'tep_cfg_select_option(array(\'Live\', \'Test\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Mode', 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_TRANSACTION_MODE', 'Test', 'Transaction mode used for processing orders', '6', '0', 'tep_cfg_select_option(array(\'Live\', \'Test\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Method', 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_TRANSACTION_METHOD', 'Authorization', 'The processing method to use for each transaction.', '6', '0', 'tep_cfg_select_option(array(\'Authorization\', \'Capture\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_AUTHORIZENET_CC_SIM_STATUS', 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_LOGIN_ID', 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_TRANSACTION_KEY', 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_MD5_HASH', 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_TRANSACTION_SERVER', 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_TRANSACTION_MODE', 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_TRANSACTION_METHOD', 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_ZONE', 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_ORDER_STATUS_ID', 'MODULE_PAYMENT_AUTHORIZENET_CC_SIM_SORT_ORDER');
    }

    function _hmac($key, $data) {
      if (function_exists('mhash') && defined('MHASH_MD5')) {
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

    function _InsertFP($loginid, $x_tran_key, $amount, $sequence, $currency = '') {
      $tstamp = time();

      $fingerprint = $this->_hmac($x_tran_key, $loginid . '^' . $sequence . '^' . $tstamp . '^' . $amount . '^' . $currency);

      return tep_draw_hidden_field('x_fp_sequence', $sequence) .
             tep_draw_hidden_field('x_fp_timestamp', $tstamp) .
             tep_draw_hidden_field('x_fp_hash', $fingerprint);
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
