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

  class d_customers {
    var $code = 'd_customers';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_customers() {
      $this->title = OSCOM::getDef('module_admin_dashboard_customers_title');
      $this->description = OSCOM::getDef('module_admin_dashboard_customers_description');

      if ( defined('MODULE_ADMIN_DASHBOARD_CUSTOMERS_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_CUSTOMERS_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_CUSTOMERS_STATUS == 'True');
      }
    }

    function getOutput() {
      $OSCOM_Db = Registry::get('Db');

      $output = '<table class="table table-hover">
                   <thead>
                     <tr class="info">
                       <th>' . OSCOM::getDef('module_admin_dashboard_customers_title') . '</th>
                       <th class="text-right">' . OSCOM::getDef('module_admin_dashboard_customers_date') . '</th>
                     </tr>
                   </thead>
                   <tbody>';

      $Qcustomers = $OSCOM_Db->get([
        'customers c',
        'customers_info ci'
      ], [
        'c.customers_id',
        'c.customers_lastname',
        'c.customers_firstname',
        'ci.customers_info_date_account_created'
      ], [
        'c.customers_id' => [
          'rel' => 'ci.customers_info_id'
        ]
      ], 'ci.customers_info_date_account_created desc', 6);

      while ($Qcustomers->fetch()) {
        $output .= '    <tr>
                          <td><a href="' . OSCOM::link(FILENAME_CUSTOMERS, 'cID=' . $Qcustomers->valueInt('customers_id') . '&action=edit') . '">' . HTML::outputProtected($Qcustomers->value('customers_firstname') . ' ' . $Qcustomers->value('customers_lastname')) . '</a></td>
                          <td class="text-right">' . DateTime::toShort($Qcustomers->value('customers_info_date_account_created')) . '</td>
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
      return defined('MODULE_ADMIN_DASHBOARD_CUSTOMERS_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Customers Module',
        'configuration_key' => 'MODULE_ADMIN_DASHBOARD_CUSTOMERS_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to show the newest customers on the dashboard?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_ADMIN_DASHBOARD_CUSTOMERS_SORT_ORDER',
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
      return array('MODULE_ADMIN_DASHBOARD_CUSTOMERS_STATUS', 'MODULE_ADMIN_DASHBOARD_CUSTOMERS_SORT_ORDER');
    }
  }
?>
