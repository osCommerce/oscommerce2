<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/


  class d_latest_errors {
    var $code = 'd_latest_errors';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_latest_errors() {
      $this->title = MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_DESCRIPTION . '<br />' . tep_get_php_error_file();

      if ( defined('MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_STATUS == 'True');
      }
    }

    function getOutput() {

      $messages = array();
      if ( file_exists(STORE_ERROR_LOG_FILE) ) {
        $messages = file(STORE_ERROR_LOG_FILE);
        $output = '<table border="0" width="100%" cellspacing="0" cellpadding="4">' .
                '  <tr class="dataTableHeadingRow">' .
                '    <td class="dataTableHeadingContent">' . MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_TITLE . '</td>' .
                '    <td class="dataTableHeadingContent" align="right">' . MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_DATE . '</td>' .
                '  </tr>';
        $item = array('fileSize' => filesize(STORE_ERROR_LOG_FILE),
                      'messages' => ( (sizeof($messages) <= 1) ? 0 : (int)(sizeof($messages)) ),
                      'lastUpdate' => filemtime(STORE_ERROR_LOG_FILE));

        $output .= '  <tr class="dataTableRow">' .
                       '    <td class="dataTableContent">' . sprintf(TEXT_ERROR_ITEM, $item['fileSize'], $item['messages']) . '</td>' .
                       '    <td class="dataTableContent" align="right" style="white-space: nowrap;">' . date("Y F d H:i:s", $item['lastUpdate']) . '</td>' .
                       '  </tr>';
        $output .= '</table>';
        $output .= '       <div class="' . ($item['fileSize'] == 0 ? 'secSuccess' : 'secWarning') . '"><p class="smallText"><a href="' . tep_href_link('error_log.php') . '">' . MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_LINK . '</a></p>' .
                   '       </div>';

      } else {
        $output = '  <div class="secWarning"><p class="smallText">' . MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_FILE_NOT_FOUND . '</p></div>';
      }

      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Latest Errors Module', 'MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_STATUS', 'True', 'Do you want to show the latest webshop PHP errors on the dashboard?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Cut date', 'STORE_ERROR_LOG_CUT_DATE', 'True', 'Do you want to cut datetime from begening of the line?', '6', '2', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '3', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order) VALUES ('PHP Error Log Destination', 'STORE_ERROR_LOG_FILE', '/var/log/www/tep/php_errors.log', 'Directory and filename of the php error log', '10', '6')");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_STATUS', 'STORE_ERROR_LOG_CUT_DATE', 'MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_SORT_ORDER', 'STORE_ERROR_LOG_FILE');
    }
  }

  function tep_get_php_error_file() {
    return ini_get("error_log");
  }
?>
