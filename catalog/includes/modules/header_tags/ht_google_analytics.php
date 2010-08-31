<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class ht_google_analytics {
    var $code = 'ht_google_analytics';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_google_analytics() {
      $this->title = MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_TITLE;
      $this->description = MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate;

      if (tep_not_null(MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_ID)) {
        $header = '<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push([\'_setAccount\', \'' . tep_output_string(MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_ID) . '\']);
  _gaq.push([\'_trackPageview\']);
 
  (function() {
    var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
    ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>';

        $oscTemplate->addHeaderTag($header);
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Google Analytics Module', 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_STATUS', 'True', 'Do you want to add Google Analytics to your shop?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Google Analytics ID', 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_ID', '', 'The Google Analytics profile ID to track.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_STATUS', 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_ID', 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_SORT_ORDER');
    }
  }
?>
