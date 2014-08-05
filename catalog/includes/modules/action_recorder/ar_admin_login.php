<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  namespace osCommerce\OM\modules\action_recorder;

  class ar_admin_login extends \osCommerce\OM\classes\actionRecorderAbstract {
    public $code = 'ar_admin_login';
    protected $minutes = 5;
    protected $attempts = 3;

    public function __construct() {
      $this->title = MODULE_ACTION_RECORDER_ADMIN_LOGIN_TITLE;
      $this->description = MODULE_ACTION_RECORDER_ADMIN_LOGIN_DESCRIPTION;

      if ( $this->check() ) {
        $this->minutes = (int)MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES;
        $this->attempts = (int)MODULE_ACTION_RECORDER_ADMIN_LOGIN_ATTEMPTS;
      }
    }

    public function canPerform() {
      $check_query = tep_db_query("select id from " . TABLE_ACTION_RECORDER . " where module = '" . tep_db_input($this->code) . "' and (" . (isset($this->user_name) ? "user_name = '" . tep_db_input($this->user_name) . "' or " : "") . " identifier = '" . tep_db_input($this->identifier) . "') and date_added >= date_sub(now(), interval " . (int)$this->minutes  . " minute) and success = 0 order by date_added desc limit " . (int)$this->attempts);

      return ( tep_db_num_rows($check_query) < $this->attempts );
    }

    public function check() {
      return defined('MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES');
    }

    public function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Allowed Minutes', 'MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES', '5', 'Number of minutes to allow login attempts to occur.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Allowed Attempts', 'MODULE_ACTION_RECORDER_ADMIN_LOGIN_ATTEMPTS', '3', 'Number of login attempts to allow within the specified period.', '6', '0', now())");
    }

    public function keys() {
      return array('MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES', 'MODULE_ACTION_RECORDER_ADMIN_LOGIN_ATTEMPTS');
    }
  }
?>
