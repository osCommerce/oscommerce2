<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class moneyorder {
    var $code, $title, $description, $enabled;

// class constructor
    function moneyorder() {
      global $order;

      $this->code = 'moneyorder';
      $this->title = MODULE_PAYMENT_MONEYORDER_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_MONEYORDER_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_MONEYORDER_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_MONEYORDER_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();
    
      $this->email_footer = MODULE_PAYMENT_MONEYORDER_TEXT_EMAIL_FOOTER;
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_MONEYORDER_ZONE > 0) ) {
        $check_flag = false;
        $check_query = osc_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_MONEYORDER_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
        while ($check = osc_db_fetch_array($check_query)) {
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
      return array('title' => MODULE_PAYMENT_MONEYORDER_TEXT_DESCRIPTION);
    }

    function process_button() {
      return false;
    }

    function before_process() {
      return false;
    }

    function after_process() {
      return false;
    }

    function get_error() {
      return false;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = osc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_MONEYORDER_STATUS'");
        $this->_check = osc_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Check/Money Order Module', 'MODULE_PAYMENT_MONEYORDER_STATUS', 'True', 'Do you want to accept Check/Money Order payments?', '6', '1', 'osc_cfg_select_option(array(\'True\', \'False\'), ', now());");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Make Payable to:', 'MODULE_PAYMENT_MONEYORDER_PAYTO', '', 'Who should payments be made payable to?', '6', '1', now());");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_MONEYORDER_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_MONEYORDER_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'osc_get_zone_class_title', 'osc_cfg_pull_down_zone_classes(', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'osc_cfg_pull_down_order_statuses(', 'osc_get_order_status_name', now())");
    }

    function remove() {
      osc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_MONEYORDER_STATUS', 'MODULE_PAYMENT_MONEYORDER_ZONE', 'MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID', 'MODULE_PAYMENT_MONEYORDER_SORT_ORDER', 'MODULE_PAYMENT_MONEYORDER_PAYTO');
    }
  }
?>
