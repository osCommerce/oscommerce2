<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class cod {
    var $code, $title, $description, $enabled;

    function __construct() {
      global $order;

      $this->code = 'cod';
      $this->title = OSCOM::getDef('module_payment_cod_text_title');
      $this->description = OSCOM::getDef('module_payment_cod_text_description');
      $this->sort_order = defined('MODULE_PAYMENT_COD_SORT_ORDER') ? MODULE_PAYMENT_COD_SORT_ORDER : 0;
      $this->enabled = defined('MODULE_PAYMENT_COD_STATUS') && (MODULE_PAYMENT_COD_STATUS == 'True') ? true : false;
      $this->order_status = defined('MODULE_PAYMENT_COD_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_COD_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_COD_ORDER_STATUS_ID : 0;

      if ( $this->enabled === true ) {
        if ( isset($order) && is_object($order) ) {
          $this->update_status();
        }
      }
    }

    function update_status() {
      global $order;

      $OSCOM_Db = Registry::get('Db');

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_COD_ZONE > 0) ) {
        $check_flag = false;
        $Qcheck = $OSCOM_Db->get('zones_to_geo_zones', 'zone_id', ['geo_zone_id' => MODULE_PAYMENT_COD_ZONE, 'zone_country_id' => $order->delivery['country']['id']], 'zone_id');
        while ($Qcheck->fetch()) {
          if ($Qcheck->valueInt('zone_id') < 1) {
            $check_flag = true;
            break;
          } elseif ($Qcheck->valueInt('zone_id') == $order->delivery['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }

// disable the module if the order only contains virtual products
      if ($this->enabled == true) {
        if ($order->content_type == 'virtual') {
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
      return defined('MODULE_PAYMENT_COD_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Cash On Delivery Module',
        'configuration_key' => 'MODULE_PAYMENT_COD_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to accept Cash On Delevery payments?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Payment Zone',
        'configuration_key' => 'MODULE_PAYMENT_COD_ZONE',
        'configuration_value' => '0',
        'configuration_description' => 'If a zone is selected, only enable this payment method for that zone.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'use_function' => 'tep_get_zone_class_title',
        'set_function' => 'tep_cfg_pull_down_zone_classes(',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_PAYMENT_COD_SORT_ORDER',
        'configuration_value' => '0',
        'configuration_description' => 'Sort order of display. Lowest is displayed first.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Set Order Status',
        'configuration_key' => 'MODULE_PAYMENT_COD_ORDER_STATUS_ID',
        'configuration_value' => '0',
        'configuration_description' => 'Set the status of orders made with this payment module to this value',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_pull_down_order_statuses(',
        'use_function' => 'tep_get_order_status_name',
        'date_added' => 'now()'
      ]);
   }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    function keys() {
      return array('MODULE_PAYMENT_COD_STATUS', 'MODULE_PAYMENT_COD_ZONE', 'MODULE_PAYMENT_COD_ORDER_STATUS_ID', 'MODULE_PAYMENT_COD_SORT_ORDER');
    }
  }
?>
