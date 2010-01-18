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
    var $_customer_id;

    function actionRecorder($module) {
      global $customer_id, $language, $PHP_SELF;

      $module = tep_sanitize_string(str_replace(' ', '', $module));

      if (defined('MODULE_ACTION_RECORDER_INSTALLED') && tep_not_null(MODULE_ACTION_RECORDER_INSTALLED)) {
        if (tep_not_null($module) && in_array($module . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)), explode(';', MODULE_ACTION_RECORDER_INSTALLED))) {
          if (!class_exists($module)) {
            if (file_exists(DIR_WS_MODULES . 'action_recorder/' . $module . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)))) {
              include(DIR_WS_LANGUAGES . $language . '/modules/action_recorder/' . $module . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)));
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

      $GLOBALS[$this->_module] = new $module();
      $GLOBALS[$this->_module]->setIdentifier();

      if (tep_session_is_registered('customer_id')) {
        $this->_customer_id = $customer_id;
      }
    }

    function canPerform() {
      if (tep_not_null($this->_module)) {
        if ($GLOBALS[$this->_module]->log_retries == true) {
          if ($GLOBALS[$this->_module]->canPerform()) {
            return true;
          } else {
            $this->record(false);

            return false;
          }
        } else {
          return $GLOBALS[$this->_module]->canPerform();
        }
      }

      return false;
    }

    function getTitle() {
      if (tep_not_null($this->_module)) {
        return $GLOBALS[$this->_module]->title;
      }
    }

    function getIdentifier() {
      if (tep_not_null($this->_module)) {
        return $GLOBALS[$this->_module]->identifier;
      }
    }

    function record($success = true) {
      if (tep_not_null($this->_module)) {
        tep_db_query("insert into " . TABLE_ACTION_RECORDER . " (module, customer_id, identifier, success, date_added) values ('" . tep_db_input($this->_module) . "', '" . (int)$this->_customer_id . "', '" . tep_db_input($this->getIdentifier()) . "', '" . ($success ? 1 : 0) . "', now())");
      }
    }

    function expireEntries() {
      if (tep_not_null($this->_module)) {
        return $GLOBALS[$this->_module]->expireEntries();
      }
    }
  }
?>
