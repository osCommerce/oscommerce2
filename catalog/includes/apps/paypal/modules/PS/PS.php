<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_PS {
    var $_title = 'PayPal Payments Standard';
    var $_short_title = 'Payments Standard';
    var $_pm_code = 'paypal_standard';
    var $_sort_order = 400;

    function getTitle() {
      return $this->_title;
    }

    function getShortTitle() {
      return $this->_short_title;
    }

    function install($OSCOM_PayPal) {
      $installed = explode(';', MODULE_PAYMENT_INSTALLED);
      $installed[] = $this->_pm_code . '.php';

      $OSCOM_PayPal->saveParameter('MODULE_PAYMENT_INSTALLED', implode(';', $installed));
    }

    function uninstall($OSCOM_PayPal) {
      $installed = explode(';', MODULE_PAYMENT_INSTALLED);
      $installed_pos = array_search($this->_pm_code . '.php', $installed);

      if ( $installed_pos !== false ) {
        unset($installed[$installed_pos]);

        $OSCOM_PayPal->saveParameter('MODULE_PAYMENT_INSTALLED', implode(';', $installed));
      }
    }

    function canMigrate() {
      $class = $this->_pm_code;

      if ( file_exists(DIR_FS_CATALOG . 'includes/modules/payment/' . $class . '.php') ) {
        if ( !class_exists($class) ) {
          include(DIR_FS_CATALOG . 'includes/modules/payment/' . $class . '.php');
        }

        $module = new $class();

        if ( isset($module->signature) ) {
          $sig = explode('|', $module->signature);

          if ( isset($sig[0]) && ($sig[0] == 'paypal') && isset($sig[1]) && ($sig[1] == $class) && isset($sig[2]) ) {
            return version_compare($sig[2], 4) >= 0;
          }
        }
      }

      return false;
    }

    function migrate($OSCOM_PayPal) {
      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER') ) {
        $server = (MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER == 'Live') ? 'LIVE' : 'SANDBOX';

        if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_ID') ) {
          if ( tep_not_null(MODULE_PAYMENT_PAYPAL_STANDARD_ID) ) {
            if ( !defined('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL')) ) {
              $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL', MODULE_PAYMENT_PAYPAL_STANDARD_ID);
            }
          }

          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_ID');
        }

        if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID') ) {
          if ( tep_not_null(MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID) ) {
            if ( !defined('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL_PRIMARY') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL_PRIMARY')) ) {
              $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL_PRIMARY', MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID);
            }
          }

          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID');
        }
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_PAGE_STYLE') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PS_PAGE_STYLE', MODULE_PAYMENT_PAYPAL_STANDARD_PAGE_STYLE);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_PAGE_STYLE');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTION_METHOD') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PS_TRANSACTION_METHOD', (MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTION_METHOD == 'Sale') ? '1' : '0');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTION_METHOD');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PS_PREPARE_ORDER_STATUS_ID', MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PS_ORDER_STATUS_ID', MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_ZONE') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PS_ZONE', MODULE_PAYMENT_PAYPAL_STANDARD_ZONE);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_ZONE');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_SORT_ORDER') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PS_SORT_ORDER', MODULE_PAYMENT_PAYPAL_STANDARD_SORT_ORDER, 'Sort Order', 'Sort order of display (lowest to highest).');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_SORT_ORDER');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTIONS_ORDER_STATUS_ID') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTIONS_ORDER_STATUS_ID');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_STATUS') ) {
        $status = '-1';

        if ( (MODULE_PAYMENT_PAYPAL_STANDARD_STATUS == 'True') && defined('MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER') ) {
          if ( MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER == 'Live' ) {
            $status = '1';
          } else {
            $status = '0';
          }
        }

        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PS_STATUS', $status);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_STATUS');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_VERIFY_SSL') ) {
        if ( !defined('OSCOM_APP_PAYPAL_VERIFY_SSL') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_VERIFY_SSL', (MODULE_PAYMENT_PAYPAL_STANDARD_VERIFY_SSL == 'True') ? '1' : '0');
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_VERIFY_SSL');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_PROXY') ) {
        if ( !defined('OSCOM_APP_PAYPAL_PROXY') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PROXY', MODULE_PAYMENT_PAYPAL_STANDARD_PROXY);
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_PROXY');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_STATUS') ) {
        if ( !defined('OSCOM_APP_PAYPAL_PS_EWP_STATUS') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PS_EWP_STATUS', (MODULE_PAYMENT_PAYPAL_STANDARD_EWP_STATUS == 'True') ? '1' : '-1');
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_STATUS');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PRIVATE_KEY') ) {
        if ( !defined('OSCOM_APP_PAYPAL_PS_EWP_PRIVATE_KEY') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PS_EWP_PRIVATE_KEY', MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PRIVATE_KEY);
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PRIVATE_KEY');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PUBLIC_KEY') ) {
        if ( !defined('OSCOM_APP_PAYPAL_PS_EWP_PUBLIC_CERT') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PS_EWP_PUBLIC_CERT', MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PUBLIC_KEY);
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PUBLIC_KEY');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_CERT_ID') ) {
        if ( !defined('OSCOM_APP_PAYPAL_PS_EWP_PUBLIC_CERT_ID') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PS_EWP_PUBLIC_CERT_ID', MODULE_PAYMENT_PAYPAL_STANDARD_EWP_CERT_ID);
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_CERT_ID');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PAYPAL_KEY') ) {
        if ( !defined('OSCOM_APP_PAYPAL_PS_EWP_PAYPAL_CERT') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PS_EWP_PAYPAL_CERT', MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PAYPAL_KEY);
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PAYPAL_KEY');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY') ) {
        if ( !defined('OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY', MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY);
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_OPENSSL') ) {
        if ( !defined('OSCOM_APP_PAYPAL_PS_EWP_OPENSSL') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PS_EWP_OPENSSL', MODULE_PAYMENT_PAYPAL_STANDARD_EWP_OPENSSL);
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_OPENSSL');
      }
    }
  }
?>
