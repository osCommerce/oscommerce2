<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class ot_loworderfee {
    var $title, $output;

    function __construct() {
      $this->code = 'ot_loworderfee';
      $this->title = OSCOM::getDef('module_order_total_loworderfee_title');
      $this->description = OSCOM::getDef('module_order_total_loworderfee_description');
      $this->enabled = defined('MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS') && (MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS == 'true') ? true : false;
      $this->sort_order = defined('MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER') && ((int)MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER > 0) ? (int)MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER : 0;

      $this->output = array();
    }

    function process() {
      global $order, $currencies;

      if (MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE == 'true') {
        switch (MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION) {
          case 'national':
            if ($order->delivery['country_id'] == STORE_COUNTRY) $pass = true; break;
          case 'international':
            if ($order->delivery['country_id'] != STORE_COUNTRY) $pass = true; break;
          case 'both':
            $pass = true; break;
          default:
            $pass = false; break;
        }

        if ( ($pass == true) && ( ($order->info['total'] - $order->info['shipping_cost']) < MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER) ) {
          $tax = tep_get_tax_rate(MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);
          $tax_description = tep_get_tax_description(MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);

          $order->info['tax'] += tep_calculate_tax(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, $tax);
          $order->info['tax_groups']["$tax_description"] += tep_calculate_tax(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, $tax);
          $order->info['total'] += MODULE_ORDER_TOTAL_LOWORDERFEE_FEE + tep_calculate_tax(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, $tax);

          $this->output[] = array('title' => $this->title . ':',
                                  'text' => $currencies->format(tep_add_tax(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, $tax), true, $order->info['currency'], $order->info['currency_value']),
                                  'value' => tep_add_tax(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, $tax));
        }
      }
    }

    function check() {
      return defined('MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS');
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS', 'MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER', 'MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE', 'MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER', 'MODULE_ORDER_TOTAL_LOWORDERFEE_FEE', 'MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION', 'MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Display Low Order Fee',
        'configuration_key' => 'MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS',
        'configuration_value' => 'true',
        'configuration_description' => 'Do you want to display the low order fee?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER',
        'configuration_value' => '4',
        'configuration_description' => 'Sort order of display. Lowest is displayed first.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Allow Low Order Fee',
        'configuration_key' => 'MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE',
        'configuration_value' => 'false',
        'configuration_description' => 'Do you want to allow low order fees?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Order Fee For Orders Under',
        'configuration_key' => 'MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER',
        'configuration_value' => '50',
        'configuration_description' => 'Add the low order fee to orders under this amount.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'use_function' => 'currencies->format',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Order Fee',
        'configuration_key' => 'MODULE_ORDER_TOTAL_LOWORDERFEE_FEE',
        'configuration_value' => '5',
        'configuration_description' => 'Low order fee.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'use_function' => 'currencies->format',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Attach Low Order Fee On Orders Made',
        'configuration_key' => 'MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION',
        'configuration_value' => 'both',
        'configuration_description' => 'Attach low order fee for orders sent to the set destination.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'national\', \'international\', \'both\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Tax Class',
        'configuration_key' => 'MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS',
        'configuration_value' => '0',
        'configuration_description' => 'Use the following tax class on the low order fee.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'use_function' => 'tep_get_tax_class_title',
        'set_function' => 'tep_cfg_pull_down_tax_classes(',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }
  }
?>
