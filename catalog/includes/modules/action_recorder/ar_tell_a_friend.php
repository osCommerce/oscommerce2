<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class ar_tell_a_friend {
    var $_log_retries = false;
    var $_min_minutes = 15;
    var $_identifier;

    function ar_tell_a_friend() {
      if (defined('MIN_TELL_A_FRIEND_EMAIL_MINUTES') && is_numeric(MIN_TELL_A_FRIEND_EMAIL_MINUTES) && (MIN_TELL_A_FRIEND_EMAIL_MINUTES > 0)) {
        $this->_min_minutes = (int)MIN_TELL_A_FRIEND_EMAIL_MINUTES;
      }

      $this->_identifier = tep_get_ip_address();
    }

    function check() {
      global $customer_id;

      $check_query = tep_db_query("select date_added from " . TABLE_ACTION_RECORDER . " where module = 'ar_tell_a_friend' and (" . (tep_session_is_registered('customer_id') ? "customer_id = '" . (int)$customer_id . "' or " : "") . " identifier = '" . tep_db_input($this->_identifier) . "') and date_added >= date_sub(now(), interval " . (int)$this->_min_minutes  . " minute) order by date_added desc limit 1");
      if (tep_db_num_rows($check_query)) {
        return false;
      } else {
        return true;
      }
    }
  }
?>
