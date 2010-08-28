<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class sb_twitter_button {
    var $code = 'sb_twitter_button';
    var $title;
    var $description;
    var $sort_order;
    var $icon = 'twitter.png';
    var $enabled = false;

    function sb_twitter_button() {
      $this->title = MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_TITLE;
      $this->public_title = MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_PUBLIC_TITLE;
      $this->description = MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_DESCRIPTION;

      if ( defined('MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_STATUS') ) {
        $this->sort_order = MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_SORT_ORDER;
        $this->enabled = (MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_STATUS == 'True');
      }
    }

    function getOutput() {
      global $HTTP_GET_VARS;

      $params = array('url=' . urlencode(tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $HTTP_GET_VARS['products_id'], 'NONSSL', false)));

      if ( strlen(MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_ACCOUNT) > 0 ) {
        $params[] = 'via=' . urlencode(MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_ACCOUNT);
      }

      if ( strlen(MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_RELATED_ACCOUNT) > 0 ) {
        $params[] = 'related=' . urlencode(MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_RELATED_ACCOUNT) . ((strlen(MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_RELATED_ACCOUNT_DESC) > 0) ? ':' . urlencode(MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_RELATED_ACCOUNT_DESC) : '');
      }

      if ( MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_COUNT_POSITION == 'Vertical' ) {
        $params[] = 'count=vertical';
      } elseif ( MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_COUNT_POSITION == 'None' ) {
        $params[] = 'count=none';
      }

      $params = implode('&', $params);

      return '<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script><a href="http://twitter.com/share?' . $params . '" target="_blank" class="twitter-share-button">' . tep_output_string_protected($this->public_title) . '</a>';
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
      return defined('MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Twitter Button Module', 'MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_STATUS', 'True', 'Do you want to allow products to be shared through Twitter Button?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Shop Owner Twitter Account', 'MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_ACCOUNT', '', 'The Twitter account to attribute the tweet to and is recommended to the user to follow.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Related Twitter Account', 'MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_RELATED_ACCOUNT', '', 'A related Twitter account that is also recommended to the user to follow.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Related Twitter Account Description', 'MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_RELATED_ACCOUNT_DESC', '', 'A description for the related Twitter account.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Count Position', 'MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_COUNT_POSITION', 'Horizontal', 'The position of the counter.', '6', '0', 'tep_cfg_select_option(array(\'Horizontal\', \'Vertical\', \'None\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_STATUS', 'MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_ACCOUNT', 'MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_RELATED_ACCOUNT', 'MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_RELATED_ACCOUNT_DESC', 'MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_COUNT_POSITION', 'MODULE_SOCIAL_BOOKMARKS_TWITTER_BUTTON_SORT_ORDER');
    }
  }
?>
