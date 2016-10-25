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

  class d_reviews {
    var $code = 'd_reviews';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_reviews() {
      $this->title = MODULE_ADMIN_DASHBOARD_REVIEWS_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_REVIEWS_DESCRIPTION;

      if ( defined('MODULE_ADMIN_DASHBOARD_REVIEWS_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_REVIEWS_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_REVIEWS_STATUS == 'True');
      }
    }

    function getOutput() {
      $OSCOM_Db = Registry::get('Db');

      $output = '<table class="table table-hover">
                   <thead>
                     <tr class="info">
                       <th>' . MODULE_ADMIN_DASHBOARD_REVIEWS_TITLE . '</th>
                       <th>' . MODULE_ADMIN_DASHBOARD_REVIEWS_DATE . '</th>
                       <th>' . MODULE_ADMIN_DASHBOARD_REVIEWS_REVIEWER . '</th>
                       <th class="text-right">' . MODULE_ADMIN_DASHBOARD_REVIEWS_RATING . '</th>
                       <th class="text-right">' . MODULE_ADMIN_DASHBOARD_REVIEWS_REVIEW_STATUS . '</th>
                     </tr>
                   </thead>
                   <tbody>';

      $Qreviews = $OSCOM_Db->get([
        'reviews r',
        'products_description pd'
      ], [
        'r.reviews_id',
        'r.date_added',
        'pd.products_name',
        'r.customers_name',
        'r.reviews_rating',
        'r.reviews_status'
      ], [
        'pd.products_id' => [
          'rel' => 'r.products_id'
        ],
        'pd.language_id' => (int)$_SESSION['languages_id']
      ], 'r.date_added desc', 6);

      while ($Qreviews->fetch()) {
        $output .= '    <tr>
                          <td><a href="' . OSCOM::link(FILENAME_REVIEWS, 'rID=' . $Qreviews->valueInt('reviews_id') . '&action=edit') . '">' . $Qreviews->value('products_name') . '</a></td>
                          <td>' . tep_date_short($Qreviews->value('date_added')) . '</td>
                          <td>' . $Qreviews->valueProtected('customers_name') . '</td>
                          <td class="text-right">' . str_repeat('<i class="fa fa-star text-info"></i>', $Qreviews->valueInt('reviews_rating')) . str_repeat('<i class="fa fa-star-o"></i>', 5 - $Qreviews->valueInt('reviews_rating')) . '</td>
                          <td class="text-right"><i class="fa fa-circle ' . ($Qreviews->valueInt('reviews_status') === 1 ? 'text-success' : 'text-danger') . '"></i></td>
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
      return defined('MODULE_ADMIN_DASHBOARD_REVIEWS_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Reviews Module',
        'configuration_key' => 'MODULE_ADMIN_DASHBOARD_REVIEWS_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to show the latest reviews on the dashboard?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_ADMIN_DASHBOARD_REVIEWS_SORT_ORDER',
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
      return array('MODULE_ADMIN_DASHBOARD_REVIEWS_STATUS', 'MODULE_ADMIN_DASHBOARD_REVIEWS_SORT_ORDER');
    }
  }
?>