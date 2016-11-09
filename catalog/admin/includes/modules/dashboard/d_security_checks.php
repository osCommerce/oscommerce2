<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class d_security_checks {
    var $code = 'd_security_checks';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_security_checks() {
      $this->title = OSCOM::getDef('module_admin_dashboard_security_checks_title');
      $this->description = OSCOM::getDef('module_admin_dashboard_security_checks_description');

      if ( defined('MODULE_ADMIN_DASHBOARD_SECURITY_CHECKS_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_SECURITY_CHECKS_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_SECURITY_CHECKS_STATUS == 'True');
      }
    }

    function getOutput() {
      global $PHP_SELF;

      $OSCOM_MessageStack = Registry::get('MessageStack');

      $secCheck_types = array('info', 'warning', 'error');

      $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
      $secmodules_array = array();
      if ($secdir = @dir(OSCOM::getConfig('dir_root') . 'includes/modules/security_check/')) {
        while ($file = $secdir->read()) {
          if (!is_dir(OSCOM::getConfig('dir_root') . 'includes/modules/security_check/' . $file)) {
            if (substr($file, strrpos($file, '.')) == $file_extension) {
              $secmodules_array[] = $file;
            }
          }
        }
        sort($secmodules_array);
        $secdir->close();
      }

      foreach ($secmodules_array as $secmodule) {
        include(OSCOM::getConfig('dir_root') . 'includes/modules/security_check/' . $secmodule);

        $secclass = 'securityCheck_' . substr($secmodule, 0, strrpos($secmodule, '.'));
        if (class_exists($secclass)) {
          $secCheck = new $secclass;

          if ( !$secCheck->pass() ) {
            if (!in_array($secCheck->type, $secCheck_types)) {
              $secCheck->type = 'info';
            }

            $OSCOM_MessageStack->add($secCheck->getMessage(), $secCheck->type, 'securityCheckModule');
          }
        }
      }

      if (!$OSCOM_MessageStack->exists('securityCheckModule')) {
        $OSCOM_MessageStack->add(OSCOM::getDef('module_admin_dashboard_security_checks_success'), 'success', 'securityCheckModule');
      }

      $output = $OSCOM_MessageStack->get('securityCheckModule');

      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_SECURITY_CHECKS_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Security Checks Module',
        'configuration_key' => 'MODULE_ADMIN_DASHBOARD_SECURITY_CHECKS_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to run the security checks for this installation?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_ADMIN_DASHBOARD_SECURITY_CHECKS_SORT_ORDER',
        'configuration_value' => '0',
        'configuration_description' => 'Sort order of display. Lowest is displayed first.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_SECURITY_CHECKS_STATUS', 'MODULE_ADMIN_DASHBOARD_SECURITY_CHECKS_SORT_ORDER');
    }
  }
?>
