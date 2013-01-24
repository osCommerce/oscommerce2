<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class ht_totop {
    var $code = 'ht_totop';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_totop() {
      $this->title = MODULE_HEADER_TAGS_TOTOP_TITLE;
      $this->description = MODULE_HEADER_TAGS_TOTOP_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_TOTOP_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_TOTOP_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_TOTOP_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate;

      $oscTemplate->addBlock('<link rel="stylesheet" media="screen,projection" href="ext/jquery/ultotop/css/ui.totop.css" />', $this->group);

      $oscTemplate->addBlock('<script src="ext/jquery/ultotop/easing.js" type="text/javascript"></script>', 'footer_scripts');
    	$oscTemplate->addBlock('<script src="ext/jquery/ultotop/jquery.ui.totop.js" type="text/javascript"></script>', 'footer_scripts');
	    $oscTemplate->addBlock('<script type="text/javascript">
		                            $(document).ready(function() {
                                  $().UItoTop({ easingType: \'easeOutQuart\' });
                                });
	                            </script>', 'footer_scripts');
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_TOTOP_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Scroll To Top Module', 'MODULE_HEADER_TAGS_TOTOP_STATUS', 'True', 'Add Scroll To Top to the webapge?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_TOTOP_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_TOTOP_STATUS', 'MODULE_HEADER_TAGS_OPENSEARCH_SORT_ORDER');
    }
  }
?>
