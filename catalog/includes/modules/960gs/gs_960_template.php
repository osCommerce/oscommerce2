<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

  class gs_960_template {
    var $code = 'gs_960_template';
    var $group = '960gs';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function gs_960_template() {
      $this->title = MODULE_960GS_TEMPLATE_TITLE;
      $this->description = MODULE_960GS_TEMPLATE_DESCRIPTION;

      if ( defined( 'MODULE_960GS_TEMPLATE_STATUS' ) ) {
        $this->sort_order = MODULE_960GS_TEMPLATE_SORT_ORDER;
        $this->enabled = ( MODULE_960GS_TEMPLATE_STATUS == 'True' );
      }
    }

    function execute() {
      global $oscTemplate;

      $template_cols = explode('-', MODULE_960GS_TEMPLATE_GRID_COLS);
      $oscTemplate->setGridContainerWidth($template_cols[0]);
      $oscTemplate->setGridContentWidth($template_cols[1]);
      $oscTemplate->setGridColumnWidth($template_cols[2]);

    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined( 'MODULE_960GS_TEMPLATE_STATUS' );
    }

    function install() {
      tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Grid 960gs developer', 'MODULE_960GS_TEMPLATE_STATUS', 'True', 'Do you want to add the Grid 960gs Developer to all pages?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())" );
      tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, set_function) values ('Grid Col Select', 'MODULE_960GS_TEMPLATE_GRID_COLS', '24-16-4', 'Container - Content - Column<br />Select the grid columns configuration.', '6', '2', now(), 'gs_960_template_col_list_select(')" );
      tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_960GS_TEMPLATE_SORT_ORDER', '1', 'Sort order of display. Lowest is displayed first.', '6', '3', now())" );
    }

    function remove() {
      tep_db_query( "delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_960GS_TEMPLATE_STATUS', 'MODULE_960GS_TEMPLATE_GRID_COLS', 'MODULE_960GS_TEMPLATE_SORT_ORDER');
    }
  }

  function gs_960_template_get_grid_width($containers = array(12, 16, 24)) {
    foreach ($containers as $container_width) {
      for ( $column_width = 1; $column_width < $container_width/2; $column_width++ ) {
        $grid_array[] = array( 'id' => $container_width . '-' . ($container_width - 2 * $column_width) . '-' . $column_width,
                               'text' => $container_width . '-' . ($container_width - 2 * $column_width) . '-' . $column_width);
      }
    }

    return $grid_array;
  }

  function gs_960_template_col_list_select($select_array, $key_value) {
    return tep_draw_pull_down_menu('configuration[' . $key_value . ']', gs_960_template_get_grid_width(), $select_array);
  }
?>
