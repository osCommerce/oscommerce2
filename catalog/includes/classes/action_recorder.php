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
      global $customer_id;

      $module = tep_sanitize_string(str_replace(' ', '', $module));

      if (!class_exists($module)) {
        if (file_exists(DIR_WS_MODULES . 'action_recorder/' . $module . '.php')) {
          include(DIR_WS_MODULES . 'action_recorder/' . $module . '.php');
        } else {
          return false;
        }
      }

      $this->_module = $module;

      $GLOBALS[$this->_module] = new $module();

      if (tep_session_is_registered('customer_id')) {
        $this->_customer_id = $customer_id;
      }
    }

    function check() {
      if (tep_not_null($this->_module)) {
        if ($GLOBALS[$this->_module]->_log_retries == true) {
          if ($GLOBALS[$this->_module]->check()) {
            return true;
          } else {
            $this->record();

            return false;
          }
        } else {
          return $GLOBALS[$this->_module]->check();
        }
      }

      return false;
    }

    function getIdentifier() {
      if (tep_not_null($this->_module)) {
        return $GLOBALS[$this->_module]->_identifier;
      }
    }

    function record() {
      if (tep_not_null($this->_module)) {
        tep_db_query("insert into " . TABLE_ACTION_RECORDER . " (module, customer_id, identifier, date_added) values ('" . tep_db_input($this->_module) . "', '" . (int)$this->_customer_id . "', '" . tep_db_input($this->getIdentifier()) . "', now())");
      }
    }
  }
?>
