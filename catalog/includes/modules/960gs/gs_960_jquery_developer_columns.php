<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

  class gs_960_jquery_developer_columns {
    var $code = 'gs_960_jquery_developer_columns';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function gs_960_jquery_developer_columns() {
      $this->title = MODULE_960GS_JQUERY_DEVELOPER_COLUMNS_TITLE;
      $this->description = MODULE_960GS_JQUERY_DEVELOPER_COLUMNS_DESCRIPTION;

      if ( defined( 'MODULE_960GS_JQUERY_DEVELOPER_COLUMNS_STATUS' ) ) {
        $this->sort_order = MODULE_960GS_JQUERY_DEVELOPER_COLUMNS_SORT_ORDER;
        $this->enabled = ( MODULE_960GS_JQUERY_DEVELOPER_COLUMNS_STATUS == 'True' );
      }
    }

    function execute() {
      global $oscTemplate;

      $header_tag = '<link rel="stylesheet" type="text/css" href="ext/960gs/developer_960.css" />' .
"<script>
$.fn.developer960 = function() {
  return this.each(function(){
    $(this).attr('id', this.id + '" . 'Develop_' . $oscTemplate->getGridContainerWidth() . 'col' . "');
  });
};

$(document).ready(function() {
  $('#bodyWrapper').developer960()
});
</script>";

      $oscTemplate->addBlock( $header_tag, $this->group );
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined( 'MODULE_960GS_JQUERY_DEVELOPER_COLUMNS_STATUS' );
    }

    function install() {
      tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Grid 960gs developer jQuery', 'MODULE_960GS_JQUERY_DEVELOPER_COLUMNS_STATUS', 'True', 'Do you want to add the jQuery Grid 960gs Developer to all pages?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())" );
      tep_db_query( "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_960GS_JQUERY_DEVELOPER_COLUMNS_SORT_ORDER', '999', 'Sort order of display. Lowest is displayed first.', '6', '2', now())" );
    }

    function remove() {
      tep_db_query( "delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_960GS_JQUERY_DEVELOPER_COLUMNS_STATUS', 'MODULE_960GS_JQUERY_DEVELOPER_COLUMNS_SORT_ORDER');
    }
  }
?>
