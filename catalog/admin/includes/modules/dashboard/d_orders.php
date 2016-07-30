<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

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
      $this->title = MODULE_ADMIN_DASHBOARD_ORDERS_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_ORDERS_DESCRIPTION;

      if ( defined('MODULE_ADMIN_DASHBOARD_ORDERS_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_ORDERS_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_ORDERS_STATUS == 'True');
      }
    }

    function getOutput() {
      $OSCOM_Db = Registry::get('Db');

      $output = '<table border="0" width="100%" cellspacing="0" cellpadding="4">' .
                '  <tr class="dataTableHeadingRow">' .
                '    <td class="dataTableHeadingContent">' . MODULE_ADMIN_DASHBOARD_ORDERS_TITLE . '</td>' .
                '    <td class="dataTableHeadingContent">' . MODULE_ADMIN_DASHBOARD_ORDERS_TOTAL . '</td>' .
                '    <td class="dataTableHeadingContent">' . MODULE_ADMIN_DASHBOARD_ORDERS_DATE . '</td>' .
                '    <td class="dataTableHeadingContent">' . MODULE_ADMIN_DASHBOARD_ORDERS_ORDER_STATUS . '</td>' .
                '  </tr>';

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
        's.language_id' => (int)$_SESSION['languages_id']
      ], 'date_last_modified desc', 6);

      while ($Qorders->fetch()) {
        $output .= '  <tr class="dataTableRow" onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);">' .
                   '    <td class="dataTableContent"><a href="' . OSCOM::link(FILENAME_ORDERS, 'oID=' . $Qorders->valueInt('orders_id') . '&action=edit') . '">' . $Qorders->valueProtected('customers_name') . '</a></td>' .
                   '    <td class="dataTableContent">' . strip_tags($Qorders->value('order_total')) . '</td>' .
                   '    <td class="dataTableContent">' . tep_date_short($Qorders->value('date_last_modified')) . '</td>' .
                   '    <td class="dataTableContent">' . $Qorders->value('orders_status_name') . '</td>' .
                   '  </tr>';
      }

      $output .= '</table>';

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
