<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require(DIR_FS_CATALOG . 'includes/classes/actionRecorder.php');
  require(DIR_FS_CATALOG . 'includes/classes/actionRecorderAbstract.php');

  class actionRecorderAdmin extends \osCommerce\OM\classes\actionRecorder {
    function actionRecorderAdmin($module, $user_id = null, $user_name = null) {
      global $language, $PHP_SELF;

      if ( !defined('MODULE_ACTION_RECORDER_INSTALLED') || !in_array($module . '.php', explode(';', MODULE_ACTION_RECORDER_INSTALLED)) ) {
        return false;
      }

      $class = 'osCommerce\\OM\\modules\\action_recorder\\' . $module;

      if ( !class_exists($class, false) ) {
        if ( file_exists(DIR_FS_CATALOG . 'includes/languages/' . basename($_SESSION['language']) . '/modules/action_recorder/' . basename($module) . '.php') ) {
          include(DIR_FS_CATALOG . 'includes/languages/' . basename($_SESSION['language']) . '/modules/action_recorder/' . basename($module) . '.php');
        }

        include(DIR_FS_CATALOG . 'includes/modules/action_recorder/' . $module . '.php');
      }

      $this->_module = new $class();
      $this->_module->setUserId($user_id);
      $this->_module->setUserName($user_name);
      $this->_module->setIdentifier();
    }
  }
?>
