<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class ar_tell_a_friend {
    var $code = 'ar_tell_a_friend';
    var $title;
    var $description;
    var $sort_order = 0;
    var $log_retries = false;
    var $min_minutes = 15;
    var $identifier;

    function ar_tell_a_friend() {
      $this->title = MODULE_ACTION_RECORDER_TELL_A_FRIEND_TITLE;
      $this->description = MODULE_ACTION_RECORDER_TELL_A_FRIEND_DESCRIPTION;

      if ($this->check()) {
        $this->min_minutes = (int)MODULE_ACTION_RECORDER_TELL_A_FRIEND_EMAIL_MINUTES;
        $this->log_retries = (MODULE_ACTION_RECORDER_TELL_A_FRIEND_LOG_RETRIES == 'True') ? true : false;
      }
    }

    function setIdentifier() {
      $this->identifier = tep_get_ip_address();
    }

    function canPerform() {
      global $customer_id;

      $check_query = tep_db_query("select date_added from " . TABLE_ACTION_RECORDER . " where module = '" . $this->code . "' and (" . (tep_session_is_registered('customer_id') ? "customer_id = '" . (int)$customer_id . "' or " : "") . " identifier = '" . tep_db_input($this->identifier) . "') and date_added >= date_sub(now(), interval " . (int)$this->min_minutes  . " minute) and success = 1 order by date_added desc limit 1");
      if (tep_db_num_rows($check_query)) {
        return false;
      } else {
        return true;
      }
    }

    function expireEntries() {
      global $db_link;

      tep_db_query("delete from " . TABLE_ACTION_RECORDER . " where module = '" . $this->code . "' and date_added < date_sub(now(), interval " . (int)$this->min_minutes  . " minute)");

      return mysql_affected_rows($db_link);
    }

    function check() {
      return defined('MODULE_ACTION_RECORDER_TELL_A_FRIEND_EMAIL_MINUTES');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Minimum Minutes Per E-Mail', 'MODULE_ACTION_RECORDER_TELL_A_FRIEND_EMAIL_MINUTES', '15', 'Minimum number of minutes to allow 1 e-mail to be sent (eg, 15 for 1 e-mail every 15 minutes)', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Log Retries', 'MODULE_ACTION_RECORDER_TELL_A_FRIEND_LOG_RETRIES', 'False', 'Log failed retry attempts', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_ACTION_RECORDER_TELL_A_FRIEND_EMAIL_MINUTES', 'MODULE_ACTION_RECORDER_TELL_A_FRIEND_LOG_RETRIES');
    }
  }
?>
