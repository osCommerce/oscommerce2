<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class bm_best_sellers {
    var $code = 'bm_best_sellers';
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function bm_best_sellers() {
      $this->title = MODULE_BOXES_BEST_SELLERS_TITLE;
      $this->description = MODULE_BOXES_BEST_SELLERS_DESCRIPTION;

      if ( defined('MODULE_BOXES_BEST_SELLERS_STATUS') ) {
        $this->sort_order = MODULE_BOXES_BEST_SELLERS_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_BEST_SELLERS_STATUS == 'True');

        $this->group = ((MODULE_BOXES_BEST_SELLERS_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    function execute() {
      global $OSCOM_APP, $current_category_id, $oscTemplate;

      if ( $OSCOM_APP->getCode() != 'products' ) {
        if (isset($current_category_id) && ($current_category_id > 0)) {
          $best_sellers_query = osc_db_query("select distinct p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c where p.products_status = '1' and p.products_ordered > 0 and p.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and '" . (int)$current_category_id . "' in (c.categories_id, c.parent_id) order by p.products_ordered desc, pd.products_name limit " . MAX_DISPLAY_BESTSELLERS);
        } else {
          $best_sellers_query = osc_db_query("select distinct p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_ordered > 0 and p.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by p.products_ordered desc, pd.products_name limit " . MAX_DISPLAY_BESTSELLERS);
        }

        if (osc_db_num_rows($best_sellers_query) >= MIN_DISPLAY_BESTSELLERS) {
          $bestsellers_list = '';

          while ($best_sellers = osc_db_fetch_array($best_sellers_query)) {
            $bestsellers_list .= '<li><a href="' . osc_href_link('products', 'id=' . $best_sellers['products_id']) . '">' . $best_sellers['products_name'] . '</a></li>';
          }

          $data = '<li class="nav-header">' . MODULE_BOXES_BEST_SELLERS_BOX_TITLE . '</li>' .
                  $bestsellers_list;

          $oscTemplate->addBlock($data, $this->group);
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_BOXES_BEST_SELLERS_STATUS');
    }

    function install() {
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Best Sellers Module', 'MODULE_BOXES_BEST_SELLERS_STATUS', 'True', 'Do you want to add the module to your shop?', '6', '1', 'osc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Placement', 'MODULE_BOXES_BEST_SELLERS_CONTENT_PLACEMENT', 'Right Column', 'Should the module be loaded in the left or right column?', '6', '1', 'osc_cfg_select_option(array(\'Left Column\', \'Right Column\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_BOXES_BEST_SELLERS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      osc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_BOXES_BEST_SELLERS_STATUS', 'MODULE_BOXES_BEST_SELLERS_CONTENT_PLACEMENT', 'MODULE_BOXES_BEST_SELLERS_SORT_ORDER');
    }
  }
?>
