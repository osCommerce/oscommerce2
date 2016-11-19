<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class ot_subtotal {
    var $title, $output;

    function __construct() {
      $this->code = 'ot_subtotal';
      $this->title = OSCOM::getDef('module_order_total_subtotal_title');
      $this->description = OSCOM::getDef('module_order_total_subtotal_description');
      $this->enabled = defined('MODULE_ORDER_TOTAL_SUBTOTAL_STATUS') && (MODULE_ORDER_TOTAL_SUBTOTAL_STATUS == 'true') ? true : false;
      $this->sort_order = defined('MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER') && ((int)MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER > 0) ? (int)MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER : 0;

      $this->output = array();
    }

    function process() {
      global $order, $currencies;

      $this->output[] = array('title' => $this->title . ':',
                              'text' => $currencies->format($order->info['subtotal'], true, $order->info['currency'], $order->info['currency_value']),
                              'value' => $order->info['subtotal']);
    }

    function check() {
      return defined('MODULE_ORDER_TOTAL_SUBTOTAL_STATUS');
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_SUBTOTAL_STATUS', 'MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Display Sub-Total',
        'configuration_key' => 'MODULE_ORDER_TOTAL_SUBTOTAL_STATUS',
        'configuration_value' => 'true',
        'configuration_description' => 'Do you want to display the order sub-total cost?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER',
        'configuration_value' => '1',
        'configuration_description' => 'Sort order of display. Lowest is displayed first.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }
  }
?>
