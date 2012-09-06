<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce
  Copyright (c) 2012 Club osCommerce www.clubosc.com

  Released under the GNU General Public License
*/

  class ht_noscript {
    var $code = 'ht_noscript';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_noscript() {
      $this->title = MODULE_HEADER_TAGS_NOSCRIPT_TITLE;
      $this->description = MODULE_HEADER_TAGS_NOSCRIPT_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_NOSCRIPT_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_NOSCRIPT_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_NOSCRIPT_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate;

      $oscTemplate->addBlock('<noscript><div class="no-script"><div class="no-script-inner">' . tep_output_string(MODULE_HEADER_TAGS_NOSCRIPT_TEXT) . '</div></div></noscript>', $this->group);
      $oscTemplate->addBlock('<style>.no-script { border: 1px solid #ddd; border-width: 0 0 1px; background: #ffff90; font: 14px verdana; line-height: 1.25; text-align: center; color: #2f2f2f; } .no-script .no-script-inner { width: 950px; margin: 0 auto; padding: 5px; } .no-script p { margin: 0; }</style>', $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_NOSCRIPT_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable No Script Module', 'MODULE_HEADER_TAGS_NOSCRIPT_STATUS', 'True', 'Add message for people with .js turned off?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_NOSCRIPT_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_NOSCRIPT_STATUS', 'MODULE_HEADER_TAGS_NOSCRIPT_SORT_ORDER');
    }
  }
?>
