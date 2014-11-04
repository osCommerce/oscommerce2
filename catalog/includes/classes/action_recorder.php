<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class actionRecorder {
    var $_module;
    var $_user_id;
    var $_user_name;

    function actionRecorder($module, $user_id = null, $user_name = null) {
      global $PHP_SELF;

      $module = osc_sanitize_string(str_replace(' ', '', $module));

      if (defined('MODULE_ACTION_RECORDER_INSTALLED') && osc_not_null(MODULE_ACTION_RECORDER_INSTALLED)) {
        if (osc_not_null($module) && in_array($module . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)), explode(';', MODULE_ACTION_RECORDER_INSTALLED))) {
          if (!class_exists($module)) {
            if (file_exists(DIR_WS_MODULES . 'action_recorder/' . $module . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)))) {
              include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/action_recorder/' . $module . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)));
              include(DIR_WS_MODULES . 'action_recorder/' . $module . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)));
            } else {
              return false;
            }
          }
        } else {
          return false;
        }
      } else {
        return false;
      }

      $this->_module = $module;

      if (!empty($user_id) && is_numeric($user_id)) {
        $this->_user_id = $user_id;
      }

      if (!empty($user_name)) {
        $this->_user_name = $user_name;
      }

      $GLOBALS[$this->_module] = new $module();
      $GLOBALS[$this->_module]->setIdentifier();
    }

    function canPerform() {
      if (osc_not_null($this->_module)) {
        return $GLOBALS[$this->_module]->canPerform($this->_user_id, $this->_user_name);
      }

      return false;
    }

    function getTitle() {
      if (osc_not_null($this->_module)) {
        return $GLOBALS[$this->_module]->title;
      }
    }

    function getIdentifier() {
      if (osc_not_null($this->_module)) {
        return $GLOBALS[$this->_module]->identifier;
      }
    }

    function record($success = true) {
      if (osc_not_null($this->_module)) {
        osc_db_query("insert into " . TABLE_ACTION_RECORDER . " (module, user_id, user_name, identifier, success, date_added) values ('" . osc_db_input($this->_module) . "', '" . (int)$this->_user_id . "', '" . osc_db_input($this->_user_name) . "', '" . osc_db_input($this->getIdentifier()) . "', '" . ($success == true ? 1 : 0) . "', now())");
      }
    }

    function expireEntries() {
      if (osc_not_null($this->_module)) {
        return $GLOBALS[$this->_module]->expireEntries();
      }
    }
  }
?>
