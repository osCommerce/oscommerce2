<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

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
        include('includes/classes/rss.php');
      }

      $rss = new lastRSS;
      $rss->items_limit = 5;
      $rss->cache_dir = OSCOM::BASE_DIR . 'Work/Cache/';
      $rss->cache_time = 86400;
      $feed = $rss->get('http://feeds.feedburner.com/osCommerce_Contributions', 'oscommerce_website-rss-addons');

      $output = '<table class="table table-hover">
                   <thead>
                     <tr class="info">
                       <th>' . MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_TITLE . '</th>
                       <th class="text-right">' . MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_DATE . '</th>
                     </tr>
                   </thead>
                   <tbody>';

      if (is_array($feed) && !empty($feed)) {
        foreach ($feed['items'] as $item) {
          $output .= '    <tr>
                            <td><a href="' . $item['link'] . '" target="_blank">' . $item['title'] . '</a></td>
                            <td class="text-right" style="white-space: nowrap;">' . date("F j, Y", strtotime($item['pubDate'])) . '</td>
                          </tr>';
        }
      } else {
        $output .= '    <tr>
                          <td colspan="2">' . MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_FEED_ERROR . '</td>
                        </tr>';
      }

      $output .= '    <tr>
                        <td class="text-right" colspan="2"><a href="http://addons.oscommerce.com" target="_blank">' . HTML::image(OSCOM::linkImage('icon_oscommerce.png'), MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_ICON_SITE) . '</a>&nbsp;<a href="http://feeds.feedburner.com/osCommerce_Contributions" target="_blank">' . HTML::image(OSCOM::linkImage('icon_rss.png'), MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_ICON_RSS) . '</a></td>
                      </tr>
                    </tbody>
                  </table>';

      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Latest Add-Ons Module',
        'configuration_key' => 'MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to show the latest osCommerce Add-Ons on the dashboard?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_SORT_ORDER',
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
      return array('MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_STATUS', 'MODULE_ADMIN_DASHBOARD_LATEST_ADDONS_SORT_ORDER');
    }
  }
?>
