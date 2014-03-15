<?php
/*
  $Id$ cs_thank_you
  Copyright (c) 2013 Club osC www.clubosc.com
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2010 osCommerce
  Released under the GNU General Public License
*/

  class cm_cs_thank_you {
    var $code;
  	var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_cs_thank_you() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CHECKOUT_SUCCESS_THANK_YOU_TITLE;
      $this->description = MODULE_CHECKOUT_SUCCESS_THANK_YOU_DESCRIPTION;

      if ( defined('MODULE_CHECKOUT_SUCCESS_THANK_YOU_STATUS') ) {
        $this->sort_order = MODULE_CHECKOUT_SUCCESS_THANK_YOU_SORT_ORDER;
        $this->enabled = (MODULE_CHECKOUT_SUCCESS_THANK_YOU_STATUS == 'True');
      }
    }

    function execute() {
	
	  global $oscTemplate;

      $cs_data = '<div id="cs-thank-you" class="ui-widget infoBoxContainer">' .
                '  <div class="ui-widget-header infoBoxHeading">' . MODULE_CHECKOUT_SUCCESS_THANK_YOU_TITLE . '</div>' .
                '  <div class="ui-widget-content infoBoxContents"><p>' . MODULE_CHECKOUT_SUCCESS_THANK_YOU_TEXT .'<br><br>' .
				   MODULE_CHECKOUT_SUCCESS_TEXT_SEE_ORDERS . '<br><br>' . MODULE_CHECKOUT_SUCCESS_TEXT_CONTACT_STORE_OWNER . '<br><br>' .
				   MODULE_CHECKOUT_SUCCESS_TEXT_THANKS_FOR_SHOPPING .
				'  </p></div>' .
                '</div>';
                
      $oscTemplate->addContent($cs_data, $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CHECKOUT_SUCCESS_THANK_YOU_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Thank You Module', 'MODULE_CHECKOUT_SUCCESS_THANK_YOU_STATUS', 'True', 'Do you want to show the Thank You module on the Success Page?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CHECKOUT_SUCCESS_THANK_YOU_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_CHECKOUT_SUCCESS_THANK_YOU_STATUS', 'MODULE_CHECKOUT_SUCCESS_THANK_YOU_SORT_ORDER');
    }
  }