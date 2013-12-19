<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class ht_mailchimp_360 {
    var $code = 'ht_mailchimp_360';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_mailchimp_360() {
      $this->title = MODULE_HEADER_TAGS_MAILCHIMP_360_TITLE;
      $this->description = MODULE_HEADER_TAGS_MAILCHIMP_360_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_MAILCHIMP_360_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_MAILCHIMP_360_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_MAILCHIMP_360_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF;

      include(DIR_WS_MODULES . 'header_tags/ht_mailchimp_360/MCAPI.class.php');
      include(DIR_WS_MODULES . 'header_tags/ht_mailchimp_360/mc360.php');

      $mc360 = new mc360();
      $mc360->set_cookies();

      if (basename($PHP_SELF) == FILENAME_CHECKOUT_SUCCESS) {
        $mc360->process();
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_MAILCHIMP_360_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable MailChimp 360 Module', 'MODULE_HEADER_TAGS_MAILCHIMP_360_STATUS', 'True', 'Do you want to activate this module in your shop?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('API Key', 'MODULE_HEADER_TAGS_MAILCHIMP_360_API_KEY', '', 'An API Key assigned to your MailChimp account', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Debug E-Mail', 'MODULE_HEADER_TAGS_MAILCHIMP_360_DEBUG_EMAIL', '', 'If an e-mail address is entered, debug data will be sent to it', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_MAILCHIMP_360_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");

// Internal parameters
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('MailChimp Store ID', 'MODULE_HEADER_TAGS_MAILCHIMP_360_STORE_ID', '', 'Do not edit. Store ID value.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('MailChimp Key Valid', 'MODULE_HEADER_TAGS_MAILCHIMP_360_KEY_VALID', '', 'Do not edit. Key Value value.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");

// Internal parameters
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('MODULE_HEADER_TAGS_MAILCHIMP_360_STORE_ID', 'MODULE_HEADER_TAGS_MAILCHIMP_360_KEY_VALID')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_MAILCHIMP_360_STATUS', 'MODULE_HEADER_TAGS_MAILCHIMP_360_API_KEY', 'MODULE_HEADER_TAGS_MAILCHIMP_360_DEBUG_EMAIL', 'MODULE_HEADER_TAGS_MAILCHIMP_360_SORT_ORDER');
    }
  }
?>
