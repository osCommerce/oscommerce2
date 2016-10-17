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

  class d_version_check {
    var $code = 'd_version_check';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_version_check() {
      $this->title = MODULE_ADMIN_DASHBOARD_VERSION_CHECK_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_VERSION_CHECK_DESCRIPTION;

      if ( defined('MODULE_ADMIN_DASHBOARD_VERSION_CHECK_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_VERSION_CHECK_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_VERSION_CHECK_STATUS == 'True');
      }
    }

    function getOutput() {
      $OSCOM_Cache = Registry::get('Cache');

      $current_version = OSCOM::getVersion();
      $new_version = false;

      if ($OSCOM_Cache->read('core_version_check')) {
        $date_last_checked = tep_datetime_short(date('Y-m-d H:i:s', $OSCOM_Cache->getTime('core_version_check')));

        $releases = $OSCOM_Cache->getCache();

        foreach ($releases as $version) {
          $version_array = explode('|', $version);

          if (version_compare($current_version, $version_array[0], '<')) {
            $new_version = true;
            break;
          }
        }
      } else {
        $date_last_checked = MODULE_ADMIN_DASHBOARD_VERSION_CHECK_NEVER;
      }

      $output = '<table class="table table-hover">
                   <thead>
                     <tr class="info">
                       <th>' . MODULE_ADMIN_DASHBOARD_VERSION_CHECK_TITLE . '</th>
                       <th class="text-right">' . MODULE_ADMIN_DASHBOARD_VERSION_CHECK_DATE . '</th>
                     </tr>
                   </thead>
                   <tbody>';

      if ($new_version == true) {
        $output .= '    <tr class="success">
                          <td colspan="2">' . HTML::image(OSCOM::linkImage('icons/warning.gif'), ICON_WARNING) . '&nbsp;<strong>' . MODULE_ADMIN_DASHBOARD_VERSION_CHECK_UPDATE_AVAILABLE . '</strong></td>
                        </tr>';
      }

      $output .= '    <tr>
                        <td><a href="' . OSCOM::link('online_update.php') . '">' . MODULE_ADMIN_DASHBOARD_VERSION_CHECK_CHECK_NOW . '</a></td>
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
