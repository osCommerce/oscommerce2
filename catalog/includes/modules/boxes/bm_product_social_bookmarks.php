<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class bm_product_social_bookmarks {
    var $code = 'bm_product_social_bookmarks';
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function bm_product_social_bookmarks() {
      $this->title = MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_TITLE;
      $this->description = MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_DESCRIPTION;

      if ( defined('MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_STATUS') ) {
        $this->sort_order = MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_STATUS == 'True');

        $this->group = ((MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    function execute() {
      global $HTTP_GET_VARS, $language, $oscTemplate;

      if ( isset($HTTP_GET_VARS['products_id']) && defined('MODULE_SOCIAL_BOOKMARKS_INSTALLED') && tep_not_null(MODULE_SOCIAL_BOOKMARKS_INSTALLED) ) {
        $sbm_array = explode(';', MODULE_SOCIAL_BOOKMARKS_INSTALLED);

        $social_bookmarks = array();

        foreach ( $sbm_array as $sbm ) {
          $class = substr($sbm, 0, strrpos($sbm, '.'));

          if ( !class_exists($class) ) {
            include(DIR_WS_LANGUAGES . $language . '/modules/social_bookmarks/' . $sbm);
            include(DIR_WS_MODULES . 'social_bookmarks/' . $class . '.php');
          }

          $sb = new $class();

          if ( $sb->isEnabled() ) {
            $social_bookmarks[] = $sb->getOutput();
          }
        }

        if ( !empty($social_bookmarks) ) {
          $data = '<div class="ui-widget infoBoxContainer">' .
                  '  <div class="ui-widget-header infoBoxHeading">' . MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_BOX_TITLE . '</div>' .
                  '  <div class="ui-widget-content infoBoxContents" style="text-align: center;">' . implode(' ', $social_bookmarks) . '</div>' .
                  '</div>';

          $oscTemplate->addBlock($data, $this->group);
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Product Social Bookmarks Module', 'MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_STATUS', 'True', 'Do you want to add the module to your shop?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Placement', 'MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_CONTENT_PLACEMENT', 'Right Column', 'Should the module be loaded in the left or right column?', '6', '1', 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_STATUS', 'MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_CONTENT_PLACEMENT', 'MODULE_BOXES_PRODUCT_SOCIAL_BOOKMARKS_SORT_ORDER');
    }
  }
?>
