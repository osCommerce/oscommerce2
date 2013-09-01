<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class cm_account_set_password {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_account_set_password() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_ACCOUNT_SET_PASSWORD_TITLE;
      $this->description = MODULE_CONTENT_ACCOUNT_SET_PASSWORD_DESCRIPTION;

      if ( defined('MODULE_CONTENT_ACCOUNT_SET_PASSWORD_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_ACCOUNT_SET_PASSWORD_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_ACCOUNT_SET_PASSWORD_STATUS == 'True');
      }
    }

    function execute() {
      global $customer_id, $oscTemplate;

      if ( tep_session_is_registered('customer_id') ) {
        $check_query = tep_db_query("select customers_password from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
        $check = tep_db_fetch_array($check_query);

        if ( empty($check['customers_password']) ) {
          $counter = 0;

          foreach ( array_keys($oscTemplate->_data['account']['account']['links']) as $key ) {
            if ( $key == 'password' ) {
              break;
            }

            $counter++;
          }

          $before_eight = array_slice($oscTemplate->_data['account']['account']['links'], 0, $counter, true);
          $after_eight = array_slice($oscTemplate->_data['account']['account']['links'], $counter + 1, null, true);

          $oscTemplate->_data['account']['account']['links'] = $before_eight;

          if ( MODULE_CONTENT_ACCOUNT_SET_PASSWORD_ALLOW_PASSWORD == 'True' ) {
            $oscTemplate->_data['account']['account']['links'] += array('set_password' => array('title' => MODULE_CONTENT_ACCOUNT_SET_PASSWORD_SET_PASSWORD_LINK_TITLE,
                                                                        'link' => tep_href_link('ext/modules/content/account/set_password.php', '', 'SSL'),
                                                                        'icon' => 'key'));
          }

          $oscTemplate->_data['account']['account']['links'] += $after_eight;
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_ACCOUNT_SET_PASSWORD_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Set Account Password', 'MODULE_CONTENT_ACCOUNT_SET_PASSWORD_STATUS', 'True', 'Do you want to enable the Set Account Password module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Allow Local Passwords', 'MODULE_CONTENT_ACCOUNT_SET_PASSWORD_ALLOW_PASSWORD', 'True', 'Allow local account passwords to be set.', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_ACCOUNT_SET_PASSWORD_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_CONTENT_ACCOUNT_SET_PASSWORD_STATUS', 'MODULE_CONTENT_ACCOUNT_SET_PASSWORD_ALLOW_PASSWORD', 'MODULE_CONTENT_ACCOUNT_SET_PASSWORD_SORT_ORDER');
    }
  }
?>
