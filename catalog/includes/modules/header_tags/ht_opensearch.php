<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class ht_opensearch {
    var $code = 'ht_opensearch';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_opensearch() {
      $this->title = MODULE_HEADER_TAGS_OPENSEARCH_TITLE;
      $this->description = MODULE_HEADER_TAGS_OPENSEARCH_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_OPENSEARCH_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_OPENSEARCH_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_OPENSEARCH_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate;

      $oscTemplate->addBlock('<link rel="search" type="application/opensearchdescription+xml" href="' . tep_href_link('opensearch.php', '', 'NONSSL', false) . '" title="' . tep_output_string(STORE_NAME) . '" />', $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_OPENSEARCH_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable OpenSearch Module', 'MODULE_HEADER_TAGS_OPENSEARCH_STATUS', 'True', 'Add shop search functionality to the browser?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Short Name', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_SHORT_NAME', '" . tep_db_input(STORE_NAME) . "', 'Short name to describe the search engine.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Description', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_DESCRIPTION', 'Search " . tep_db_input(STORE_NAME) . "', 'Description of the search engine.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Contact', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_CONTACT', '" . tep_db_input(STORE_OWNER_EMAIL_ADDRESS) . "', 'E-Mail address of the search engine maintainer. (optional)', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Tags', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_TAGS', '', 'Keywords to identify and categorize the search content, separated by an empty space. (optional)', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Attribution', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_ATTRIBUTION', 'Copyright (c) " . tep_db_input(STORE_NAME) . "', 'Attribution for the search content. (optional)', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Adult Content', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_ADULT_CONTENT', 'False', 'Search content contains material suitable only for adults.', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('16x16 Icon', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_ICON', '" . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . "favicon.ico', 'A 16x16 sized icon (must be in .ico format, eg http://server/favicon.ico). (optional)', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('64x64 Image', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_IMAGE', '', 'A 64x64 sized image (must be in .png format, eg http://server/images/logo.png). (optional)', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_OPENSEARCH_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_OPENSEARCH_STATUS', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_SHORT_NAME', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_DESCRIPTION', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_CONTACT', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_TAGS', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_ATTRIBUTION', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_ADULT_CONTENT', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_ICON', 'MODULE_HEADER_TAGS_OPENSEARCH_SITE_IMAGE', 'MODULE_HEADER_TAGS_OPENSEARCH_SORT_ORDER');
    }
  }
?>
