<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2017 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_EC {
    var $_title;
    var $_short_title;
    var $_introduction;
    var $_req_notes;
    var $_pm_code = 'paypal_express';
    var $_pm_pf_code = 'paypal_pro_payflow_ec';
    var $_sort_order = 100;

    function OSCOM_PayPal_EC() {
      global $OSCOM_PayPal;

      $this->_title = $OSCOM_PayPal->getDef('module_ec_title');
      $this->_short_title = $OSCOM_PayPal->getDef('module_ec_short_title');
      $this->_introduction = $OSCOM_PayPal->getDef('module_ec_introduction');

      $this->_req_notes = array();

      if ( !function_exists('curl_init') ) {
        $this->_req_notes[] = $OSCOM_PayPal->getDef('module_ec_error_curl');
      }

      if ( defined('OSCOM_APP_PAYPAL_GATEWAY') ) {
        if ( (OSCOM_APP_PAYPAL_GATEWAY == '1') && !$OSCOM_PayPal->hasCredentials('EC') ) { // PayPal
          $this->_req_notes[] = $OSCOM_PayPal->getDef('module_ec_error_credentials');
        } elseif ( (OSCOM_APP_PAYPAL_GATEWAY == '0') && !$OSCOM_PayPal->hasCredentials('EC', 'payflow') ) { // Payflow
          $this->_req_notes[] = $OSCOM_PayPal->getDef('module_ec_error_credentials_payflow');
        }
      }
    }

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
      return $this->doMigrationCheck($this->_pm_code) || $this->doMigrationCheck($this->_pm_pf_code);
    }

    function doMigrationCheck($class) {
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
      $is_payflow = false;

      if ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER') ) {
        $server = (MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER == 'Live') ? 'LIVE' : 'SANDBOX';

        if ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_SELLER_ACCOUNT') ) {
          if ( tep_not_null(MODULE_PAYMENT_PAYPAL_EXPRESS_SELLER_ACCOUNT) ) {
            if ( !defined('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL')) ) {
              $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL', MODULE_PAYMENT_PAYPAL_EXPRESS_SELLER_ACCOUNT);
            }
          }

          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_SELLER_ACCOUNT');
        }

        if ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_API_USERNAME') && defined('MODULE_PAYMENT_PAYPAL_EXPRESS_API_PASSWORD') && defined('MODULE_PAYMENT_PAYPAL_EXPRESS_API_SIGNATURE') ) {
          if ( tep_not_null(MODULE_PAYMENT_PAYPAL_EXPRESS_API_USERNAME) && tep_not_null(MODULE_PAYMENT_PAYPAL_EXPRESS_API_PASSWORD) && tep_not_null(MODULE_PAYMENT_PAYPAL_EXPRESS_API_SIGNATURE) ) {
            if ( !defined('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME')) ) {
              if ( !defined('OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD')) ) {
                if ( !defined('OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE')) ) {
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME', MODULE_PAYMENT_PAYPAL_EXPRESS_API_USERNAME);
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD', MODULE_PAYMENT_PAYPAL_EXPRESS_API_PASSWORD);
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE', MODULE_PAYMENT_PAYPAL_EXPRESS_API_SIGNATURE);
                }
              }
            }
          }

          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_API_USERNAME');
          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_API_PASSWORD');
          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_API_SIGNATURE');
        }
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTION_SERVER') ) {
        $is_payflow = true;

        $server = (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTION_SERVER == 'Live') ? 'LIVE' : 'SANDBOX';

        if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_VENDOR') && defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_USERNAME') && defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PASSWORD') && defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PARTNER') ) {
          if ( tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_VENDOR) && tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PASSWORD) && tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PARTNER) ) {
            if ( !defined('OSCOM_APP_PAYPAL_PF_' . $server . '_VENDOR') || !tep_not_null(constant('OSCOM_APP_PAYPAL_PF_' . $server . '_VENDOR')) ) {
              if ( !defined('OSCOM_APP_PAYPAL_PF_' . $server . '_PASSWORD') || !tep_not_null(constant('OSCOM_APP_PAYPAL_PF_' . $server . '_PASSWORD')) ) {
                if ( !defined('OSCOM_APP_PAYPAL_PF_' . $server . '_PARTNER') || !tep_not_null(constant('OSCOM_APP_PAYPAL_PF_' . $server . '_PARTNER')) ) {
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PF_' . $server . '_VENDOR', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_VENDOR);
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PF_' . $server . '_USER', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_USERNAME);
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PF_' . $server . '_PASSWORD', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PASSWORD);
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PF_' . $server . '_PARTNER', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PARTNER);
                }
              }
            }
          }

          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_VENDOR');
          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_USERNAME');
          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PASSWORD');
          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PARTNER');
        }
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_ACCOUNT_OPTIONAL') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_EC_ACCOUNT_OPTIONAL', (MODULE_PAYMENT_PAYPAL_EXPRESS_ACCOUNT_OPTIONAL == 'True') ? '1' : '0');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_ACCOUNT_OPTIONAL');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_INSTANT_UPDATE') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_EC_INSTANT_UPDATE', (MODULE_PAYMENT_PAYPAL_EXPRESS_INSTANT_UPDATE == 'True') ? '1' : '0');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_INSTANT_UPDATE');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_IMAGE') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_EC_CHECKOUT_IMAGE', (MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_IMAGE == 'Static') ? '0' : '1');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_IMAGE');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_PAGE_STYLE') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_PAGE_STYLE');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PAGE_STYLE') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PAGE_STYLE');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_METHOD') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_EC_TRANSACTION_METHOD', (MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_METHOD == 'Sale') ? '1' : '0');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_METHOD');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTION_METHOD') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_EC_TRANSACTION_METHOD', (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTION_METHOD == 'Sale') ? '1' : '0');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTION_METHOD');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_ORDER_STATUS_ID') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_EC_ORDER_STATUS_ID', MODULE_PAYMENT_PAYPAL_EXPRESS_ORDER_STATUS_ID);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_ORDER_STATUS_ID');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ORDER_STATUS_ID') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_EC_ORDER_STATUS_ID', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ORDER_STATUS_ID);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ORDER_STATUS_ID');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_ZONE') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_EC_ZONE', MODULE_PAYMENT_PAYPAL_EXPRESS_ZONE);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_ZONE');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ZONE') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_EC_ZONE', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ZONE);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ZONE');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_SORT_ORDER') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_EC_SORT_ORDER', MODULE_PAYMENT_PAYPAL_EXPRESS_SORT_ORDER, 'Sort Order', 'Sort order of display (lowest to highest).');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_SORT_ORDER');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_SORT_ORDER') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_EC_SORT_ORDER', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_SORT_ORDER, 'Sort Order', 'Sort order of display (lowest to highest).');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_SORT_ORDER');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTIONS_ORDER_STATUS_ID') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTIONS_ORDER_STATUS_ID');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTIONS_ORDER_STATUS_ID') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTIONS_ORDER_STATUS_ID');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_STATUS') ) {
        $status = '-1';

        if ( (MODULE_PAYMENT_PAYPAL_EXPRESS_STATUS == 'True') && defined('MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER') ) {
          if ( MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER == 'Live' ) {
            $status = '1';
          } else {
            $status = '0';
          }
        }

        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_EC_STATUS', $status);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_STATUS');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_STATUS') ) {
        $status = '-1';

        if ( (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_STATUS == 'True') && defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTION_SERVER') ) {
          if ( MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTION_SERVER == 'Live' ) {
            $status = '1';
          } else {
            $status = '0';
          }
        }

        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_EC_STATUS', $status);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_STATUS');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTION_SERVER') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TRANSACTION_SERVER');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_VERIFY_SSL') ) {
        if ( !defined('OSCOM_APP_PAYPAL_VERIFY_SSL') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_VERIFY_SSL', (MODULE_PAYMENT_PAYPAL_EXPRESS_VERIFY_SSL == 'True') ? '1' : '0');
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_VERIFY_SSL');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_VERIFY_SSL') ) {
        if ( !defined('OSCOM_APP_PAYPAL_VERIFY_SSL') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_VERIFY_SSL', (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_VERIFY_SSL == 'True') ? '1' : '0');
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_VERIFY_SSL');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_PROXY') ) {
        if ( !defined('OSCOM_APP_PAYPAL_PROXY') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PROXY', MODULE_PAYMENT_PAYPAL_EXPRESS_PROXY);
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_PROXY');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PROXY') ) {
        if ( !defined('OSCOM_APP_PAYPAL_PROXY') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PROXY', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PROXY);
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_PROXY');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_DEBUG_EMAIL') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_DEBUG_EMAIL');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_DEBUG_EMAIL') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_DEBUG_EMAIL');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_FLOW') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_FLOW');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_EXPRESS_DISABLE_IE_COMPAT') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_EXPRESS_DISABLE_IE_COMPAT');
      }

      if ( $is_payflow === true ) {
        $installed = explode(';', MODULE_PAYMENT_INSTALLED);
        $installed_pos = array_search($this->_pm_pf_code . '.php', $installed);

        if ( $installed_pos !== false ) {
          unset($installed[$installed_pos]);

          $OSCOM_PayPal->saveParameter('MODULE_PAYMENT_INSTALLED', implode(';', $installed));
        }
      }
    }
  }
?>
