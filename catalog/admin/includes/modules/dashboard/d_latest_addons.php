<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class d_latest_addons {
    var $code = 'd_latest_addons';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_latest_addons() {
      $this->title = MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_DESCRIPTION;

      if ( defined('MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_STATUS == 'True');
      }
    }

    function getOutput() {
      if (!class_exists('lastRSS')) {
        include(DIR_WS_CLASSES . 'rss.php');
      }

      $rss = new lastRSS;
      $rss->items_limit = 5;
      $rss->cache_dir = DIR_FS_CACHE;
      $rss->cache_time = 86400;
      $feed = $rss->get('http://feeds.feedburner.com/osCommerce_Contributions');

      $output = '<table border="0" width="100%" cellspacing="0" cellpadding="4">' .
                '  <tr class="dataTableHeadingRow">' .
                '    <td class="dataTableHeadingContent">' . MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_TITLE . '</td>' .
                '    <td class="dataTableHeadingContent" align="right">' . MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_DATE . '</td>' .
                '  </tr>';

      if (is_array($feed) && !empty($feed)) {
        foreach ($feed['items'] as $item) {
          $output .= '  <tr class="dataTableRow" onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);">' .
                     '    <td class="dataTableContent"><a href="' . $item['link'] . '" target="_blank">' . $item['title'] . '</a></td>' .
                     '    <td class="dataTableContent" align="right" style="white-space: nowrap;">' . date("F j, Y", strtotime($item['pubDate'])) . '</td>' .
                     '  </tr>';
        }
      } else {
        $output .= '  <tr class="dataTableRow">' .
                   '    <td class="dataTableContent" colspan="2">' . MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_FEED_ERROR . '</td>' .
                   '  </tr>';
      }

      $output .= '  <tr class="dataTableRow">' .
                 '    <td class="dataTableContent" align="right" colspan="2"><a href="http://addons.oscommerce.com" target="_blank">' . tep_image(DIR_WS_IMAGES . 'icon_oscommerce.png', MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_ICON_SITE) . '</a>&nbsp;<a href="http://feeds.feedburner.com/osCommerce_Contributions" target="_blank">' . tep_image(DIR_WS_IMAGES . 'icon_rss.png', MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_ICON_RSS) . '</a></td>' .
                 '  </tr>' .
                 '</table>';

      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Latest Add-Ons Module', 'MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_STATUS', 'True', 'Do you want to show the latest osCommerce Add-Ons on the dashboard?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_STATUS', 'MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_SORT_ORDER');
    }
  }
?>
