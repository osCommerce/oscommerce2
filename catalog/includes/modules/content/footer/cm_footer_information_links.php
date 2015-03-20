<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class cm_footer_information_links {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_footer_information_links() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_FOOTER_INFORMATION_TITLE;
      $this->description = MODULE_CONTENT_FOOTER_INFORMATION_DESCRIPTION;

      if ( defined('MODULE_CONTENT_FOOTER_INFORMATION_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_FOOTER_INFORMATION_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_FOOTER_INFORMATION_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate;
      
      $content_width = (int)MODULE_CONTENT_FOOTER_INFORMATION_CONTENT_WIDTH;
      
      ob_start();
      include(DIR_WS_MODULES . 'content/' . $this->group . '/templates/links.php');
      $template = ob_get_clean();

      $oscTemplate->addContent($template, $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_FOOTER_INFORMATION_STATUS');
    }

    function install() {
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Information Links Footer Module', 'MODULE_CONTENT_FOOTER_INFORMATION_STATUS', 'True', 'Do you want to enable the Information Links content module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Width', 'MODULE_CONTENT_FOOTER_INFORMATION_CONTENT_WIDTH', '3', 'What width container should the content be shown in? (12 = full width, 6 = half width).', '6', '1', 'tep_cfg_select_option(array(\'12\', \'11\', \'10\', \'9\', \'8\', \'7\', \'6\', \'5\', \'4\', \'3\', \'2\', \'1\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_FOOTER_INFORMATION_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_CONTENT_FOOTER_INFORMATION_STATUS', 'MODULE_CONTENT_FOOTER_INFORMATION_CONTENT_WIDTH', 'MODULE_CONTENT_FOOTER_INFORMATION_SORT_ORDER');
    }
  }

