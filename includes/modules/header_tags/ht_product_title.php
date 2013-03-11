<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class ht_product_title {
    var $code = 'ht_product_title';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_product_title() {
      $this->title = MODULE_HEADER_TAGS_PRODUCT_TITLE_TITLE;
      $this->description = MODULE_HEADER_TAGS_PRODUCT_TITLE_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_PRODUCT_TITLE_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_PRODUCT_TITLE_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_PRODUCT_TITLE_STATUS == 'True');
      }
    }

    function execute() {
      global $OSCOM_APP, $OSCOM_Template, $product_exists;

      if ( ($OSCOM_APP->getCode() == 'products') && ($OSCOM_APP->getCurrentAction() == null) ) {
        if ( $product_exists === true ) {
          $product_info_query = osc_db_query("select pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$_GET['id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
          $product_info = osc_db_fetch_array($product_info_query);

          $OSCOM_Template->setTitle($product_info['products_name'] . ', ' . $OSCOM_Template->getTitle());
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_PRODUCT_TITLE_STATUS');
    }

    function install() {
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Product Title Module', 'MODULE_HEADER_TAGS_PRODUCT_TITLE_STATUS', 'True', 'Do you want to allow product titles to be added to the page title?', '6', '1', 'osc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_PRODUCT_TITLE_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      osc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_PRODUCT_TITLE_STATUS', 'MODULE_HEADER_TAGS_PRODUCT_TITLE_SORT_ORDER');
    }
  }
?>
