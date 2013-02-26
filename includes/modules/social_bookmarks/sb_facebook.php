<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class sb_facebook {
    var $code = 'sb_facebook';
    var $title;
    var $description;
    var $sort_order;
    var $icon = 'facebook.png';
    var $enabled = false;

    function sb_facebook() {
      $this->title = MODULE_SOCIAL_BOOKMARKS_FACEBOOK_TITLE;
      $this->public_title = MODULE_SOCIAL_BOOKMARKS_FACEBOOK_PUBLIC_TITLE;
      $this->description = MODULE_SOCIAL_BOOKMARKS_FACEBOOK_DESCRIPTION;

      if ( defined('MODULE_SOCIAL_BOOKMARKS_FACEBOOK_STATUS') ) {
        $this->sort_order = MODULE_SOCIAL_BOOKMARKS_FACEBOOK_SORT_ORDER;
        $this->enabled = (MODULE_SOCIAL_BOOKMARKS_FACEBOOK_STATUS == 'True');
      }
    }

    function getOutput() {
      return '<a href="http://www.facebook.com/share.php?u=' . urlencode(osc_href_link('products', 'id=' . $_GET['id'], 'NONSSL', false)) . '" target="_blank"><img src="' . DIR_WS_IMAGES . 'social_bookmarks/' . $this->icon . '" border="0" title="' . osc_output_string_protected($this->public_title) . '" alt="' . osc_output_string_protected($this->public_title) . '" /></a>';
    }

    function isEnabled() {
      return $this->enabled;
    }

    function getIcon() {
      return $this->icon;
    }

    function getPublicTitle() {
      return $this->public_title;
    }

    function check() {
      return defined('MODULE_SOCIAL_BOOKMARKS_FACEBOOK_STATUS');
    }

    function install() {
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Facebook Module', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_STATUS', 'True', 'Do you want to allow products to be shared through Facebook?', '6', '1', 'osc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      osc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_SOCIAL_BOOKMARKS_FACEBOOK_STATUS', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_SORT_ORDER');
    }
  }
?>
