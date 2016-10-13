<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\FileSystem;
  use OSC\OM\Registry;

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
        $output = '<table class="table table-hover">
                    <thead>
                      <tr class="info">
                        <th>' . MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_TITLE . '</th>
                      </tr>
                    </thead>
                    <tbody>';

        foreach ($result as $p) {
          $output .= '    <tr>
                            <td><a href="' . $p['url'] . '" target="_blank"><strong>' . $p['title'] . '</strong></a> <span class="label label-info">' . $p['category_title'] . '</span><br />' . $p['status_update'] . '</td>
                          </tr>';
        }

        $output .= '    <tr>
                          <td class="text-right"><a href="https://www.oscommerce.com/Services" target="_blank">' . MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_MORE_TITLE . '</a></td>
                        </tr>
                      </tbody>
                    </table>';
      }

      return $output;
    }

    function _getContent() {
      $result = null;

      $filename = DIR_FS_CACHE . 'oscommerce_partners_news.cache';

      if ( is_file($filename) ) {
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

          if ( FileSystem::isWritable(DIR_FS_CACHE) ) {
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
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Partner News Module',
        'configuration_key' => 'MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to show the latest osCommerce Partner News on the dashboard?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_SORT_ORDER',
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
      return array('MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_STATUS', 'MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_SORT_ORDER');
    }
  }
?>
