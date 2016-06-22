<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class sb_twitter {
    var $code = 'sb_twitter';
    var $title;
    var $description;
    var $sort_order;
    var $icon = 'twitter.png';
    var $enabled = false;

    function __construct() {
      $this->title = MODULE_SOCIAL_BOOKMARKS_TWITTER_TITLE;
      $this->public_title = MODULE_SOCIAL_BOOKMARKS_TWITTER_PUBLIC_TITLE;
      $this->description = MODULE_SOCIAL_BOOKMARKS_TWITTER_DESCRIPTION;

      if ( defined('MODULE_SOCIAL_BOOKMARKS_TWITTER_STATUS') ) {
        $this->sort_order = MODULE_SOCIAL_BOOKMARKS_TWITTER_SORT_ORDER;
        $this->enabled = (MODULE_SOCIAL_BOOKMARKS_TWITTER_STATUS == 'True');
      }
    }

    function getOutput() {
      return '<a href="http://twitter.com/home?status=' . urlencode(OSCOM::link('product_info.php', 'products_id=' . $_GET['products_id'], 'NONSSL', false)) . '" target="_blank"><img src="' . DIR_WS_IMAGES . 'social_bookmarks/' . $this->icon . '" border="0" title="' . tep_output_string_protected($this->public_title) . '" alt="' . tep_output_string_protected($this->public_title) . '" /></a>';
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
      return defined('MODULE_SOCIAL_BOOKMARKS_TWITTER_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Twitter Module',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_TWITTER_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to allow products to be shared through Twitter?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_TWITTER_SORT_ORDER',
        'configuration_value' => '0',
        'configuration_description' => 'Sort order of display. Lowest is displayed first.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    function keys() {
      return array('MODULE_SOCIAL_BOOKMARKS_TWITTER_STATUS', 'MODULE_SOCIAL_BOOKMARKS_TWITTER_SORT_ORDER');
    }
  }
?>
