<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  namespace osCommerce\OM\classes;

  class actionRecorder {
    protected $_module;
    protected $_user_id;
    protected $_user_name;

    public function __construct($module, $user_id = null, $user_name = null) {
      $module = tep_sanitize_string(str_replace(' ', '', $module));

      if ( !defined('MODULE_ACTION_RECORDER_INSTALLED') || !in_array($module . '.php', explode(';', MODULE_ACTION_RECORDER_INSTALLED)) ) {
        return false;
      }

      $class = 'osCommerce\\OM\\modules\\action_recorder\\' . $module;

      if ( !class_exists($class, false) ) {
        if ( file_exists(DIR_FS_CATALOG . 'includes/languages/' . basename($_SESSION['language']) . '/modules/action_recorder/' . basename($module) . '.php') ) {
          include(DIR_FS_CATLAOG . 'includes/languages/' . basename($_SESSION['language']) . '/modules/action_recorder/' . basename($module) . '.php');
        }
      }

      $this->_module = $module;

      if ( is_numeric($user_id) ) {
        $this->_user_id = $user_id;
      }

      if ( !empty($user_name) ) {
        $this->_user_name = $user_name;
      }

      $GLOBALS[$this->_module] = new $class();
      $GLOBALS[$this->_module]->setIdentifier();
    }

    public function canPerform() {
      if ( isset($this->_module) ) {
        return $GLOBALS[$this->_module]->canPerform($this->_user_id, $this->_user_name);
      }

      return false;
    }

    public function getTitle() {
      if ( isset($this->_module) ) {
        return $GLOBALS[$this->_module]->getTitle();
      }
    }

    public function getIdentifier() {
      if ( isset($this->_module) ) {
        return $GLOBALS[$this->_module]->getIdentifier();
      }
    }

    public function record($success = true) {
      if ( isset($this->_module) ) {
        tep_db_query("insert into " . TABLE_ACTION_RECORDER . " (module, user_id, user_name, identifier, success, date_added) values ('" . tep_db_input($this->_module) . "', '" . (int)$this->_user_id . "', '" . tep_db_input($this->_user_name) . "', '" . tep_db_input($this->getIdentifier()) . "', '" . ($success === true ? 1 : 0) . "', now())");
      }
    }

    public function expireEntries() {
      if ( isset($this->_module) ) {
        return $GLOBALS[$this->_module]->expireEntries();
      }
    }

    public function setUserId($id) {
      $this->_user_id = $id;
    }
  }
?>
