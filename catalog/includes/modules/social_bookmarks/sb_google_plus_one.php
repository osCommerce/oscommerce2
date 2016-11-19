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

  class sb_google_plus_one {
    var $code = 'sb_google_plus_one';
    var $title;
    var $description;
    var $sort_order;
    var $icon;
    var $enabled = false;

    protected $lang;

    function __construct() {
      $this->lang = Registry::get('Language');

      $this->title = OSCOM::getDef('module_social_bookmarks_google_plus_one_title');
      $this->public_title = OSCOM::getDef('module_social_bookmarks_google_plus_one_public_title');
      $this->description = OSCOM::getDef('module_social_bookmarks_google_plus_one_description');

      if ( defined('MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_STATUS') ) {
        $this->sort_order = MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_SORT_ORDER;
        $this->enabled = (MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_STATUS == 'True');
      }
    }

    function getOutput() {
      $output = '<div class="g-plusone" data-href="' . OSCOM::link('product_info.php', 'products_id=' . $_GET['products_id'], false) . '" data-size="' . strtolower(MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_SIZE) . '" data-annotation="' . strtolower(MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ANNOTATION) . '"';

      if (MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ANNOTATION == 'Inline') {
        $output.= ' data-width="' . (int)MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_WIDTH . '" data-align="' . strtolower(MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ALIGN) . '"';
      }

      $output .= '></div>';

      $output .= '<script>
  if ( typeof window.___gcfg == "undefined" ) {
    window.___gcfg = { };
  }

  if ( typeof window.___gcfg.lang == "undefined" ) {
    window.___gcfg.lang = "' . HTML::outputProtected($this->lang->get('code')) . '";
  }

  (function() {
    var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true;
    po.src = \'https://apis.google.com/js/plusone.js\';
    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s);
  })();
</script>';

      return $output;
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
      return defined('MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Google+ +1 Button Module',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to allow products to be recommended through Google+ +1 Button?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Button Size',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_SIZE',
        'configuration_value' => 'Small',
        'configuration_description' => 'Sets the size of the button.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'Small\', \'Medium\', \'Standard\', \'Tall\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Annotation',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ANNOTATION',
        'configuration_value' => 'None',
        'configuration_description' => 'The annotation to display next to the button.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'None\', \'Bubble\', \'Inline\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Inline Width',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_WIDTH',
        'configuration_value' => '120',
        'configuration_description' => 'The width of the inline annotation in pixels (minimum 120).',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Inline Alignment',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ALIGN',
        'configuration_value' => 'Left',
        'configuration_description' => 'The alignment of the inline annotation.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'Left\', \'Right\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_SORT_ORDER',
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
      return array('MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_STATUS', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_SIZE', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ANNOTATION', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_WIDTH', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ALIGN', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_SORT_ORDER');
    }
  }
?>
