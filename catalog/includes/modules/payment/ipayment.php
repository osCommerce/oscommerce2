<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/

  class ipayment {
    var $code, $title, $description, $enabled;

// class constructor
    function ipayment() {
      global $order;

      $this->code = 'ipayment';
      $this->title = MODULE_PAYMENT_IPAYMENT_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_IPAYMENT_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_IPAYMENT_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_IPAYMENT_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_IPAYMENT_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_IPAYMENT_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_IPAYMENT_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

      $this->form_action_url = 'https://ipayment.de/merchant/' . MODULE_PAYMENT_IPAYMENT_ID . '/processor.php';
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_IPAYMENT_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_IPAYMENT_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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

      $confirmation = array('fields' => array(array('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_OWNER,
                                                    'field' => tep_draw_input_field('addr_name', $order->billing['firstname'] . ' ' . $order->billing['lastname'])),
                                              array('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_NUMBER,
                                                    'field' => tep_draw_input_field('cc_number')),
                                              array('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_EXPIRES,
                                                    'field' => tep_draw_pull_down_menu('cc_expdate_month', $expires_month) . '&nbsp;' . tep_draw_pull_down_menu('cc_expdate_year', $expires_year)),
                                              array('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_CHECKNUMBER,
                                                    'field' => tep_draw_input_field('cc_checkcode', '', 'size="4" maxlength="3"'))));

      return $confirmation;
    }

    function process_button() {
      global $order, $currencies, $currency;

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
                               tep_draw_hidden_field('trxuser_id', MODULE_PAYMENT_IPAYMENT_USER_ID) .
                               tep_draw_hidden_field('trxpassword', MODULE_PAYMENT_IPAYMENT_PASSWORD) .
//                               tep_draw_hidden_field('order_id', '') .
//                               tep_draw_hidden_field('strict_id_check', 1) .
                               tep_draw_hidden_field('from_ip', tep_get_ip_address()) .
                               tep_draw_hidden_field('trx_currency', $currency) .
                               tep_draw_hidden_field('trx_amount', number_format($order->info['total'] * 100, 0, '','')) .
                               tep_draw_hidden_field('trx_typ', 'auth') .
                               tep_draw_hidden_field('addr_email', $order->customer['email_address']) .
                               tep_draw_hidden_field('addr_street', $order->billing['street_address']) .
                               tep_draw_hidden_field('addr_city', $order->billing['city']) .
                               tep_draw_hidden_field('addr_zip', $order->billing['postcode']) .
                               tep_draw_hidden_field('addr_country', $order->billing['country']['iso_code_2']) .
                               tep_draw_hidden_field('addr_state', $zone_code) .
                               tep_draw_hidden_field('addr_telefon', $order->customer['telephone']) .
                               tep_draw_hidden_field('redirect_url', tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', true)) .
                               tep_draw_hidden_field('silent_error_url', tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code, 'SSL', true)) .
                               tep_draw_hidden_field('client_name', 'oscommerce') .
                               tep_draw_hidden_field('client_version', PROJECT_VERSION);

      if (tep_not_null(MODULE_PAYMENT_IPAYMENT_SECRET_HASH_PASSWORD)) {
        $process_button_string .= tep_draw_hidden_field('trx_securityhash', md5(MODULE_PAYMENT_IPAYMENT_USER_ID . number_format($order->info['total'] * 100, 0, '','') . $currency . MODULE_PAYMENT_IPAYMENT_PASSWORD . MODULE_PAYMENT_IPAYMENT_SECRET_HASH_PASSWORD));
      }

      return $process_button_string;
    }

    function before_process() {
      global $HTTP_GET_VARS;

      if (tep_not_null(MODULE_PAYMENT_IPAYMENT_SECRET_HASH_PASSWORD) && ($HTTP_GET_VARS['trx_securityhash'] != md5(MODULE_PAYMENT_IPAYMENT_USER_ID . number_format($order->info['total'] * 100, 0, '','') . $currency . MODULE_PAYMENT_IPAYMENT_PASSWORD . MODULE_PAYMENT_IPAYMENT_SECRET_HASH_PASSWORD))) {
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code));
      }

      if ($HTTP_GET_VARS['ret_errorcode'] != 0) {
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=' . tep_output_string_protected($HTTP_GET_VARS['ret_errormsg'])));
      }

      return false;
    }

    function after_process() {
      return false;
    }

    function get_error() {
      global $HTTP_GET_VARS;

      $error = array('title' => IPAYMENT_ERROR_HEADING,
                     'error' => ((isset($HTTP_GET_VARS['error'])) ? stripslashes(urldecode($HTTP_GET_VARS['error'])) : IPAYMENT_ERROR_MESSAGE));

      return $error;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_IPAYMENT_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable iPayment Module', 'MODULE_PAYMENT_IPAYMENT_STATUS', 'True', 'Do you want to accept iPayment payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Account Number', 'MODULE_PAYMENT_IPAYMENT_ID', '99999', 'The account number used for the iPayment service', '6', '2', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('User ID', 'MODULE_PAYMENT_IPAYMENT_USER_ID', '99999', 'The user ID for the iPayment service', '6', '3', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('User Password', 'MODULE_PAYMENT_IPAYMENT_PASSWORD', '0', 'The user password for the iPayment service', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Secret Hash Password', 'MODULE_PAYMENT_IPAYMENT_SECRET_HASH_PASSWORD', '', 'The secret hash password to validate transactions with', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_IPAYMENT_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_IPAYMENT_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_IPAYMENT_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_IPAYMENT_STATUS', 'MODULE_PAYMENT_IPAYMENT_ID', 'MODULE_PAYMENT_IPAYMENT_USER_ID', 'MODULE_PAYMENT_IPAYMENT_PASSWORD', 'MODULE_PAYMENT_IPAYMENT_SECRET_HASH_PASSWORD', 'MODULE_PAYMENT_IPAYMENT_ZONE', 'MODULE_PAYMENT_IPAYMENT_ORDER_STATUS_ID', 'MODULE_PAYMENT_IPAYMENT_SORT_ORDER');
    }
  }
?>
