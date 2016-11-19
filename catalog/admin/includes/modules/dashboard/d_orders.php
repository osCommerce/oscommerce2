<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\DateTime;
  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class d_orders {
    var $code = 'd_orders';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_orders() {
      $this->title = OSCOM::getDef('module_admin_dashboard_orders_title');
      $this->description = OSCOM::getDef('module_admin_dashboard_orders_description');

      if ( defined('MODULE_ADMIN_DASHBOARD_ORDERS_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_ORDERS_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_ORDERS_STATUS == 'True');
      }
    }

    function getOutput() {
      $OSCOM_Db = Registry::get('Db');
      $OSCOM_Language = Registry::get('Language');

      $output = '<table class="table table-hover">
                   <thead>
                     <tr class="info">
                       <th>' . OSCOM::getDef('module_admin_dashboard_orders_title') . '</th>
                       <th>' . OSCOM::getDef('module_admin_dashboard_orders_total') . '</th>
                       <th>' . OSCOM::getDef('module_admin_dashboard_orders_date') . '</th>
                       <th>' . OSCOM::getDef('module_admin_dashboard_orders_order_status') . '</th>
                     </tr>
                   </thead>
                   <tbody>';

      $Qorders = $OSCOM_Db->get([
        'orders o',
        'orders_total ot',
        'orders_status s'
      ], [
        'o.orders_id',
        'o.customers_name',
        'greatest(o.date_purchased, ifnull(o.last_modified, 0)) as date_last_modified',
        's.orders_status_name',
        'ot.text as order_total'
      ], [
        'o.orders_id' => [
          'rel' => 'ot.orders_id'
        ],
        'ot.class' => 'ot_total',
        'o.orders_status' => [
          'rel' => 's.orders_status_id'
        ],
        's.language_id' => $OSCOM_Language->getId()
      ], 'date_last_modified desc', 6);

      while ($Qorders->fetch()) {
        $output .= '    <tr>
                          <td><a href="' . OSCOM::link(FILENAME_ORDERS, 'oID=' . $Qorders->valueInt('orders_id') . '&action=edit') . '">' . $Qorders->valueProtected('customers_name') . '</a></td>
                          <td>' . strip_tags($Qorders->value('order_total')) . '</td>
                          <td>' . DateTime::toShort($Qorders->value('date_last_modified')) . '</td>
                          <td>' . $Qorders->value('orders_status_name') . '</td>
                        </tr>';
      }

      $output .= '  </tbody>
                  </table>';

      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_ORDERS_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Orders Module',
        'configuration_key' => 'MODULE_ADMIN_DASHBOARD_ORDERS_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to show the latest orders on the dashboard?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_ADMIN_DASHBOARD_ORDERS_SORT_ORDER',
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
      return array('MODULE_ADMIN_DASHBOARD_ORDERS_STATUS', 'MODULE_ADMIN_DASHBOARD_ORDERS_SORT_ORDER');
    }
  }
?>
