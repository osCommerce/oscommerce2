<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class sb_facebook_like {
    var $code = 'sb_facebook_like';
    var $title;
    var $description;
    var $sort_order;
    var $icon = 'facebook.png';
    var $enabled = false;

    function sb_facebook_like() {
      $this->title = MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_TITLE;
      $this->public_title = MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_PUBLIC_TITLE;
      $this->description = MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_DESCRIPTION;

      if ( defined('MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_STATUS') ) {
        $this->sort_order = MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_SORT_ORDER;
        $this->enabled = (MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_STATUS == 'True');
      }
    }

    function getOutput() {
      global $HTTP_GET_VARS;

      $style = (MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_STYLE == 'Standard') ? 'standard' : 'button_count';
      $faces = (MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_FACES == 'True') ? 'true' : 'false';
      $width = MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_WIDTH;
      $action = (MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_VERB == 'Like') ? 'like' : 'recommend';
      $scheme = (MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_SCHEME == 'Light') ? 'light' : 'dark';

      return '<iframe src="http://www.facebook.com/plugins/like.php?href=' . urlencode(tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $HTTP_GET_VARS['products_id'], 'NONSSL', false)) . '&amp;layout=' . $style . '&amp;show_faces=' . $faces . '&amp;width=' . $width . '&amp;action=' . $action . '&amp;colorscheme=' . $scheme . '&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:' . $width . 'px; height:35px;" allowTransparency="true"></iframe>';
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
      return defined('MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Facebook Like Module', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_STATUS', 'True', 'Do you want to allow products to be shared through Facebook Like?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Layout Style', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_STYLE', 'Standard', 'Determines the size and amount of social context next to the button.', '6', '1', 'tep_cfg_select_option(array(\'Standard\', \'Button Count\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show Faces', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_FACES', 'False', 'Show profile pictures below the button?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Width', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_WIDTH', '125', 'The width of the iframe in pixels.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Verb to Display', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_VERB', 'Like', 'The verb to display in the button.', '6', '1', 'tep_cfg_select_option(array(\'Like\', \'Recommend\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Color Scheme', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_SCHEME', 'Light', 'The color scheme of the button.', '6', '1', 'tep_cfg_select_option(array(\'Light\', \'Dark\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_STATUS', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_STYLE', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_FACES', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_WIDTH', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_VERB', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_SCHEME', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_SORT_ORDER');
    }
  }
?>
