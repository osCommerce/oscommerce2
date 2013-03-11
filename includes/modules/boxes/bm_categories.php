<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class bm_categories {
    var $code = 'bm_categories';
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function bm_categories() {
      $this->title = MODULE_BOXES_CATEGORIES_TITLE;
      $this->description = MODULE_BOXES_CATEGORIES_DESCRIPTION;

      if ( defined('MODULE_BOXES_CATEGORIES_STATUS') ) {
        $this->sort_order = MODULE_BOXES_CATEGORIES_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_CATEGORIES_STATUS == 'True');

        $this->group = ((MODULE_BOXES_CATEGORIES_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    function execute() {
      global $OSCOM_Template, $cPath;

      $OSCOM_CategoryTree = new category_tree();
      $OSCOM_CategoryTree->setCategoryPath($cPath, '<strong>', '</strong>');
      $OSCOM_CategoryTree->setParentGroupString('<ul class="nav nav-list">', '</ul>');

      $output = '<li class="nav-header">' . MODULE_BOXES_CATEGORIES_BOX_TITLE . '</li>' .
                $OSCOM_CategoryTree->getTree();

      $OSCOM_Template->addBlock($output, $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_BOXES_CATEGORIES_STATUS');
    }

    function install() {
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Categories Module', 'MODULE_BOXES_CATEGORIES_STATUS', 'True', 'Do you want to add the module to your shop?', '6', '1', 'osc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Placement', 'MODULE_BOXES_CATEGORIES_CONTENT_PLACEMENT', 'Left Column', 'Should the module be loaded in the left or right column?', '6', '1', 'osc_cfg_select_option(array(\'Left Column\', \'Right Column\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_BOXES_CATEGORIES_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      osc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_BOXES_CATEGORIES_STATUS', 'MODULE_BOXES_CATEGORIES_CONTENT_PLACEMENT', 'MODULE_BOXES_CATEGORIES_SORT_ORDER');
    }
  }
?>
