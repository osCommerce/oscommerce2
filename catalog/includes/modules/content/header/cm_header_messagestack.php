<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class cm_header_messagestack {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_header_messagestack() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_HEADER_MESSAGESTACK_TITLE;
      $this->description = MODULE_CONTENT_HEADER_MESSAGESTACK_DESCRIPTION;

      if ( defined('MODULE_CONTENT_HEADER_MESSAGESTACK_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_HEADER_MESSAGESTACK_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_HEADER_MESSAGESTACK_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate, $messageStack;
      
      if ($messageStack->size('header') > 0) {

        ob_start();
        include(DIR_WS_MODULES . 'content/' . $this->group . '/templates/messagestack.php');
        $template = ob_get_clean();

        $oscTemplate->addContent($template, $this->group);
        
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_HEADER_MESSAGESTACK_STATUS');
    }

    function install() {
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Message Stack Notifications Module', 'MODULE_CONTENT_HEADER_MESSAGESTACK_STATUS', 'True', 'Should the Message Stack Notifications be shown in the header when needed? ', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_HEADER_MESSAGESTACK_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_CONTENT_HEADER_MESSAGESTACK_STATUS', 'MODULE_CONTENT_HEADER_MESSAGESTACK_SORT_ORDER');
    }
  }

