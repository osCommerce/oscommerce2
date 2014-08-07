<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  namespace osCommerce\OM\modules\action_recorder;

  class ar_tell_a_friend extends \osCommerce\OM\classes\actionRecorderAbstract {
    protected $code = 'ar_tell_a_friend';

    public function __construct() {
      $this->title = MODULE_ACTION_RECORDER_TELL_A_FRIEND_TITLE;
      $this->description = MODULE_ACTION_RECORDER_TELL_A_FRIEND_DESCRIPTION;

      if ( $this->check() ) {
        $this->minutes = (int)MODULE_ACTION_RECORDER_TELL_A_FRIEND_EMAIL_MINUTES;
      }
    }

    public function check() {
      return defined('MODULE_ACTION_RECORDER_TELL_A_FRIEND_EMAIL_MINUTES');
    }

    public function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Minimum Minutes Per E-Mail', 'MODULE_ACTION_RECORDER_TELL_A_FRIEND_EMAIL_MINUTES', '15', 'Minimum number of minutes to allow 1 e-mail to be sent (eg, 15 for 1 e-mail every 15 minutes)', '6', '0', now())");
    }

    public function keys() {
      return array('MODULE_ACTION_RECORDER_TELL_A_FRIEND_EMAIL_MINUTES');
    }
  }
?>
