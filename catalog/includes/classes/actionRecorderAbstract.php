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
// TODO make protected when getters() are created and the admin/modules page calls them instead of the variable directly
    public $code;
    public $title;
    public $description;
    public $sort_order = 0;
    protected $minutes = 15;
    protected $attempts = 1;
    protected $user_id;
    protected $user_name;
    protected $identifier;

    abstract public function check();
    abstract public function install();
    abstract public function keys();

    public function getTitle() {
      return $this->title;
    }

    public function getUserId() {
      return $this->user_id;
    }

    public function getUserName() {
      return $this->user_name;
    }

    public function getIdentifier() {
      return $this->identifier;
    }

    public function setUserId($id) {
      $this->user_id = $id;
    }

    public function setUserName($name) {
      $this->user_name = $name;
    }

    public function setIdentifier() {
      $this->identifier = tep_get_ip_address();
    }

    public function canPerform() {
      $check_query = tep_db_query("select id from " . TABLE_ACTION_RECORDER . " where module = '" . tep_db_input($this->code) . "' and (" . (is_numeric($this->user_id) ? "user_id = '" . (int)$this->user_id . "' or " : "") . " identifier = '" . tep_db_input($this->identifier) . "') and date_added >= date_sub(now(), interval " . (int)$this->minutes  . " minute) and success = 1 order by date_added desc limit " . (int)$this->attempts);

      return ( tep_db_num_rows($check_query) < $this->attempts );
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
