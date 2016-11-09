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

  class d_admin_logins {
    var $code = 'd_admin_logins';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_admin_logins() {
      $this->title = OSCOM::getDef('module_admin_dashboard_admin_logins_title');
      $this->description = OSCOM::getDef('module_admin_dashboard_admin_logins_description');

      if ( defined('MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_STATUS == 'True');
      }
    }

    function getOutput() {
      $OSCOM_Db = Registry::get('Db');

      $output = '<table class="table table-hover">
                   <thead>
                     <tr class="info">
                       <th>' . OSCOM::getDef('module_admin_dashboard_admin_logins_title') . '</th>
                       <th class="text-right">' . OSCOM::getDef('module_admin_dashboard_admin_logins_date') . '</th>
                     </tr>
                   </thead>
                   <tbody>';

      $Qlogins = $OSCOM_Db->get('action_recorder', [
        'id',
        'user_name',
        'success',
        'date_added'
      ], [
        'module' => 'ar_admin_login'
      ], 'date_added desc', 6);

      while ($Qlogins->fetch()) {
        $output .= '    <tr>
                          <td><i class="fa fa-' . (($Qlogins->valueInt('success') === 1) ? 'check text-success' : 'times text-danger') . '"></i>&nbsp;<a href="' . OSCOM::link(FILENAME_ACTION_RECORDER, 'module=ar_admin_login&aID=' . $Qlogins->valueInt('id')) . '">' . $Qlogins->valueProtected('user_name') . '</a></td>
                          <td class="text-right">' . DateTime::toShort($Qlogins->value('date_added')) . '</td>
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
      return defined('MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Administrator Logins Module',
        'configuration_key' => 'MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to show the latest administrator logins on the dashboard?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_SORT_ORDER',
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
      return array('MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_STATUS', 'MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_SORT_ORDER');
    }
  }
?>
