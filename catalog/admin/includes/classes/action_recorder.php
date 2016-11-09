<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  require(OSCOM::getConfig('dir_root', 'Shop') . 'includes/classes/action_recorder.php');

  class actionRecorderAdmin extends actionRecorder {
    function __construct($module, $user_id = null, $user_name = null) {
      global $PHP_SELF;

      $this->lang = Registry::get('Language');

      $module = HTML::sanitize(str_replace(' ', '', $module));

      if (defined('MODULE_ACTION_RECORDER_INSTALLED') && tep_not_null(MODULE_ACTION_RECORDER_INSTALLED)) {
        if (tep_not_null($module) && in_array($module . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)), explode(';', MODULE_ACTION_RECORDER_INSTALLED))) {
          if (!class_exists($module)) {
            if (is_file(OSCOM::getConfig('dir_root', 'Shop') . 'includes/modules/action_recorder/' . $module . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)))) {
              $this->lang->loadDefinitions('Shop/modules/action_recorder/' . $module);
              include(OSCOM::getConfig('dir_root', 'Shop') . 'includes/modules/action_recorder/' . $module . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)));
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
