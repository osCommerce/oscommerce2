<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce
  Copyright (c) 2012 Club osCommerce clubosc.com

  Released under the GNU General Public License
*/

  class ht_product_meta {
    var $code = 'ht_product_meta';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_product_meta() {
      $this->title = MODULE_HEADER_TAGS_PRODUCT_META_TITLE;
      $this->description = MODULE_HEADER_TAGS_PRODUCT_META_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_PRODUCT_META_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_PRODUCT_META_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_PRODUCT_META_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate, $HTTP_GET_VARS, $languages_id, $product_check;
      $meta_show = '';
      if (basename($PHP_SELF) == FILENAME_PRODUCT_INFO) {
        if (isset($HTTP_GET_VARS['products_id'])) {
          if ($product_check['total'] > 0) {
            $meta_info_query = tep_db_query("select pd.products_seo_description, pd.products_seo_keywords from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
            $meta_info = tep_db_fetch_array($meta_info_query);
            
            if (tep_not_null($meta_info['products_seo_description'])) {
              $oscTemplate->addBlock('<meta name="description" content="' . tep_output_string($meta_info['products_seo_description']) . '" />', $this->group);
            }
            
            if (tep_not_null($meta_info['products_seo_keywords'])) {
              $oscTemplate->addBlock('<meta name="keywords" content="' . tep_output_string($meta_info['products_seo_keywords']) . '" />', $this->group);
            }
            
          }
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_PRODUCT_META_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Product Meta Module', 'MODULE_HEADER_TAGS_PRODUCT_META_STATUS', 'True', 'Do you want to allow product meta tags to be added to the page header?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_PRODUCT_META_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_PRODUCT_META_STATUS', 'MODULE_HEADER_TAGS_PRODUCT_META_SORT_ORDER');
    }
  }
?>
