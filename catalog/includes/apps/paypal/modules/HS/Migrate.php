<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_HS_Migrate {
    function OSCOM_PayPal_HS_Migrate($OSCOM_PayPal) {
      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER') ) {
        $server = (MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER == 'Live') ? 'LIVE' : 'SANDBOX';

        if ( defined('MODULE_PAYMENT_PAYPAL_PRO_HS_ID') ) {
          if ( tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_ID) ) {
            if ( !defined('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL')) ) {
              $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL', MODULE_PAYMENT_PAYPAL_PRO_HS_ID);
            }
          }

          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_ID');
        }

        if ( defined('MODULE_PAYMENT_PAYPAL_PRO_HS_PRIMARY_ID') ) {
          if ( tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_PRIMARY_ID) ) {
            if ( !defined('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL_PRIMARY') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL_PRIMARY')) ) {
              $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL_PRIMARY', MODULE_PAYMENT_PAYPAL_PRO_HS_PRIMARY_ID);
            }
          }

          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_PRIMARY_ID');
        }

        if ( defined('MODULE_PAYMENT_PAYPAL_PRO_HS_API_USERNAME') && defined('MODULE_PAYMENT_PAYPAL_PRO_HS_API_PASSWORD') && defined('MODULE_PAYMENT_PAYPAL_PRO_HS_API_SIGNATURE') ) {
          if ( tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_API_USERNAME) && tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_API_PASSWORD) && tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_API_SIGNATURE) ) {
            if ( !defined('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME')) ) {
              if ( !defined('OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD')) ) {
                if ( !defined('OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE')) ) {
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME', MODULE_PAYMENT_PAYPAL_PRO_HS_API_USERNAME);
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD', MODULE_PAYMENT_PAYPAL_PRO_HS_API_PASSWORD);
                  $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE', MODULE_PAYMENT_PAYPAL_PRO_HS_API_SIGNATURE);
                }
              }
            }
          }

          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_API_USERNAME');
          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_API_PASSWORD');
          $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_API_SIGNATURE');
        }
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTION_METHOD') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_HS_TRANSACTION_METHOD', (MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTION_METHOD == 'Sale') ? '1' : '0');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTION_METHOD');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_HS_PREPARE_ORDER_STATUS_ID') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_HS_PREPARE_ORDER_STATUS_ID', MODULE_PAYMENT_PAYPAL_PRO_HS_PREPARE_ORDER_STATUS_ID);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_PREPARE_ORDER_STATUS_ID');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_HS_ORDER_STATUS_ID') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_HS_ORDER_STATUS_ID', MODULE_PAYMENT_PAYPAL_PRO_HS_ORDER_STATUS_ID);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_ORDER_STATUS_ID');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_HS_ZONE') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_HS_ZONE', MODULE_PAYMENT_PAYPAL_PRO_HS_ZONE);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_ZONE');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_HS_SORT_ORDER') ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_HS_SORT_ORDER', MODULE_PAYMENT_PAYPAL_PRO_HS_SORT_ORDER, 'Sort Order', 'Sort order of display (lowest to highest).');
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_SORT_ORDER');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTIONS_ORDER_STATUS_ID') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTIONS_ORDER_STATUS_ID');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_HS_STATUS') ) {
        $status = '-1';

        if ( (MODULE_PAYMENT_PAYPAL_PRO_HS_STATUS == 'True') && defined('MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER') ) {
          if ( MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER == 'Live' ) {
            $status = '1';
          } else {
            $status = '0';
          }
        }

        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_HS_STATUS', $status);
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_STATUS');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_HS_VERIFY_SSL') ) {
        if ( !defined('OSCOM_APP_PAYPAL_VERIFY_SSL') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_VERIFY_SSL', (MODULE_PAYMENT_PAYPAL_PRO_HS_VERIFY_SSL == 'True') ? '1' : '0');
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_VERIFY_SSL');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_HS_PROXY') ) {
        if ( !defined('OSCOM_APP_PAYPAL_PROXY') ) {
          $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PROXY', MODULE_PAYMENT_PAYPAL_PRO_HS_PROXY);
        }

        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_PROXY');
      }

      if ( defined('MODULE_PAYMENT_PAYPAL_PRO_HS_DEBUG_EMAIL') ) {
        $OSCOM_PayPal->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_DEBUG_EMAIL');
      }
    }
  }
?>
