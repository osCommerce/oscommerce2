<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Cache;
  use OSC\OM\DateTime;
  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class d_version_check {
    var $code = 'd_version_check';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_version_check() {
      $this->title = OSCOM::getDef('module_admin_dashboard_version_check_title');
      $this->description = OSCOM::getDef('module_admin_dashboard_version_check_description');

      if ( defined('MODULE_ADMIN_DASHBOARD_VERSION_CHECK_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_VERSION_CHECK_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_VERSION_CHECK_STATUS == 'True');
      }
    }

    function getOutput() {
      $current_version = OSCOM::getVersion();
      $new_version = false;

      $VersionCache = new Cache('core_version_check');

      if ($VersionCache->exists()) {
        $date_last_checked = DateTime::toShort(date('Y-m-d H:i:s', $VersionCache->getTime()), true);

        $releases = $VersionCache->get();

        foreach ($releases as $version) {
          $version_array = explode('|', $version);

          if (version_compare($current_version, $version_array[0], '<')) {
            $new_version = true;
            break;
          }
        }
      } else {
        $date_last_checked = OSCOM::getDef('module_admin_dashboard_version_check_never');
      }

      $output = '<table class="table table-hover">
                   <thead>
                     <tr class="info">
                       <th>' . OSCOM::getDef('module_admin_dashboard_version_check_title') . '</th>
                       <th class="text-right">' . OSCOM::getDef('module_admin_dashboard_version_check_date') . '</th>
                     </tr>
                   </thead>
                   <tbody>';

      if ($new_version == true) {
        $output .= '    <tr class="success">
                          <td colspan="2">' . HTML::image(OSCOM::linkImage('icons/warning.gif'), OSCOM::getDef('icon_warning')) . '&nbsp;<strong>' . OSCOM::getDef('module_admin_dashboard_version_check_update_available') . '</strong></td>
                        </tr>';
      }

      $output .= '    <tr>
                        <td><a href="' . OSCOM::link('online_update.php') . '">' . OSCOM::getDef('module_admin_dashboard_version_check_check_now') . '</a></td>
                        <td class="text-right">' . $date_last_checked . '</td>
                      </tr>
                    </tbody>
                  </table>';

      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_VERSION_CHECK_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Version Check Module',
        'configuration_key' => 'MODULE_ADMIN_DASHBOARD_VERSION_CHECK_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to show the version check results on the dashboard?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_ADMIN_DASHBOARD_VERSION_CHECK_SORT_ORDER',
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
      return array('MODULE_ADMIN_DASHBOARD_VERSION_CHECK_STATUS', 'MODULE_ADMIN_DASHBOARD_VERSION_CHECK_SORT_ORDER');
    }
  }
?>
