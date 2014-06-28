<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_DP_Migrate {
    function OSCOM_PayPal_DP_Migrate($OSCOM_PayPal) {
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

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_METHOD') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_DP_TRANSACTION_METHOD', (MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_METHOD == 'Sale') ? '1' : '0');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_METHOD');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_ORDER_STATUS_ID') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_DP_ORDER_STATUS_ID', MODULE_PAYMENT_PAYPAL_PRO_DP_ORDER_STATUS_ID);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_ORDER_STATUS_ID');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_ZONE') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_DP_ZONE', MODULE_PAYMENT_PAYPAL_PRO_DP_ZONE);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_ZONE');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_SORT_ORDER') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_DP_SORT_ORDER', MODULE_PAYMENT_PAYPAL_PRO_DP_SORT_ORDER, 'Sort Order', 'Sort order of display (lowest to highest).');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_SORT_ORDER');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTIONS_ORDER_STATUS_ID') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTIONS_ORDER_STATUS_ID');
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

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER');
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

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_PROXY') ) {
        if ( !defined('OSCOM_APP_PAYPAL_PROXY') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PROXY', MODULE_PAYMENT_PAYPAL_PRO_DP_PROXY);
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_PROXY');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_DP_DEBUG_EMAIL') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_DP_DEBUG_EMAIL');
      }
    }
  }
?>
