<?php
/*
  $Id: secpay.php,v 1.31 2003/01/29 19:57:15 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class secpay {
    var $code, $title, $description, $enabled;

// class constructor
    function secpay() {
      global $order;

      $this->code = 'secpay';
      $this->title = MODULE_PAYMENT_SECPAY_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_SECPAY_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_SECPAY_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_SECPAY_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_SECPAY_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_SECPAY_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

      $this->form_action_url = 'https://www.secpay.com/java-bin/ValCard';
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_SECPAY_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_SECPAY_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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
                   'module' => $this->title);
    }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {
      return false;
    }

    function process_button() {
      global $order, $currencies, $currency;

      switch (MODULE_PAYMENT_SECPAY_CURRENCY) {
        case 'Default Currency':
          $sec_currency = DEFAULT_CURRENCY;
          break;
        case 'Any Currency':
        default:
          $sec_currency = $currency;
          break;
      }

      switch (MODULE_PAYMENT_SECPAY_TEST_STATUS) {
        case 'Always Fail':
          $test_status = 'false';
          break;
        case 'Production':
          $test_status = 'live';
          break;
        case 'Always Successful':
        default:
          $test_status = 'true';
          break;
      }

      $process_button_string = tep_draw_hidden_field('merchant', MODULE_PAYMENT_SECPAY_MERCHANT_ID) .
                               tep_draw_hidden_field('trans_id', STORE_NAME . date('Ymdhis')) .
                               tep_draw_hidden_field('amount', number_format($order->info['total'] * $currencies->get_value($sec_currency), $currencies->currencies[$sec_currency]['decimal_places'], '.', '')) .
                               tep_draw_hidden_field('bill_name', $order->billing['firstname'] . ' ' . $order->billing['lastname']) .
                               tep_draw_hidden_field('bill_addr_1', $order->billing['street_address']) .
                               tep_draw_hidden_field('bill_addr_2', $order->billing['suburb']) .
                               tep_draw_hidden_field('bill_city', $order->billing['city']) .
                               tep_draw_hidden_field('bill_state', $order->billing['state']) .
                               tep_draw_hidden_field('bill_post_code', $order->billing['postcode']) .
                               tep_draw_hidden_field('bill_country', $order->billing['country']['title']) .
                               tep_draw_hidden_field('bill_tel', $order->customer['telephone']) .
                               tep_draw_hidden_field('bill_email', $order->customer['email_address']) .
                               tep_draw_hidden_field('ship_name', $order->delivery['firstname'] . ' ' . $order->delivery['lastname']) .
                               tep_draw_hidden_field('ship_addr_1', $order->delivery['street_address']) .
                               tep_draw_hidden_field('ship_addr_2', $order->delivery['suburb']) .
                               tep_draw_hidden_field('ship_city', $order->delivery['city']) .
                               tep_draw_hidden_field('ship_state', $order->delivery['state']) .
                               tep_draw_hidden_field('ship_post_code', $order->delivery['postcode']) .
                               tep_draw_hidden_field('ship_country', $order->delivery['country']['title']) .
                               tep_draw_hidden_field('currency', $sec_currency) .
                               tep_draw_hidden_field('callback', tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', false) . ';' . tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code, 'SSL', false)) .
                               tep_draw_hidden_field(tep_session_name(), tep_session_id()) .
                               tep_draw_hidden_field('options', 'test_status=' . $test_status . ',dups=false,cb_post=true,cb_flds=' . tep_session_name());

      return $process_button_string;
    }

    function before_process() {
      global $HTTP_POST_VARS;

      if ($HTTP_POST_VARS['valid'] == 'true') {
        if ($remote_host = getenv('REMOTE_HOST')) {
          if ($remote_host != 'secpay.com') {
            $remote_host = gethostbyaddr($remote_host);
          }
          if ($remote_host != 'secpay.com') {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, tep_session_name() . '=' . $HTTP_POST_VARS[tep_session_name()] . '&payment_error=' . $this->code, 'SSL', false, false));
          }
        } else {
          tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, tep_session_name() . '=' . $HTTP_POST_VARS[tep_session_name()] . '&payment_error=' . $this->code, 'SSL', false, false));
        }
      }
    }

    function after_process() {
      return false;
    }

    function get_error() {
      global $HTTP_GET_VARS;

      if (isset($HTTP_GET_VARS['message']) && (strlen($HTTP_GET_VARS['message']) > 0)) {
        $error = stripslashes(urldecode($HTTP_GET_VARS['message']));
      } else {
        $error = MODULE_PAYMENT_SECPAY_TEXT_ERROR_MESSAGE;
      }

      return array('title' => MODULE_PAYMENT_SECPAY_TEXT_ERROR,
                   'error' => $error);
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_SECPAY_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable SECpay Module', 'MODULE_PAYMENT_SECPAY_STATUS', 'True', 'Do you want to accept SECPay payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant ID', 'MODULE_PAYMENT_SECPAY_MERCHANT_ID', 'secpay', 'Merchant ID to use for the SECPay service', '6', '2', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Currency', 'MODULE_PAYMENT_SECPAY_CURRENCY', 'Any Currency', 'The currency to use for credit card transactions', '6', '3', 'tep_cfg_select_option(array(\'Any Currency\', \'Default Currency\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Mode', 'MODULE_PAYMENT_SECPAY_TEST_STATUS', 'Always Successful', 'Transaction mode to use for the SECPay service', '6', '4', 'tep_cfg_select_option(array(\'Always Successful\', \'Always Fail\', \'Production\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_SECPAY_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_SECPAY_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_SECPAY_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_SECPAY_STATUS', 'MODULE_PAYMENT_SECPAY_MERCHANT_ID', 'MODULE_PAYMENT_SECPAY_CURRENCY', 'MODULE_PAYMENT_SECPAY_TEST_STATUS', 'MODULE_PAYMENT_SECPAY_ZONE', 'MODULE_PAYMENT_SECPAY_ORDER_STATUS_ID', 'MODULE_PAYMENT_SECPAY_SORT_ORDER');
    }
  }
?>
