<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  if (!class_exists('moneybookers')) {
    include(DIR_FS_CATALOG . 'includes/modules/payment/moneybookers.php');
  }

  class moneybookers_msc extends moneybookers {
    var $code, $title, $description, $enabled;

    var $_sid; // Moneybookers transaction session ID
    var $_mbcartID = 'cart_MoneybookersMSC_ID';
    var $_payment_method = 'MSC';
    var $_payment_method_image = '4b.gif';

// class constructor
    function moneybookers_msc() {
      global $order;

      $this->signature = 'moneybookers|moneybookers_msc|1.0|2.3';

      $this->code = 'moneybookers_msc';
      $this->title = MODULE_PAYMENT_MONEYBOOKERS_MSC_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_MONEYBOOKERS_MSC_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_MONEYBOOKERS_MSC_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_MONEYBOOKERS_MSC_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_MONEYBOOKERS_MSC_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_MONEYBOOKERS_MSC_PREPARE_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_MONEYBOOKERS_MSC_PREPARE_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

      if (defined('MODULE_PAYMENT_MONEYBOOKERS_IFRAME') && (MODULE_PAYMENT_MONEYBOOKERS_IFRAME == 'True')) {
        $this->form_action_url = osc_href_link('ext/modules/payment/moneybookers/checkout.php', '', 'SSL');
      } else {
        $this->form_action_url = 'https://www.moneybookers.com/app/payment.pl';
      }
    }

// class methods
    function update_status() {
      global $order;

      if (!defined('MODULE_PAYMENT_MONEYBOOKERS_STATUS')) {
        $this->enabled = false;
      } elseif ( ($this->enabled == true) && ((int)MODULE_PAYMENT_MONEYBOOKERS_MSC_ZONE > 0) ) {
        $check_flag = false;
        $check_query = osc_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_MONEYBOOKERS_MSC_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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

    function confirmation() {
      include(DIR_FS_CATALOG . 'includes/languages/' . $_SESSION['language'] . '/modules/payment/moneybookers.php');

      parent::confirmation();
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = osc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_MONEYBOOKERS_MSC_STATUS'");
        $this->_check = osc_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      if (!defined('MODULE_PAYMENT_MONEYBOOKERS_STATUS')) {
        osc_redirect(osc_href_link('ext/modules/payment/moneybookers/activation.php', 'action=coreRequired'));
      }

      $zone_id = 0;

      $zone_query = osc_db_query("select geo_zone_id from " . TABLE_GEO_ZONES . " where geo_zone_name = 'Moneybookers MC (Euro 6000/4B)'");
      if (osc_db_num_rows($zone_query)) {
        $zone = osc_db_fetch_array($zone_query);

        $zone_id = $zone['geo_zone_id'];
      } else {
        osc_db_query("insert into " . TABLE_GEO_ZONES . " values (null, 'Moneybookers MC (Euro 6000/4B)', 'The zone for the Moneybookers MC (Euro 6000/4B) payment module', null, now())");
        $zone_id = osc_db_insert_id();

        $country_query = osc_db_query("select countries_id from " . TABLE_COUNTRIES . " where countries_iso_code_2 = 'ES'");
        if (osc_db_num_rows($country_query)) {
          $country = osc_db_fetch_array($country_query);

          osc_db_query("insert into " . TABLE_ZONES_TO_GEO_ZONES . " values (null, '" . (int)$country['countries_id'] . "', 0, '" . (int)$zone_id . "', null, now())");
        }
      }

      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Moneybookers Mastercard (Euro 6000/4B)', 'MODULE_PAYMENT_MONEYBOOKERS_MSC_STATUS', 'False', 'Do you want to accept Moneybookers Mastercard (Euro 6000/4B) payments?', '6', '3', 'osc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_MONEYBOOKERS_MSC_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_MONEYBOOKERS_MSC_ZONE', '" . (int)$zone_id . "', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'osc_get_zone_class_title', 'osc_cfg_pull_down_zone_classes(', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Preparing Order Status', 'MODULE_PAYMENT_MONEYBOOKERS_MSC_PREPARE_ORDER_STATUS_ID', '" . MODULE_PAYMENT_MONEYBOOKERS_PREPARE_ORDER_STATUS_ID . "', 'Set the status of prepared orders made with this payment module to this value', '6', '0', 'osc_cfg_pull_down_order_statuses(', 'osc_get_order_status_name', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Transactions Order Status', 'MODULE_PAYMENT_MONEYBOOKERS_MSC_TRANSACTIONS_ORDER_STATUS_ID', '" . MODULE_PAYMENT_MONEYBOOKERS_TRANSACTIONS_ORDER_STATUS_ID . "', 'Set the status of callback transactions to this value', '6', '0', 'osc_cfg_pull_down_order_statuses(', 'osc_get_order_status_name', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_MONEYBOOKERS_MSC_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'osc_cfg_pull_down_order_statuses(', 'osc_get_order_status_name', now())");
    }

    function keys() {
      return array('MODULE_PAYMENT_MONEYBOOKERS_MSC_STATUS', 'MODULE_PAYMENT_MONEYBOOKERS_MSC_ZONE', 'MODULE_PAYMENT_MONEYBOOKERS_MSC_PREPARE_ORDER_STATUS_ID', 'MODULE_PAYMENT_MONEYBOOKERS_MSC_TRANSACTIONS_ORDER_STATUS_ID', 'MODULE_PAYMENT_MONEYBOOKERS_MSC_ORDER_STATUS_ID', 'MODULE_PAYMENT_MONEYBOOKERS_MSC_SORT_ORDER');
    }
  }
?>
