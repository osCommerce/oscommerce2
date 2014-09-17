<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_LOGIN {
    var $_title = 'Log In with PayPal';
    var $_short_title = 'Log In';
    var $_cm_code = 'login/cm_paypal_login';
    var $_sort_order = 1000;

    function getTitle() {
      return $this->_title;
    }

    function getShortTitle() {
      return $this->_short_title;
    }

    function install($OSCOM_PayPal) {
      $installed = explode(';', MODULE_CONTENT_INSTALLED);
      $installed[] = $this->_cm_code;

      $OSCOM_PayPal->saveParameter('MODULE_CONTENT_INSTALLED', implode(';', $installed));
    }

    function uninstall($OSCOM_PayPal) {
      $installed = explode(';', MODULE_CONTENT_INSTALLED);
      $installed_pos = array_search($this->_cm_code, $installed);

      if ( $installed_pos !== false ) {
        unset($installed[$installed_pos]);

        $OSCOM_PayPal->saveParameter('MODULE_CONTENT_INSTALLED', implode(';', $installed));
      }
    }

    function canMigrate() {
      $class = basename($this->_cm_code);

      if ( file_exists(DIR_FS_CATALOG . 'includes/modules/content/' . $this->_cm_code . '.php') ) {
        if ( !class_exists($class) ) {
          include(DIR_FS_CATALOG . 'includes/modules/content/' . $this->_cm_code . '.php');
        }

        $module = new $class();

        if ( isset($module->signature) ) {
          $sig = explode('|', $module->signature);

          if ( isset($sig[0]) && ($sig[0] == 'paypal') && isset($sig[1]) && ($sig[1] == 'paypal_login') && isset($sig[2]) ) {
            return version_compare($sig[2], 4) >= 0;
          }
        }
      }

      return false;
    }

    function migrate($OSCOM_PayPal) {
      if ( defined('MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE') ) {
        $server = (MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE == 'Live') ? 'LIVE' : 'SANDBOX';

        if ( defined('MODULE_CONTENT_PAYPAL_LOGIN_CLIENT_ID') ) {
          if ( tep_not_null(MODULE_CONTENT_PAYPAL_LOGIN_CLIENT_ID) ) {
            if ( !defined('OSCOM_APP_PAYPAL_LOGIN_' . $server . '_CLIENT_ID') || !tep_not_null(constant('OSCOM_APP_PAYPAL_LOGIN_' . $server . '_CLIENT_ID')) ) {
              $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_LOGIN_' . $server . '_CLIENT_ID', MODULE_CONTENT_PAYPAL_LOGIN_CLIENT_ID);
            }
          }

          $OSCOM_PayPal->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_CLIENT_ID');
        }

        if ( defined('MODULE_CONTENT_PAYPAL_LOGIN_SECRET') ) {
          if ( tep_not_null(MODULE_CONTENT_PAYPAL_LOGIN_SECRET) ) {
            if ( !defined('OSCOM_APP_PAYPAL_LOGIN_' . $server . '_SECRET') || !tep_not_null(constant('OSCOM_APP_PAYPAL_LOGIN_' . $server . '_SECRET')) ) {
              $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_LOGIN_' . $server . '_SECRET', MODULE_CONTENT_PAYPAL_LOGIN_SECRET);
            }
          }

          $OSCOM_PayPal->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_SECRET');
        }
      }

      if ( defined('MODULE_CONTENT_PAYPAL_LOGIN_THEME') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_LOGIN_THEME', MODULE_CONTENT_PAYPAL_LOGIN_THEME);
        $OSCOM_PayPal->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_THEME');
      }

      if ( defined('MODULE_CONTENT_PAYPAL_LOGIN_ATTRIBUTES') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_LOGIN_ATTRIBUTES', MODULE_CONTENT_PAYPAL_LOGIN_ATTRIBUTES);
        $OSCOM_PayPal->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_ATTRIBUTES');
      }

      if ( defined('MODULE_CONTENT_PAYPAL_LOGIN_CONTENT_WIDTH') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_LOGIN_CONTENT_WIDTH', MODULE_CONTENT_PAYPAL_LOGIN_CONTENT_WIDTH);
        $OSCOM_PayPal->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_CONTENT_WIDTH');
      }

      if ( defined('MODULE_CONTENT_PAYPAL_LOGIN_SORT_ORDER') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_LOGIN_SORT_ORDER', MODULE_CONTENT_PAYPAL_LOGIN_SORT_ORDER, 'Sort Order', 'Sort order of display (lowest to highest).');
        $OSCOM_PayPal->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_SORT_ORDER');
      }

      if ( defined('MODULE_CONTENT_PAYPAL_LOGIN_STATUS') ) {
        $status = '-1';

        if ( (MODULE_CONTENT_PAYPAL_LOGIN_STATUS == 'True') && defined('MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE') ) {
          if ( MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE == 'Live' ) {
            $status = '1';
          } else {
            $status = '0';
          }
        }

        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_LOGIN_STATUS', $status);
        $OSCOM_PayPal->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_STATUS');
      }

      if ( defined('MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE');
      }

      if ( defined('MODULE_CONTENT_PAYPAL_LOGIN_VERIFY_SSL') ) {
        if ( !defined('OSCOM_APP_PAYPAL_VERIFY_SSL') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_VERIFY_SSL', (MODULE_CONTENT_PAYPAL_LOGIN_VERIFY_SSL == 'True') ? '1' : '0');
        }

        $OSCOM_PayPal->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_VERIFY_SSL');
      }

      if ( defined('MODULE_CONTENT_PAYPAL_LOGIN_PROXY') ) {
        if ( !defined('OSCOM_APP_PAYPAL_PROXY') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PROXY', MODULE_CONTENT_PAYPAL_LOGIN_PROXY);
        }

        $OSCOM_PayPal->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_PROXY');
      }
    }
  }
?>
