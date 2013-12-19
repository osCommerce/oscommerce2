<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class sb_google_plus_one {
    var $code = 'sb_google_plus_one';
    var $title;
    var $description;
    var $sort_order;
    var $icon;
    var $enabled = false;

    function sb_google_plus_one() {
      $this->title = MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_TITLE;
      $this->public_title = MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_PUBLIC_TITLE;
      $this->description = MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_DESCRIPTION;

      if ( defined('MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_STATUS') ) {
        $this->sort_order = MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_SORT_ORDER;
        $this->enabled = (MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_STATUS == 'True');
      }
    }

    function getOutput() {
      global $HTTP_GET_VARS, $lng, $languages_id;

      if (!isset($lng) || (isset($lng) && !is_object($lng))) {
        include(DIR_WS_CLASSES . 'language.php');
        $lng = new language;
      }

      foreach ($lng->catalog_languages as $lkey => $lvalue) {
        if ($lvalue['id'] == $languages_id) {
          $language_code = $lkey;
          break;
        }
      }

      $output = '<div class="g-plusone" data-href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $HTTP_GET_VARS['products_id'], 'NONSSL', false) . '" data-size="' . strtolower(MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_SIZE) . '" data-annotation="' . strtolower(MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ANNOTATION) . '"';

      if (MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ANNOTATION == 'Inline') {
        $output.= ' data-width="' . (int)MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_WIDTH . '" data-align="' . strtolower(MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ALIGN) . '"';
      }

      $output .= '></div>';

      $output .= '<script type="text/javascript">
  if ( typeof window.___gcfg == "undefined" ) {
    window.___gcfg = { };
  }

  if ( typeof window.___gcfg.lang == "undefined" ) {
    window.___gcfg.lang = "' . tep_output_string_protected($language_code) . '";
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
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Google+ +1 Button Module', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_STATUS', 'True', 'Do you want to allow products to be recommended through Google+ +1 Button?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Button Size', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_SIZE', 'Small', 'Sets the size of the button.', '6', '1', 'tep_cfg_select_option(array(\'Small\', \'Medium\', \'Standard\', \'Tall\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Annotation', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ANNOTATION', 'None', 'The annotation to display next to the button.', '6', '1', 'tep_cfg_select_option(array(\'None\', \'Bubble\', \'Inline\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Inline Width', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_WIDTH', '120', 'The width of the inline annotation in pixels (minimum 120).', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Inline Alignment', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ALIGN', 'Left', 'The alignment of the inline annotation.', '6', '1', 'tep_cfg_select_option(array(\'Left\', \'Right\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_STATUS', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_SIZE', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ANNOTATION', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_WIDTH', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_ALIGN', 'MODULE_SOCIAL_BOOKMARKS_GOOGLE_PLUS_ONE_SORT_ORDER');
    }
  }
?>
