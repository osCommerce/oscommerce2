<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require(DIR_FS_CATALOG . 'includes/classes/action_recorder.php');

  class actionRecorderAdmin extends actionRecorder {
    function actionRecorderAdmin($module, $user_id = null, $user_name = null) {
      global $language, $PHP_SELF;

      $module = tep_sanitize_string(str_replace(' ', '', $module));

      if (defined('MODULE_ACTION_RECORDER_INSTALLED') && tep_not_null(MODULE_ACTION_RECORDER_INSTALLED)) {
        if (tep_not_null($module) && in_array($module . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)), explode(';', MODULE_ACTION_RECORDER_INSTALLED))) {
          if (!class_exists($module)) {
            if (file_exists(DIR_FS_CATALOG . 'includes/modules/action_recorder/' . $module . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)))) {
              include(DIR_FS_CATALOG . 'includes/languages/' . $language . '/modules/action_recorder/' . $module . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)));
              include(DIR_FS_CATALOG . 'includes/modules/action_recorder/' . $module . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)));
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
  }
?>
