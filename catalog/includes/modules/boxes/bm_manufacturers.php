<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class bm_manufacturers {
    var $code = 'bm_manufacturers';
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function bm_manufacturers() {
      $this->title = MODULE_BOXES_MANUFACTURERS_TITLE;
      $this->description = MODULE_BOXES_MANUFACTURERS_DESCRIPTION;

      if ( defined('MODULE_BOXES_MANUFACTURERS_STATUS') ) {
        $this->sort_order = MODULE_BOXES_MANUFACTURERS_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_MANUFACTURERS_STATUS == 'True');

        $this->group = ((MODULE_BOXES_MANUFACTURERS_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    function getData() {
      global $HTTP_GET_VARS, $request_type, $oscTemplate;

      $data = '';

      $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS . " order by manufacturers_name");
      if ($number_of_rows = tep_db_num_rows($manufacturers_query)) {
        if ($number_of_rows <= MAX_DISPLAY_MANUFACTURERS_IN_A_LIST) {
// Display a list
          $manufacturers_list = '<ul style="list-style: none; margin: 0; padding: 0;">';
          while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
            $manufacturers_name = ((strlen($manufacturers['manufacturers_name']) > MAX_DISPLAY_MANUFACTURER_NAME_LEN) ? substr($manufacturers['manufacturers_name'], 0, MAX_DISPLAY_MANUFACTURER_NAME_LEN) . '..' : $manufacturers['manufacturers_name']);
            if (isset($HTTP_GET_VARS['manufacturers_id']) && ($HTTP_GET_VARS['manufacturers_id'] == $manufacturers['manufacturers_id'])) $manufacturers_name = '<strong>' . $manufacturers_name .'</strong>';
            $manufacturers_list .= '<li><a href="' . tep_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $manufacturers['manufacturers_id']) . '">' . $manufacturers_name . '</a></li>';
          }

          $manufacturers_list .= '</ul>';

          $content = $manufacturers_list;
        } else {
// Display a drop-down
          $manufacturers_array = array();
          if (MAX_MANUFACTURERS_LIST < 2) {
            $manufacturers_array[] = array('id' => '', 'text' => PULL_DOWN_DEFAULT);
          }

          while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
            $manufacturers_name = ((strlen($manufacturers['manufacturers_name']) > MAX_DISPLAY_MANUFACTURER_NAME_LEN) ? substr($manufacturers['manufacturers_name'], 0, MAX_DISPLAY_MANUFACTURER_NAME_LEN) . '..' : $manufacturers['manufacturers_name']);
            $manufacturers_array[] = array('id' => $manufacturers['manufacturers_id'],
                                           'text' => $manufacturers_name);
          }

          $content = tep_draw_form('manufacturers', tep_href_link(FILENAME_DEFAULT, '', $request_type, false), 'get') .
                     tep_draw_pull_down_menu('manufacturers_id', $manufacturers_array, (isset($HTTP_GET_VARS['manufacturers_id']) ? $HTTP_GET_VARS['manufacturers_id'] : ''), 'onchange="this.form.submit();" size="' . MAX_MANUFACTURERS_LIST . '" style="width: 100%"') . tep_hide_session_id() .
                     '</form>';
        }

        $data = '<div class="ui-widget infoBoxContainer">' .
                  '  <div class="ui-widget-header infoBoxHeading">' . MODULE_BOXES_MANUFACTURERS_BOX_TITLE . '</div>' .
                  '  <div class="ui-widget-content infoBoxContents">' . $content . '</div>' .
                  '</div>';
      }

      return $data;
    }

    function execute() {
      global $SID, $oscTemplate;

      if ((USE_CACHE == 'true') && empty($SID)) {
        $output = tep_cache_manufacturers_box();
      } else {
        $output = $this->getData();
      }

      $oscTemplate->addBlock($output, $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_BOXES_MANUFACTURERS_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Manufacturers Module', 'MODULE_BOXES_MANUFACTURERS_STATUS', 'True', 'Do you want to add the module to your shop?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Placement', 'MODULE_BOXES_MANUFACTURERS_CONTENT_PLACEMENT', 'Left Column', 'Should the module be loaded in the left or right column?', '6', '1', 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_BOXES_MANUFACTURERS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_BOXES_MANUFACTURERS_STATUS', 'MODULE_BOXES_MANUFACTURERS_CONTENT_PLACEMENT', 'MODULE_BOXES_MANUFACTURERS_SORT_ORDER');
    }
  }
?>
