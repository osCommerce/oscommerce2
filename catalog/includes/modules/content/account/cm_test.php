<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class cm_test {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_test() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_TEST_TITLE;
      $this->description = MODULE_CONTENT_TEST_DESCRIPTION;

      if ( defined('MODULE_CONTENT_TEST_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_TEST_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_TEST_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate;

      $test_array = array('title' => 'Test link',
                          'link' => tep_href_link(FILENAME_DEFAULT),
                          'icon' => 'circle-arrow-e');

      $oscTemplate->_data['account']['account']['links']['test'] = $test_array;

      $oscTemplate->_data['account']['test'] = array('title' => 'Test',
                                                     'links' => array('test' => $test_array));

      $counter = 1;

      foreach ( array_keys($oscTemplate->_data['account']['account']['links']) as $key ) {
        if ( $key == 'edit' ) {
          break;
        }

        $counter++;
      }

      $before_eight = array_slice($oscTemplate->_data['account']['account']['links'], 0, $counter, true);
      $after_eight = array_slice($oscTemplate->_data['account']['account']['links'], $counter, null, true);

      $oscTemplate->_data['account']['account']['links'] = $before_eight + array('test2' => $test_array) + $after_eight;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_TEST_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Test Module', 'MODULE_CONTENT_TEST_STATUS', 'True', 'Do you want to enable the test module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_TEST_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_CONTENT_TEST_STATUS', 'MODULE_CONTENT_TEST_SORT_ORDER');
    }
  }
?>
