<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2017 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_DP {
    var $_title;
    var $_short_title;
    var $_introduction;
    var $_req_notes;
    var $_pm_code = 'paypal_pro_dp';
    var $_pm_pf_code = 'paypal_pro_payflow_dp';
    var $_sort_order = 200;

    function OSCOM_PayPal_DP() {
      global $OSCOM_PayPal;

      $this->_title = $OSCOM_PayPal->getDef('module_dp_title');
      $this->_short_title = $OSCOM_PayPal->getDef('module_dp_short_title');
      $this->_introduction = $OSCOM_PayPal->getDef('module_dp_introduction');

      $this->_req_notes = array();

      if ( !function_exists('curl_init') ) {
        $this->_req_notes[] = $OSCOM_PayPal->getDef('module_dp_error_curl');
      }

      if ( defined('OSCOM_APP_PAYPAL_GATEWAY') ) {
        if ( (OSCOM_APP_PAYPAL_GATEWAY == '1') && !$OSCOM_PayPal->hasCredentials('DP') ) { // PayPal
          $this->_req_notes[] = $OSCOM_PayPal->getDef('module_dp_error_credentials');
        } elseif ( (OSCOM_APP_PAYPAL_GATEWAY == '0') && !$OSCOM_PayPal->hasCredentials('DP', 'payflow') ) { // Payflow
          $this->_req_notes[] = $OSCOM_PayPal->getDef('module_dp_error_credentials_payflow');
        }
      }

      if ( !$OSCOM_PayPal->isInstalled('EC') || !in_array(OSCOM_APP_PAYPAL_EC_STATUS, array('1', '0')) ) {
        $this->_req_notes[] = $OSCOM_PayPal->getDef('module_dp_error_express_module');
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

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER') ) {
        $server = (MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER == 'Live') ? 'LIVE' : 'SANDBOX';

        if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_API_USERNAME') && defined('MODULE_PAYMENT_PAYPAL_PRO_DP_API_PASSWORD') && defined('MODULE_PAYMENT_PAYPAL_PRO_DP_API_SIGNATURE') ) {
          if ( tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_DP_API_USERNAME) && tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_DP_API_PASSWORD) && tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_DP_API_SIGNATURE) ) {
            if ( !defined('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME')) ) {
              if ( !defined('OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD')) ) {
                if ( !defined('OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE')) ) {
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME', MODULE_PAYMENT_PAYPAL_PRO_DP_API_USERNAME);
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD', MODULE_PAYMENT_PAYPAL_PRO_DP_API_PASSWORD);
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE', MODULE_PAYMENT_PAYPAL_PRO_DP_API_SIGNATURE);
                }
              }
            }
          }

          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_API_USERNAME');
          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_API_PASSWORD');
          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_API_SIGNATURE');
        }
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_SERVER') ) {
        $is_payflow = true;

        $server = (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_SERVER == 'Live') ? 'LIVE' : 'SANDBOX';

        if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_VENDOR') && defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_USERNAME') && defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PASSWORD') && defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PARTNER') ) {
          if ( tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_VENDOR) && tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PASSWORD) && tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PARTNER) ) {
            if ( !defined('OSCOM_APP_PAYPAL_PF_' . $server . '_VENDOR') || !tep_not_null(constant('OSCOM_APP_PAYPAL_PF_' . $server . '_VENDOR')) ) {
              if ( !defined('OSCOM_APP_PAYPAL_PF_' . $server . '_PASSWORD') || !tep_not_null(constant('OSCOM_APP_PAYPAL_PF_' . $server . '_PASSWORD')) ) {
                if ( !defined('OSCOM_APP_PAYPAL_PF_' . $server . '_PARTNER') || !tep_not_null(constant('OSCOM_APP_PAYPAL_PF_' . $server . '_PARTNER')) ) {
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PF_' . $server . '_VENDOR', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_VENDOR);
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PF_' . $server . '_USER', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_USERNAME);
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PF_' . $server . '_PASSWORD', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PASSWORD);
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PF_' . $server . '_PARTNER', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PARTNER);
                }
              }
            }
          }

          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_VENDOR');
          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_USERNAME');
          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PASSWORD');
          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PARTNER');
        }
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_METHOD') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_DP_TRANSACTION_METHOD', (MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_METHOD == 'Sale') ? '1' : '0');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_METHOD');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_METHOD') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_DP_TRANSACTION_METHOD', (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_METHOD == 'Sale') ? '1' : '0');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_METHOD');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_ORDER_STATUS_ID') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_DP_ORDER_STATUS_ID', MODULE_PAYMENT_PAYPAL_PRO_DP_ORDER_STATUS_ID);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_ORDER_STATUS_ID');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ORDER_STATUS_ID') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_DP_ORDER_STATUS_ID', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ORDER_STATUS_ID);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ORDER_STATUS_ID');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_ZONE') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_DP_ZONE', MODULE_PAYMENT_PAYPAL_PRO_DP_ZONE);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_ZONE');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ZONE') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_DP_ZONE', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ZONE);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ZONE');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_SORT_ORDER') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_DP_SORT_ORDER', MODULE_PAYMENT_PAYPAL_PRO_DP_SORT_ORDER, 'Sort Order', 'Sort order of display (lowest to highest).');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_SORT_ORDER');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_SORT_ORDER') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_DP_SORT_ORDER', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_SORT_ORDER, 'Sort Order', 'Sort order of display (lowest to highest).');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_SORT_ORDER');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTIONS_ORDER_STATUS_ID') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTIONS_ORDER_STATUS_ID');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTIONS_ORDER_STATUS_ID') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTIONS_ORDER_STATUS_ID');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_STATUS') ) {
        $status = '-1';

        if ( (MODULE_PAYMENT_PAYPAL_PRO_DP_STATUS == 'True') && defined('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER') ) {
          if ( MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER == 'Live' ) {
            $status = '1';
          } else {
            $status = '0';
          }
        }

        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_DP_STATUS', $status);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_STATUS');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_STATUS') ) {
        $status = '-1';

        if ( (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_STATUS == 'True') && defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_SERVER') ) {
          if ( MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_SERVER == 'Live' ) {
            $status = '1';
          } else {
            $status = '0';
          }
        }

        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_DP_STATUS', $status);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_STATUS');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_SERVER') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_SERVER');
      }

      $cards = array('MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_VISA',
                     'MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_MASTERCARD',
                     'MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_DISCOVER',
                     'MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_AMEX',
                     'MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_MAESTRO');

      $cards_pass = true;

      foreach ( $cards as $c ) {
        if ( !defined($c) ) {
          $cards_pass = false;
          break;
        }
      }

      if ( $cards_pass === true ) {
        $cards_installed = array();

        foreach ( $cards as $c ) {
          if ( constant($c) == 'True' ) {
            $cards_installed[] = strtolower(substr($c, strrpos($c, '_')+1));
          }
        }

        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_DP_CARDS', implode(';', $cards_installed));
      }

      foreach ( $cards as $c ) {
        $OSCOM_PayPal->deleteParameter($c);
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_VERIFY_SSL') ) {
        if ( !defined('OSCOM_APP_PAYPAL_VERIFY_SSL') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_VERIFY_SSL', (MODULE_PAYMENT_PAYPAL_PRO_DP_VERIFY_SSL == 'True') ? '1' : '0');
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_VERIFY_SSL');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_VERIFY_SSL') ) {
        if ( !defined('OSCOM_APP_PAYPAL_VERIFY_SSL') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_VERIFY_SSL', (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_VERIFY_SSL == 'True') ? '1' : '0');
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_VERIFY_SSL');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_PROXY') ) {
        if ( !defined('OSCOM_APP_PAYPAL_PROXY') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PROXY', MODULE_PAYMENT_PAYPAL_PRO_DP_PROXY);
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_PROXY');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PROXY') ) {
        if ( !defined('OSCOM_APP_PAYPAL_PROXY') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PROXY', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PROXY);
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PROXY');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_DEBUG_EMAIL') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_DEBUG_EMAIL');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_DEBUG_EMAIL') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_DEBUG_EMAIL');
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
