<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class psigate {
    var $code, $title, $description, $enabled;

// class constructor
    function psigate() {
      global $order;

      $this->code = 'psigate';
      $this->title = MODULE_PAYMENT_PSIGATE_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_PSIGATE_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_PSIGATE_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_PSIGATE_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_PSIGATE_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_PSIGATE_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

      $this->form_action_url = 'https://order.psigate.com/psigate.asp';
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PSIGATE_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PSIGATE_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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
      if (MODULE_PAYMENT_PSIGATE_INPUT_MODE == 'Local') {
        $js = 'if (payment_value == "' . $this->code . '") {' . "\n" .
              '  var psigate_cc_number = document.checkout_payment.psigate_cc_number.value;' . "\n" .
              '  if (psigate_cc_number == "" || psigate_cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
              '    error_message = error_message + "' . MODULE_PAYMENT_PSIGATE_TEXT_JS_CC_NUMBER . '";' . "\n" .
              '    error = 1;' . "\n" .
              '  }' . "\n" .
              '}' . "\n";

        return $js;
      } else {
        return false;
      }
    }

    function selection() {
      global $order;

      if (MODULE_PAYMENT_PSIGATE_INPUT_MODE == 'Local') {
        for ($i=1; $i<13; $i++) {
          $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1,2000)));
        }

        $today = getdate(); 
        for ($i=$today['year']; $i < $today['year']+10; $i++) {
          $expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
        }

        $selection = array('id' => $this->code,
                           'module' => $this->title,
                           'fields' => array(array('title' => MODULE_PAYMENT_PSIGATE_TEXT_CREDIT_CARD_OWNER,
                                                   'field' => $order->billing['firstname'] . ' ' . $order->billing['lastname']),
                                             array('title' => MODULE_PAYMENT_PSIGATE_TEXT_CREDIT_CARD_NUMBER,
                                                   'field' => tep_draw_input_field('psigate_cc_number')),
                                             array('title' => MODULE_PAYMENT_PSIGATE_TEXT_CREDIT_CARD_EXPIRES,
                                                   'field' => tep_draw_pull_down_menu('psigate_cc_expires_month', $expires_month) . '&nbsp;' . tep_draw_pull_down_menu('psigate_cc_expires_year', $expires_year))));
      } else {
        $selection = array('id' => $this->code,
                           'module' => $this->title);
      }

      return $selection;
    }

    function pre_confirmation_check() {
      global $HTTP_POST_VARS;

      if (MODULE_PAYMENT_PSIGATE_INPUT_MODE == 'Local') {
        include(DIR_WS_CLASSES . 'cc_validation.php');

        $cc_validation = new cc_validation();
        $result = $cc_validation->validate($HTTP_POST_VARS['psigate_cc_number'], $HTTP_POST_VARS['psigate_cc_expires_month'], $HTTP_POST_VARS['psigate_cc_expires_year']);

        $error = '';
        switch ($result) {
          case -1:
            $error = sprintf(TEXT_CCVAL_ERROR_UNKNOWN_CARD, substr($cc_validation->cc_number, 0, 4));
            break;
          case -2:
          case -3:
          case -4:
            $error = TEXT_CCVAL_ERROR_INVALID_DATE;
            break;
          case false:
            $error = TEXT_CCVAL_ERROR_INVALID_NUMBER;
            break;
        }

        if ( ($result == false) || ($result < 1) ) {
          $payment_error_return = 'payment_error=' . $this->code . '&error=' . urlencode($error) . '&psigate_cc_owner=' . urlencode($HTTP_POST_VARS['psigate_cc_owner']) . '&psigate_cc_expires_month=' . $HTTP_POST_VARS['psigate_cc_expires_month'] . '&psigate_cc_expires_year=' . $HTTP_POST_VARS['psigate_cc_expires_year'];

          tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
        }

        $this->cc_card_type = $cc_validation->cc_type;
        $this->cc_card_number = $cc_validation->cc_number;
        $this->cc_expiry_month = $cc_validation->cc_expiry_month;
        $this->cc_expiry_year = $cc_validation->cc_expiry_year;
      } else {
        return false;
      }
    }

    function confirmation() {
      global $HTTP_POST_VARS, $order;

      if (MODULE_PAYMENT_PSIGATE_INPUT_MODE == 'Local') {
        $confirmation = array('title' => $this->title . ': ' . $this->cc_card_type,
                              'fields' => array(array('title' => MODULE_PAYMENT_PSIGATE_TEXT_CREDIT_CARD_OWNER,
                                                      'field' => $order->billing['firstname'] . ' ' . $order->billing['lastname']),
                                                array('title' => MODULE_PAYMENT_PSIGATE_TEXT_CREDIT_CARD_NUMBER,
                                                      'field' => substr($this->cc_card_number, 0, 4) . str_repeat('X', (strlen($this->cc_card_number) - 8)) . substr($this->cc_card_number, -4)),
                                                array('title' => MODULE_PAYMENT_PSIGATE_TEXT_CREDIT_CARD_EXPIRES,
                                                      'field' => strftime('%B, %Y', mktime(0,0,0,$HTTP_POST_VARS['psigate_cc_expires_month'], 1, '20' . $HTTP_POST_VARS['psigate_cc_expires_year'])))));

        return $confirmation;
      } else {
        return false;
      }
    }

    function process_button() {
      global $HTTP_SERVER_VARS, $order, $currencies;

      switch (MODULE_PAYMENT_PSIGATE_TRANSACTION_MODE) {
        case 'Always Good':
          $transaction_mode = '1';
          break;
        case 'Always Duplicate':
          $transaction_mode = '2';
          break;
        case 'Always Decline':
          $transaction_mode = '3';
          break;
        case 'Production':
        default:
          $transaction_mode = '0';
          break;
      }

      switch (MODULE_PAYMENT_PSIGATE_TRANSACTION_TYPE) {
        case 'Sale':
          $transaction_type = '0';
          break;
        case 'PostAuth':
          $transaction_type = '2';
          break;
        case 'PreAuth':
        default:
          $transaction_type = '1';
          break;
      }

      $process_button_string = tep_draw_hidden_field('MerchantID', MODULE_PAYMENT_PSIGATE_MERCHANT_ID) .
                               tep_draw_hidden_field('FullTotal', number_format($order->info['total'] * $currencies->get_value(MODULE_PAYMENT_PSIGATE_CURRENCY), $currencies->currencies[MODULE_PAYMENT_PSIGATE_CURRENCY]['decimal_places'])) .
                               tep_draw_hidden_field('ThanksURL', tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', true)) .
                               tep_draw_hidden_field('NoThanksURL', tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code, 'NONSSL', true)) .
                               tep_draw_hidden_field('Bname', $order->billing['firstname'] . ' ' . $order->billing['lastname']) .
                               tep_draw_hidden_field('Baddr1', $order->billing['street_address']) .
                               tep_draw_hidden_field('Bcity', $order->billing['city']);

      if ($order->billing['country']['iso_code_2'] == 'US') {
        $billing_state_query = tep_db_query("select zone_code from " . TABLE_ZONES . " where zone_id = '" . (int)$order->billing['zone_id'] . "'");
        $billing_state = tep_db_fetch_array($billing_state_query);

        $process_button_string .= tep_draw_hidden_field('Bstate', $billing_state['zone_code']);
      } else {
        $process_button_string .= tep_draw_hidden_field('Bstate', $order->billing['state']);
      }

      $process_button_string .= tep_draw_hidden_field('Bzip', $order->billing['postcode']) .
                                tep_draw_hidden_field('Bcountry', $order->billing['country']['iso_code_2']) .
                                tep_draw_hidden_field('Phone', $order->customer['telephone']) .
                                tep_draw_hidden_field('Email', $order->customer['email_address']) .
                                tep_draw_hidden_field('Sname', $order->delivery['firstname'] . ' ' . $order->delivery['lastname']) .
                                tep_draw_hidden_field('Saddr1', $order->delivery['street_address']) .
                                tep_draw_hidden_field('Scity', $order->delivery['city']) .
                                tep_draw_hidden_field('Sstate', $order->delivery['state']) .
                                tep_draw_hidden_field('Szip', $order->delivery['postcode']) .
                                tep_draw_hidden_field('Scountry', $order->delivery['country']['iso_code_2']) .
                                tep_draw_hidden_field('ChargeType', $transaction_type) .
                                tep_draw_hidden_field('Result', $transaction_mode) .
                                tep_draw_hidden_field('IP', $HTTP_SERVER_VARS['REMOTE_ADDR']);

      if (MODULE_PAYMENT_PSIGATE_INPUT_MODE == 'Local') {
        $process_button_string .= tep_draw_hidden_field('CardNumber', $this->cc_card_number) .
                                  tep_draw_hidden_field('ExpMonth', $this->cc_expiry_month) .
                                  tep_draw_hidden_field('ExpYear', substr($this->cc_expiry_year, -2));
      }

      return $process_button_string;
    }

    function before_process() {
      return false;
    }

    function after_process() {
      return false;
    }

    function get_error() {
      global $HTTP_GET_VARS;

      if (isset($HTTP_GET_VARS['ErrMsg']) && tep_not_null($HTTP_GET_VARS['ErrMsg'])) {
        $error = stripslashes(urldecode($HTTP_GET_VARS['ErrMsg']));
      } elseif (isset($HTTP_GET_VARS['Err']) && tep_not_null($HTTP_GET_VARS['Err'])) {
        $error = stripslashes(urldecode($HTTP_GET_VARS['Err']));
      } elseif (isset($HTTP_GET_VARS['error']) && tep_not_null($HTTP_GET_VARS['error'])) {
        $error = stripslashes(urldecode($HTTP_GET_VARS['error']));
      } else {
        $error = MODULE_PAYMENT_PSIGATE_TEXT_ERROR_MESSAGE;
      }

      return array('title' => MODULE_PAYMENT_PSIGATE_TEXT_ERROR,
                   'error' => $error);
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PSIGATE_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable PSiGate Module', 'MODULE_PAYMENT_PSIGATE_STATUS', 'True', 'Do you want to accept PSiGate payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant ID', 'MODULE_PAYMENT_PSIGATE_MERCHANT_ID', 'teststorewithcard', 'Merchant ID used for the PSiGate service', '6', '2', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Mode', 'MODULE_PAYMENT_PSIGATE_TRANSACTION_MODE', 'Always Good', 'Transaction mode to use for the PSiGate service', '6', '3', 'tep_cfg_select_option(array(\'Production\', \'Always Good\', \'Always Duplicate\', \'Always Decline\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Type', 'MODULE_PAYMENT_PSIGATE_TRANSACTION_TYPE', 'PreAuth', 'Transaction type to use for the PSiGate service', '6', '4', 'tep_cfg_select_option(array(\'Sale\', \'PreAuth\', \'PostAuth\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Credit Card Collection', 'MODULE_PAYMENT_PSIGATE_INPUT_MODE', 'Local', 'Should the credit card details be collected locally or remotely at PSiGate?', '6', '5', 'tep_cfg_select_option(array(\'Local\', \'Remote\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Currency', 'MODULE_PAYMENT_PSIGATE_CURRENCY', 'USD', 'The currency to use for credit card transactions', '6', '6', 'tep_cfg_select_option(array(\'CAD\', \'USD\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_PSIGATE_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_PSIGATE_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_PSIGATE_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_PSIGATE_STATUS', 'MODULE_PAYMENT_PSIGATE_MERCHANT_ID', 'MODULE_PAYMENT_PSIGATE_TRANSACTION_MODE', 'MODULE_PAYMENT_PSIGATE_TRANSACTION_TYPE', 'MODULE_PAYMENT_PSIGATE_INPUT_MODE', 'MODULE_PAYMENT_PSIGATE_CURRENCY', 'MODULE_PAYMENT_PSIGATE_ZONE', 'MODULE_PAYMENT_PSIGATE_ORDER_STATUS_ID', 'MODULE_PAYMENT_PSIGATE_SORT_ORDER');
    }
  }
?>
