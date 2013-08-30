<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class d_partner_news {
    var $code = 'd_partner_news';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_partner_news() {
      $this->title = MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_DESCRIPTION;

      if ( defined('MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_STATUS == 'True');
      }

      if ( !function_exists('json_decode') ) {
        $this->description .= '<p style="color: #ff0000; font-weight: bold;">' . MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_ERROR_JSON_DECODE . '</p>';

        $this->enabled = false;
      }
    }

    function getOutput() {
      $result = $this->_getContent();

      $output = null;

      if (is_array($result) && !empty($result)) {
        $output = '<table border="0" width="100%" cellspacing="0" cellpadding="4">' .
                  '  <tr class="dataTableHeadingRow">' .
                  '    <td class="dataTableHeadingContent">' . MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_TITLE . '</td>' .
                  '  </tr>';

        foreach ($result as $p) {
          $output .= '  <tr class="dataTableRow" onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);">' .
                     '    <td class="dataTableContent"><a href="' . $p['url'] . '" target="_blank"><strong>' . $p['title'] . '</strong></a> (' . $p['category_title'] . ')<br />' . $p['status_update'] . '</td>' .
                     '  </tr>';
        }

        $output .= '  <tr class="dataTableRow">' .
                   '    <td class="dataTableContent" align="right" colspan="2"><a href="http://www.oscommerce.com/Services" target="_blank">' . MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_MORE_TITLE . '</a></td>' .
                   '  </tr>' .
                   '</table>';
      }

      return $output;
    }

    function _getContent() {
      $result = null;

      $filename = DIR_FS_CACHE . 'oscommerce_partners_news.cache';

      if ( file_exists($filename) ) {
        $difference = floor((time() - filemtime($filename)) / 60);

        if ( $difference < 60 ) {
          $result = unserialize(file_get_contents($filename));
        }
      }

      if ( !isset($result) ) {
        if (function_exists('curl_init')) {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, 'http://www.oscommerce.com/index.php?RPC&Website&Index&GetPartnerStatusUpdates');
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          $response = trim(curl_exec($ch));
          curl_close($ch);

          if (!empty($response)) {
            $result = trim($response);
          }
        } else {
          if ($fp = @fsockopen('www.oscommerce.com', 80, $errno, $errstr, 30)) {
            $header = 'GET /index.php?RPC&Website&Index&GetPartnerStatusUpdates HTTP/1.0' . "\r\n" .
                      'Host: www.oscommerce.com' . "\r\n" .
                      'Connection: close' . "\r\n\r\n";

            fwrite($fp, $header);

            $response = '';
            while (!feof($fp)) {
              $response .= fgets($fp, 1024);
            }

            fclose($fp);

            $response = explode("\r\n\r\n", $response); // split header and content

            if (isset($response[1]) && !empty($response[1])) {
              $result = trim($response[1]);
            }
          }
        }

        if ( !empty($result) ) {
          $result = json_decode($result, true);

          if ( is_writable(DIR_FS_CACHE) ) {
            file_put_contents($filename, serialize($result), LOCK_EX);
          }
        }
      }

      return $result;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Partner News Module', 'MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_STATUS', 'True', 'Do you want to show the latest osCommerce Partner News on the dashboard?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_STATUS', 'MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_SORT_ORDER');
    }
  }
?>
