<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class sb_facebook {
    var $code = 'sb_facebook';
    var $title;
    var $description;
    var $sort_order;
    var $icon = 'facebook.png';
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_social_bookmarks_facebook_title');
      $this->public_title = OSCOM::getDef('module_social_bookmarks_facebook_public_title');
      $this->description = OSCOM::getDef('module_social_bookmarks_facebook_description');

      if ( defined('MODULE_SOCIAL_BOOKMARKS_FACEBOOK_STATUS') ) {
        $this->sort_order = MODULE_SOCIAL_BOOKMARKS_FACEBOOK_SORT_ORDER;
        $this->enabled = (MODULE_SOCIAL_BOOKMARKS_FACEBOOK_STATUS == 'True');
      }
    }

    function getOutput() {
      return '<a href="http://www.facebook.com/share.php?u=' . urlencode(OSCOM::link('product_info.php', 'products_id=' . $_GET['products_id'], false)) . '" target="_blank"><img src="' . OSCOM::linkImage('social_bookmarks/' . $this->icon) . '" border="0" title="' . HTML::outputProtected($this->public_title) . '" alt="' . HTML::outputProtected($this->public_title) . '" /></a>';
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
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Facebook Module',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to allow products to be shared through Facebook?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_SORT_ORDER',
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
      return array('MODULE_SOCIAL_BOOKMARKS_FACEBOOK_STATUS', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_SORT_ORDER');
    }
  }
?>
