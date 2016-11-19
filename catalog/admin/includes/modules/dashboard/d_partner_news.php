<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Cache;
  use OSC\OM\FileSystem;
  use OSC\OM\HTTP;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class d_partner_news {
    var $code = 'd_partner_news';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_partner_news() {
      $this->title = OSCOM::getDef('module_admin_dashboard_partner_news_title');
      $this->description = OSCOM::getDef('module_admin_dashboard_partner_news_description');

      if ( defined('MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_PARTNER_NEWS_STATUS == 'True');
      }

      if ( !function_exists('json_decode') ) {
        $this->description .= '<p style="color: #ff0000; font-weight: bold;">' . OSCOM::getDef('module_admin_dashboard_partner_news_error_json_decode') . '</p>';

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
                        <th>' . OSCOM::getDef('module_admin_dashboard_partner_news_title') . '</th>
                      </tr>
                    </thead>
                    <tbody>';

        foreach ($result as $p) {
          $output .= '    <tr>
                            <td><a href="' . $p['url'] . '" target="_blank"><strong>' . $p['title'] . '</strong></a> <span class="label label-info">' . $p['category_title'] . '</span><br />' . $p['status_update'] . '</td>
                          </tr>';
        }

        $output .= '    <tr>
                          <td class="text-right"><a href="https://www.oscommerce.com/Services" target="_blank">' . OSCOM::getDef('module_admin_dashboard_partner_news_more_title') . '</a></td>
                        </tr>
                      </tbody>
                    </table>';
      }

      return $output;
    }

    function _getContent() {
      $result = null;

      $NewsCache = new Cache('oscommerce_website-partner_news');

      if ($NewsCache->exists(60)) {
        $result = $NewsCache->get();
      } else {
        $response = HTTP::getResponse([
          'url' => 'https://www.oscommerce.com/index.php?RPC&Website&Index&GetPartnerStatusUpdates'
        ]);

        if (!empty($response)) {
          $response = json_decode($response, true);

          if (is_array($response) && !empty($response)) {
            $result = $response;

            $NewsCache->save($result);
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
