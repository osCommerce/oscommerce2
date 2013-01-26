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
    var $linespererrors = 1;
    var $enabled = false;

    function d_latest_errors() {
      $this->title = MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_DESCRIPTION . '<br />' . tep_get_php_error_file();

      if ( defined('MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_STATUS') ) {
        $this->linespererrors = MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_LINES;
        $this->sort_order = MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_STATUS == 'True');
      }
    }

    function getOutput() {

      $output = '<table border="0" width="100%" cellspacing="0" cellpadding="4">' .
                '  <tr class="dataTableHeadingRow">' .
                '    <td class="dataTableHeadingContent">' . MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_TITLE . '</td>' .
                '    <td class="dataTableHeadingContent" align="right">' . MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_DATE . '</td>' .
                '  </tr>';
      $messages = array();
      if ( file_exists(STORE_PHP_ERROR_LOG) ) {
        $messages = file(STORE_PHP_ERROR_LOG);

        $item = array('fileSize' => filesize(STORE_PHP_ERROR_LOG),
                      'messages' => ( (sizeof($messages) <= $this->linespererrors) ? 0 : (int)((sizeof($messages)) / $this->linespererrors) ),
                      'lastUpdate' => filemtime(STORE_PHP_ERROR_LOG));

        $output .= '  <tr class="dataTableRow" onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);">' .
                       '    <td class="dataTableContent">' . sprintf(TEXT_ERROR_ITEM, $item['fileSize'], $item['messages']) . '</td>' .
                       '    <td class="dataTableContent" align="right" style="white-space: nowrap;">' . date("Y F d H:i:s", $item['lastUpdate']) . '</td>' .
                       '  </tr>';
        $output .= '  <tr class="dataTableRow">' .
                   '    <td class="dataTableContent" colspan="2">' .
                   '       <div class="' . ($item['fileSize'] == 0 ? 'secSuccess' : 'secWarning') . '"><p class="smallText"><a href="' . tep_href_link('error_log.php') . '">' . MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_LINK . '</a></p>' .
                   '       </div></td>' .
                   '  </tr>';
      } else {

        $output .= '  <tr class="dataTableRow">' .
                   '    <td class="dataTableContent" align="right" colspan="2"><div class="secwarning"><p class="smallText">' . MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_FILE_NOT_FOUND . '</p></div></td>' .
                   '  </tr>';
      }
        $output .= '</table>';

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
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Lines/Error', 'MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_LINES', '1', 'How many lines generated per errors?', '6', '2', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '3', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order) VALUES ('PHP Error Log Destination', 'STORE_PHP_ERROR_LOG', '/var/log/www/tep/php_errors.log', 'Directory and filename of the php error log', '10', '6')");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_STATUS', 'MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_LINES', 'MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_SORT_ORDER', 'STORE_PHP_ERROR_LOG');
    }
  }

  function tep_get_php_error_file() {
    return ini_get("error_log");
  }
?>
