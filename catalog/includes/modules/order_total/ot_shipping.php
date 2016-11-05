<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class ot_shipping {
    var $title, $output;

    function __construct() {
      $this->code = 'ot_shipping';
      $this->title = OSCOM::getDef('module_order_total_shipping_title');
      $this->description = OSCOM::getDef('module_order_total_shipping_description');
      $this->enabled = defined('MODULE_ORDER_TOTAL_SHIPPING_STATUS') && (MODULE_ORDER_TOTAL_SHIPPING_STATUS == 'true') ? true : false;
      $this->sort_order = defined('MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER') && ((int)MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER > 0) ? (int)MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER : 0;

      $this->output = array();
    }

    function process() {
      global $order, $currencies;

      if (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') {
      	$pass = false;
        switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
          case 'national':
            if ($order->delivery['country_id'] == STORE_COUNTRY) $pass = true; break;
          case 'international':
            if ($order->delivery['country_id'] != STORE_COUNTRY) $pass = true; break;
          case 'both':
            $pass = true; break;
          default:
            $pass = false; break;
        }

        if ( ($pass == true) && ( ($order->info['total'] - $order->info['shipping_cost']) >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
          $order->info['shipping_method'] = OSCOM::getDef('free_shipping_title');
          $order->info['total'] -= $order->info['shipping_cost'];
          $order->info['shipping_cost'] = 0;
        }
      }

      $module = substr($_SESSION['shipping']['id'], 0, strpos($_SESSION['shipping']['id'], '_'));

      if (tep_not_null($order->info['shipping_method'])) {
        if ($GLOBALS[$module]->tax_class > 0) {
          $shipping_tax = tep_get_tax_rate($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
          $shipping_tax_description = tep_get_tax_description($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);

          $order->info['tax'] += tep_calculate_tax($order->info['shipping_cost'], $shipping_tax);
          $order->info['tax_groups']["$shipping_tax_description"] += tep_calculate_tax($order->info['shipping_cost'], $shipping_tax);
          $order->info['total'] += tep_calculate_tax($order->info['shipping_cost'], $shipping_tax);

          if (DISPLAY_PRICE_WITH_TAX == 'true') $order->info['shipping_cost'] += tep_calculate_tax($order->info['shipping_cost'], $shipping_tax);
        }

        $this->output[] = array('title' => $order->info['shipping_method'] . ':',
                                'text' => $currencies->format($order->info['shipping_cost'], true, $order->info['currency'], $order->info['currency_value']),
                                'value' => $order->info['shipping_cost']);
      }
    }

    function check() {
      return defined('MODULE_ORDER_TOTAL_SHIPPING_STATUS');
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', 'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Display Shipping',
        'configuration_key' => 'MODULE_ORDER_TOTAL_SHIPPING_STATUS',
        'configuration_value' => 'true',
        'configuration_description' => 'Do you want to display the order shipping cost?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER',
        'configuration_value' => '2',
        'configuration_description' => 'Sort order of display. Lowest is displayed first.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Allow Free Shipping',
        'configuration_key' => 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING',
        'configuration_value' => 'false',
        'configuration_description' => 'Do you want to allow free shipping?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Free Shipping For Orders Over',
        'configuration_key' => 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER',
        'configuration_value' => '50',
        'configuration_description' => 'Provide free shipping for orders over the set amount.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'use_function' => 'currencies->format',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Provide Free Shipping For Orders Made',
        'configuration_key' => 'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION',
        'configuration_value' => 'national',
        'configuration_description' => 'Provide free shipping for orders sent to the set destination.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'national\', \'international\', \'both\'), ',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }
  }
?>
