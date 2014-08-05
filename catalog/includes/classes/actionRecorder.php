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

    public function __construct($module, $user_id = null, $user_name = null) {
      if ( !defined('MODULE_ACTION_RECORDER_INSTALLED') || !in_array($module . '.php', explode(';', MODULE_ACTION_RECORDER_INSTALLED)) ) {
        return false;
      }

      $class = 'osCommerce\\OM\\modules\\action_recorder\\' . $module;

      if ( !class_exists($class, false) ) {
        if ( file_exists(DIR_FS_CATALOG . 'includes/languages/' . basename($_SESSION['language']) . '/modules/action_recorder/' . basename($module) . '.php') ) {
          include(DIR_FS_CATALOG . 'includes/languages/' . basename($_SESSION['language']) . '/modules/action_recorder/' . basename($module) . '.php');
        }
      }

      if ( !is_subclass_of($class, 'actionRecorderAbstract') ) {
        return false;
      }

      $this->_module = new $class();
      $this->_module->setUserId($user_id);
      $this->_module->setUserName($user_name);
      $this->_module->setIdentifier();
    }

    public function record($success = true) {
      if ( isset($this->_module) ) {
        tep_db_query("insert into " . TABLE_ACTION_RECORDER . " (module, user_id, user_name, identifier, success, date_added) values ('" . tep_db_input($this->_module->getCode()) . "', '" . (int)$this->_module->getUserId() . "', '" . tep_db_input($this->_module->getUserName()) . "', '" . tep_db_input($this->_module->getIdentifier()) . "', '" . ($success === true ? 1 : 0) . "', now())");
      }
    }

    public function __call($name, $arguments) {
      if ( isset($this->_module) ) {
        return call_user_func_array(array($this->_module, $name), $arguments);
      }
    }
  }
?>
