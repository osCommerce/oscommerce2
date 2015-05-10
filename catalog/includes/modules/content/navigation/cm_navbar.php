<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  class cm_navbar {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_navbar() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_NAVBAR_TITLE;
      $this->description = MODULE_CONTENT_NAVBAR_DESCRIPTION;

      if ( defined('MODULE_CONTENT_NAVBAR_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_NAVBAR_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_NAVBAR_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $cart, $lng, $currencies, $request_type, $oscTemplate;

      ob_start();
      include(DIR_WS_MODULES . 'content/' . $this->group . '/templates/navbar.php');
      $template = ob_get_clean();

      $oscTemplate->addContent($template, $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_NAVBAR_STATUS');
    }

    function install() {
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Navbar Module', 'MODULE_CONTENT_NAVBAR_STATUS', 'True', 'Should the Navbar be shown? ', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_NAVBAR_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_CONTENT_NAVBAR_STATUS', 'MODULE_CONTENT_NAVBAR_SORT_ORDER');
    }
  }

