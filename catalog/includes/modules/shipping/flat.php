<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class flat {
    var $code, $title, $description, $icon, $enabled;

// class constructor
    function __construct() {
      global $order;

      $OSCOM_Db = Registry::get('Db');

      $this->code = 'flat';
      $this->title = OSCOM::getDef('module_shipping_flat_text_title');
      $this->description = OSCOM::getDef('module_shipping_flat_text_description');
      $this->sort_order = defined('MODULE_SHIPPING_FLAT_SORT_ORDER') ? (int)MODULE_SHIPPING_FLAT_SORT_ORDER : 0;
      $this->icon = '';
      $this->tax_class = defined('MODULE_SHIPPING_FLAT_TAX_CLASS') ? MODULE_SHIPPING_FLAT_TAX_CLASS : 0;
      $this->enabled = (defined('MODULE_SHIPPING_FLAT_STATUS') && (MODULE_SHIPPING_FLAT_STATUS == 'True') ? true : false);

      if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_FLAT_ZONE > 0) ) {
        $check_flag = false;
        $Qcheck = $OSCOM_Db->get('zones_to_geo_zones', 'zone_id', ['geo_zone_id' => MODULE_SHIPPING_FLAT_ZONE, 'zone_country_id' => $order->delivery['country']['id']], 'zone_id');
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
    }

// class methods
    function quote($method = '') {
      global $order;

      $this->quotes = array('id' => $this->code,
                            'module' => OSCOM::getDef('module_shipping_flat_text_title'),
                            'methods' => array(array('id' => $this->code,
                                                     'title' => OSCOM::getDef('module_shipping_flat_text_way'),
                                                     'cost' => MODULE_SHIPPING_FLAT_COST)));

      if ($this->tax_class > 0) {
        $this->quotes['tax'] = tep_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
      }

      if (tep_not_null($this->icon)) $this->quotes['icon'] = HTML::image($this->icon, $this->title);

      return $this->quotes;
    }

    function check() {
      return defined('MODULE_SHIPPING_FLAT_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Flat Shipping',
        'configuration_key' => 'MODULE_SHIPPING_FLAT_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to offer flat rate shipping?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Shipping Cost',
        'configuration_key' => 'MODULE_SHIPPING_FLAT_COST',
        'configuration_value' => '5.00',
        'configuration_description' => 'The shipping cost for all orders using this shipping method.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Tax Class',
        'configuration_key' => 'MODULE_SHIPPING_FLAT_TAX_CLASS',
        'configuration_value' => '0',
        'configuration_description' => 'Use the following tax class on the shipping fee.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'use_function' => 'tep_get_tax_class_title',
        'set_function' => 'tep_cfg_pull_down_tax_classes(',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Shipping Zone',
        'configuration_key' => 'MODULE_SHIPPING_FLAT_ZONE',
        'configuration_value' => '0',
        'configuration_description' => 'If a zone is selected, only enable this shipping method for that zone.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'use_function' => 'tep_get_zone_class_title',
        'set_function' => 'tep_cfg_pull_down_zone_classes(',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_SHIPPING_FLAT_SORT_ORDER',
        'configuration_value' => '0',
        'configuration_description' => 'Sort order of display. Lowest is displayed first.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    function keys() {
      return array('MODULE_SHIPPING_FLAT_STATUS', 'MODULE_SHIPPING_FLAT_COST', 'MODULE_SHIPPING_FLAT_TAX_CLASS', 'MODULE_SHIPPING_FLAT_ZONE', 'MODULE_SHIPPING_FLAT_SORT_ORDER');
    }
  }
?>
