<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce
  Copyright (c) 2012 Club osCommerce clubosc.com
  
  Released under the GNU General Public License
*/

  class ht_manufacturer_title {
    var $code = 'ht_manufacturer_title';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_manufacturer_title() {
      $this->title = MODULE_HEADER_TAGS_MANUFACTURER_TITLE_TITLE;
      $this->description = MODULE_HEADER_TAGS_MANUFACTURER_TITLE_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_MANUFACTURER_TITLE_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_MANUFACTURER_TITLE_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_MANUFACTURER_TITLE_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $HTTP_GET_VARS, $oscTemplate, $manufacturers, $languages_id;

      if (basename($PHP_SELF) == FILENAME_DEFAULT) {
        if (isset($HTTP_GET_VARS['manufacturers_id']) && is_numeric($HTTP_GET_VARS['manufacturers_id'])) {
          $manufacturers_query = tep_db_query("select manufacturers_name, manufacturers_seo_title from " . TABLE_MANUFACTURERS . " m, " . TABLE_MANUFACTURERS_INFO . " mi where m.manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "' and m.manufacturers_id = mi.manufacturers_id and languages_id = '" . (int)$languages_id . "'");
          if (tep_db_num_rows($manufacturers_query)) {
            $manufacturers = tep_db_fetch_array($manufacturers_query);

            if (tep_not_null($manufacturers['manufacturers_seo_title'])) {
              $oscTemplate->setTitle($manufacturers['manufacturers_seo_title']);
            }
            else {
              $oscTemplate->setTitle($manufacturers['manufacturers_name']);
            }
          }
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_MANUFACTURER_TITLE_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Manufacturer Title Module', 'MODULE_HEADER_TAGS_MANUFACTURER_TITLE_STATUS', 'True', 'Do you want to allow manufacturer titles to be added to the page title?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_MANUFACTURER_TITLE_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_MANUFACTURER_TITLE_STATUS', 'MODULE_HEADER_TAGS_MANUFACTURER_TITLE_SORT_ORDER');
    }
  }
?>
