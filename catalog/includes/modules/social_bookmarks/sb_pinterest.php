<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce
  Copyright (c) 2012 Club osCommerce www.clubosc.com

  Released under the GNU General Public License
*/

  class sb_pinterest {
    var $code = 'sb_pinterest';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;
    
    function sb_pinterest() {
      $this->title = MODULE_SOCIAL_BOOKMARKS_PINTEREST_TITLE;
      $this->description = MODULE_SOCIAL_BOOKMARKS_PINTEREST_DESCRIPTION;

      if ( defined('MODULE_SOCIAL_BOOKMARKS_PINTEREST_STATUS') ) {
        $this->sort_order = MODULE_SOCIAL_BOOKMARKS_PINTEREST_SORT_ORDER;
        $this->enabled = (MODULE_SOCIAL_BOOKMARKS_PINTEREST_STATUS == 'True');
      }
    }

    function getOutput() {
      global $HTTP_GET_VARS;
      global $languages_id;
      global $oscTemplate;
      
      // add the js in the footer
      $oscTemplate->addBlock('<script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>', 'footer_scripts');

      // grab the product name (used for description)
      $product_name = tep_get_products_name($HTTP_GET_VARS['products_id'], $languages_id);

      // and image (used for media)
      $image_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "'");
      $image = tep_db_fetch_array($image_query);

      // url
      $params = array('url=' . urlencode(tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $HTTP_GET_VARS['products_id'], 'NONSSL', false)));

      // image
      $params[] = 'media=' . urlencode(HTTP_SERVER . DIR_WS_HTTP_CATALOG . DIR_WS_IMAGES . $image['products_image']);

      // count layout
      switch(MODULE_SOCIAL_BOOKMARKS_PINTEREST_BUTTON_COUNT_POSITION) {
        case "Horizontal":
        $layout = 'count-layout="horizontal"';
        break;
        case "Vertical":
        $layout = 'count-layout="vertical"';
        break;
        default:
        $layout = 'count-layout="none"';
        break;
      }
      
      // description
      $params[] = 'description=' . urlencode($product_name);

      $params = implode('&', $params);

      return '<a href="http://pinterest.com/pin/create/button/?' . $params . '" class="pin-it-button" ' . $layout . '><img border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a>';
    }

    function isEnabled() {
      return $this->enabled;
    }

    function getPublicTitle() {
      return $this->public_title;
    }

    function check() {
      return defined('MODULE_SOCIAL_BOOKMARKS_PINTEREST_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Pinterest Module', 'MODULE_SOCIAL_BOOKMARKS_PINTEREST_STATUS', 'True', 'Do you want to allow Pinterest Button?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SOCIAL_BOOKMARKS_PINTEREST_SORT_ORDER', '90', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Layout Position', 'MODULE_SOCIAL_BOOKMARKS_PINTEREST_BUTTON_COUNT_POSITION', 'None', 'Horizontal or Vertical or None', '6', '2', 'tep_cfg_select_option(array(\'Horizontal\', \'Vertical\', \'None\'), ', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_SOCIAL_BOOKMARKS_PINTEREST_STATUS', 'MODULE_SOCIAL_BOOKMARKS_PINTEREST_SORT_ORDER', 'MODULE_SOCIAL_BOOKMARKS_PINTEREST_BUTTON_COUNT_POSITION');
    }
  }
?>
