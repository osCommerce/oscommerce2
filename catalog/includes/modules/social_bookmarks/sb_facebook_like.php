<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class sb_facebook_like {
    var $code = 'sb_facebook_like';
    var $title;
    var $description;
    var $sort_order;
    var $icon = 'facebook.png';
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_social_bookmarks_facebook_like_title');
      $this->public_title = OSCOM::getDef('module_social_bookmarks_facebook_like_public_title');
      $this->description = OSCOM::getDef('module_social_bookmarks_facebook_like_description');

      if ( defined('MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_STATUS') ) {
        $this->sort_order = MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_SORT_ORDER;
        $this->enabled = (MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_STATUS == 'True');
      }
    }

    function getOutput() {
      $style = (MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_STYLE == 'Standard') ? 'standard' : 'button_count';
      $faces = (MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_FACES == 'True') ? 'true' : 'false';
      $width = MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_WIDTH;
      $action = (MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_VERB == 'Like') ? 'like' : 'recommend';
      $scheme = (MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_SCHEME == 'Light') ? 'light' : 'dark';

      return '<iframe src="http://www.facebook.com/plugins/like.php?href=' . urlencode(OSCOM::link('product_info.php', 'products_id=' . $_GET['products_id'], false)) . '&amp;layout=' . $style . '&amp;show_faces=' . $faces . '&amp;width=' . $width . '&amp;action=' . $action . '&amp;colorscheme=' . $scheme . '&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:' . $width . 'px; height:35px;" allowTransparency="true"></iframe>';
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
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Facebook Like Module',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to allow products to be shared through Facebook Like?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Layout Style',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_STYLE',
        'configuration_value' => 'Standard',
        'configuration_description' => 'Determines the size and amount of social context next to the button.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'Standard\', \'Button Count\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Show Faces',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_FACES',
        'configuration_value' => 'False',
        'configuration_description' => 'Show profile pictures below the button?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Width',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_WIDTH',
        'configuration_value' => '125',
        'configuration_description' => 'The width of the iframe in pixels.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Verb to Display',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_VERB',
        'configuration_value' => 'Like',
        'configuration_description' => 'The verb to display in the button.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'Like\', \'Recommend\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Color Scheme',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_SCHEME',
        'configuration_value' => 'Light',
        'configuration_description' => 'The color scheme of the button.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'Light\', \'Dark\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_SORT_ORDER',
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
      return array('MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_STATUS', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_STYLE', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_FACES', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_WIDTH', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_VERB', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_SCHEME', 'MODULE_SOCIAL_BOOKMARKS_FACEBOOK_LIKE_SORT_ORDER');
    }
  }
?>
