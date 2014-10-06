<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class cm_footer_contact_us {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_footer_contact_us() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_FOOTER_CONTACT_US_TITLE;
      $this->description = MODULE_CONTENT_FOOTER_CONTACT_US_DESCRIPTION;

      if ( defined('MODULE_CONTENT_FOOTER_CONTACT_US_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_FOOTER_CONTACT_US_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_FOOTER_CONTACT_US_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate;
      
      switch (MODULE_CONTENT_FOOTER_CONTACT_US_CONTENT_WIDTH) {
        case "100%":
        $content_width = 12;
        break;
        case "75%":
        $content_width = 9;
        break;
        case "66%":
        $content_width = 8;
        break;
        case "50%":
        $content_width = 6;
        break;
        case "33%":
        $content_width = 4;
        break;
        case "25%":
        $content_width = 3;
        break;
        case "20%":
        default:
        $content_width = 2;
      }
      
      ob_start();
      include(DIR_WS_MODULES . 'content/' . $this->group . '/templates/contact_us.php');
      $template = ob_get_clean();

      $oscTemplate->addContent($template, $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_FOOTER_CONTACT_US_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Contact Us Footer Module', 'MODULE_CONTENT_FOOTER_CONTACT_US_STATUS', 'True', 'Do you want to enable the Contact Us content module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Width', 'MODULE_CONTENT_FOOTER_CONTACT_US_CONTENT_WIDTH', '25%', 'What width container should the content be shown in?', '6', '1', 'tep_cfg_select_option(array(\'20%\', \'25%\', \'33%\', \'50%\', \'66%\', \'75%\', \'100%\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_FOOTER_CONTACT_US_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_CONTENT_FOOTER_CONTACT_US_STATUS', 'MODULE_CONTENT_FOOTER_CONTACT_US_CONTENT_WIDTH', 'MODULE_CONTENT_FOOTER_CONTACT_US_SORT_ORDER');
    }
  }

