<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_Braintree_CC {
    var $_title;
    var $_short_title;
    var $_req_notes;
    var $_pm_code = 'braintree_cc';
    var $_sort_order = 100;

    function OSCOM_Braintree_CC() {
      global $OSCOM_Braintree;

      $this->_title = $OSCOM_Braintree->getDef('module_cc_title');
      $this->_short_title = $OSCOM_Braintree->getDef('module_cc_short_title');

      $this->_req_notes = array();

      if ( version_compare(PHP_VERSION, '5.4.0', '<') ) {
        $this->_req_notes[] = $OSCOM_Braintree->getDef('module_cc_error_php', array('version' => '5.4.0'));
      }

      $requiredExtensions = array('xmlwriter', 'openssl', 'dom', 'hash', 'curl');

      $exts = array();

      foreach ( $requiredExtensions as $ext ) {
        if ( !extension_loaded($ext) ) {
          $exts[] = $ext;
        }
      }

      if ( !empty($exts) ) {
        $this->_req_notes[] = $OSCOM_Braintree->getDef('module_cc_error_php_ext', array('ext' => implode('<br />', $exts)));
      }

      if ( defined('OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS') ) {
        $warning = false;

        if (OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS == '1') {
          if ( !tep_not_null(OSCOM_APP_PAYPAL_BRAINTREE_MERCHANT_ID) || !tep_not_null(OSCOM_APP_PAYPAL_BRAINTREE_PUBLIC_KEY) || !tep_not_null(OSCOM_APP_PAYPAL_BRAINTREE_PRIVATE_KEY) ) {
            $warning = true;
          }
        } elseif (OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS == '0') {
          if ( !tep_not_null(OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_MERCHANT_ID) || !tep_not_null(OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_PUBLIC_KEY) || !tep_not_null(OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_PRIVATE_KEY) ) {
            $warning = true;
          }
        }

        if ($warning === true) {
          $this->_req_notes[] = $OSCOM_Braintree->getDef('module_cc_error_credentials');
        }
      }

      if ( defined('OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS') ) {
        $ma = null;

        if (OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS == '1') {
          if ( defined('OSCOM_APP_PAYPAL_BRAINTREE_CURRENCIES_MA') ) {
            $ma = OSCOM_APP_PAYPAL_BRAINTREE_CURRENCIES_MA;
          }
        } elseif (OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS == '0') {
          if ( defined('OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_CURRENCIES_MA') ) {
            $ma = OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_CURRENCIES_MA;
          }
        }

        if (isset($ma)) {
          $ma_error = true;

          if ( tep_not_null($ma) ) {
            $mas = explode(';', $ma);

            foreach ( $mas as $a ) {
              $ac = explode(':', $a, 2);

              if ( isset($ac[1]) && ($ac[1] == DEFAULT_CURRENCY) ) {
                $ma_error = false;
                break;
              }
            }
          }

          if ( $ma_error === true ) {
            $this->_req_notes[] = $OSCOM_Braintree->getDef('module_cc_error_merchant_accounts_currency', array('currency' => DEFAULT_CURRENCY));
          }
        }
      }
    }

    function getTitle() {
      return $this->_title;
    }

    function getShortTitle() {
      return $this->_short_title;
    }

    function install($OSCOM_Braintree) {
      $installed = explode(';', MODULE_PAYMENT_INSTALLED);
      $installed[] = $this->_pm_code . '.php';

      $OSCOM_Braintree->saveParameter('MODULE_PAYMENT_INSTALLED', implode(';', $installed));
    }

    function uninstall($OSCOM_Braintree) {
      $installed = explode(';', MODULE_PAYMENT_INSTALLED);
      $installed_pos = array_search($this->_pm_code . '.php', $installed);

      if ( $installed_pos !== false ) {
        unset($installed[$installed_pos]);

        $OSCOM_Braintree->saveParameter('MODULE_PAYMENT_INSTALLED', implode(';', $installed));
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

          if ( isset($sig[0]) && ($sig[0] == 'braintree') && isset($sig[1]) && ($sig[1] == $class) && isset($sig[2]) ) {
            return version_compare($sig[2], 2) >= 0;
          }
        }
      }

      return false;
    }

    function migrate($OSCOM_PayPal) {
      if ( defined('MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_SERVER') ) {
        $server = (MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_SERVER == 'Live') ? '' : 'SANDBOX_';

        if ( defined('MODULE_PAYMENT_BRAINTREE_CC_MERCHANT_ID') && defined('MODULE_PAYMENT_BRAINTREE_CC_PUBLIC_KEY') && defined('MODULE_PAYMENT_BRAINTREE_CC_PRIVATE_KEY') ) {
          if ( tep_not_null(MODULE_PAYMENT_BRAINTREE_CC_MERCHANT_ID) && tep_not_null(MODULE_PAYMENT_BRAINTREE_CC_PUBLIC_KEY) && tep_not_null(MODULE_PAYMENT_BRAINTREE_CC_PRIVATE_KEY) ) {
            if ( !defined('OSCOM_APP_PAYPAL_BRAINTREE_' . $server . 'MERCHANT_ID') || !tep_not_null(constant('OSCOM_APP_PAYPAL_BRAINTREE_' . $server . 'MERCHANT_ID')) ) {
              if ( !defined('OSCOM_APP_PAYPAL_BRAINTREE_' . $server . 'PUBLIC_KEY') || !tep_not_null(constant('OSCOM_APP_PAYPAL_BRAINTREE_' . $server . 'PUBLIC_KEY')) ) {
                if ( !defined('OSCOM_APP_PAYPAL_BRAINTREE_' . $server . 'PRIVATE_KEY') || !tep_not_null(constant('OSCOM_APP_PAYPAL_BRAINTREE_' . $server . 'PRIVATE_KEY')) ) {
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_BRAINTREE_' . $server . 'MERCHANT_ID', MODULE_PAYMENT_BRAINTREE_CC_MERCHANT_ID);
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_BRAINTREE_' . $server . 'PUBLIC_KEY', MODULE_PAYMENT_BRAINTREE_CC_PUBLIC_KEY);
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_BRAINTREE_' . $server . 'PRIVATE_KEY', MODULE_PAYMENT_BRAINTREE_CC_PRIVATE_KEY);
                }
              }
            }
          }

          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_BRAINTREE_CC_MERCHANT_ID');
          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_BRAINTREE_CC_PUBLIC_KEY');
          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_BRAINTREE_CC_PRIVATE_KEY');
          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_BRAINTREE_CC_CLIENT_KEY');
        }
      }

      if ( defined('MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_SERVER') ) {
        $server = (MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_SERVER == 'Live') ? '' : 'SANDBOX_';

        if ( defined('MODULE_PAYMENT_BRAINTREE_CC_MERCHANT_ACCOUNTS') ) {
          if ( tep_not_null(MODULE_PAYMENT_BRAINTREE_CC_MERCHANT_ACCOUNTS) ) {
            if ( !defined('OSCOM_APP_PAYPAL_BRAINTREE_' . $server . 'CURRENCIES_MA') || !tep_not_null(constant('OSCOM_APP_PAYPAL_BRAINTREE_' . $server . 'CURRENCIES_MA')) ) {
              $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_BRAINTREE_' . $server . 'CURRENCIES_MA', MODULE_PAYMENT_BRAINTREE_CC_MERCHANT_ACCOUNTS);
            }
          }

          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_BRAINTREE_CC_MERCHANT_ACCOUNTS');
        }
      }

      if ( defined('MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_METHOD') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_BRAINTREE_CC_TRANSACTION_METHOD', (MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_METHOD == 'Payment') ? '1' : '0');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_METHOD');
      }

      if ( defined('MODULE_PAYMENT_BRAINTREE_CC_ORDER_STATUS_ID') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_BRAINTREE_CC_ORDER_STATUS_ID', MODULE_PAYMENT_BRAINTREE_CC_ORDER_STATUS_ID);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_BRAINTREE_CC_ORDER_STATUS_ID');
      }

      if ( defined('MODULE_PAYMENT_BRAINTREE_CC_ZONE') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_BRAINTREE_CC_ZONE', MODULE_PAYMENT_BRAINTREE_CC_ZONE);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_BRAINTREE_CC_ZONE');
      }

      if ( defined('MODULE_PAYMENT_BRAINTREE_CC_SORT_ORDER') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_BRAINTREE_SORT_ORDER', MODULE_PAYMENT_BRAINTREE_CC_SORT_ORDER, 'Sort Order', 'Sort order of display (lowest to highest).');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_BRAINTREE_CC_SORT_ORDER');
      }

      if ( defined('MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_ORDER_STATUS_ID') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_ORDER_STATUS_ID');
      }

      if ( defined('MODULE_PAYMENT_BRAINTREE_CC_STATUS') ) {
        $status = '-1';

        if ( (MODULE_PAYMENT_BRAINTREE_CC_STATUS == 'True') && defined('MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_SERVER') ) {
          if ( MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_SERVER == 'Live' ) {
            $status = '1';
          } else {
            $status = '0';
          }
        }

        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS', $status);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_BRAINTREE_CC_STATUS');
      }

      if ( defined('MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_SERVER') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_BRAINTREE_CC_TRANSACTION_SERVER');
      }

      if ( defined('MODULE_PAYMENT_BRAINTREE_CC_TOKENS') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_BRAINTREE_CC_CC_TOKENS', (MODULE_PAYMENT_BRAINTREE_CC_TOKENS == 'True') ? '1' : '0');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_BRAINTREE_CC_TOKENS');
      }

      if ( defined('MODULE_PAYMENT_BRAINTREE_CC_VERIFY_WITH_CVV') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_BRAINTREE_CC_VERIFY_CVV', (MODULE_PAYMENT_BRAINTREE_CC_VERIFY_WITH_CVV == 'True') ? '1' : '0');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_BRAINTREE_CC_VERIFY_WITH_CVV');
      }
    }
  }
?>
