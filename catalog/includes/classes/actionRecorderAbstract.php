<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  namespace osCommerce\OM\classes;

  abstract class actionRecorderAbstract {
    protected $code;
    protected $title;
    protected $description;
    protected $sort_order = 0;
    protected $minutes = 15;
    protected $identifier;

    abstract public function check();
    abstract public function install();
    abstract public function keys();

    public function getTitle() {
      return $this->title;
    }

    public function getIdentifier() {
      return $this->identifier;
    }

    public function setIdentifier() {
      $this->identifier = tep_get_ip_address();
    }

    public function canPerform($user_id, $user_name) {
      $check_query = tep_db_query("select date_added from " . TABLE_ACTION_RECORDER . " where module = '" . tep_db_input($this->code) . "' and (" . (!empty($user_id) ? "user_id = '" . (int)$user_id . "' or " : "") . " identifier = '" . tep_db_input($this->identifier) . "') and date_added >= date_sub(now(), interval " . (int)$this->minutes  . " minute) and success = 1 order by date_added desc limit 1");
      if (tep_db_num_rows($check_query)) {
        return false;
      } else {
        return true;
      }
    }

    public function expireEntries() {
      tep_db_query("delete from " . TABLE_ACTION_RECORDER . " where module = '" . $this->code . "' and date_added < date_sub(now(), interval " . (int)$this->minutes  . " minute)");

      return tep_db_affected_rows();
    }

    public function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
  }
?>
