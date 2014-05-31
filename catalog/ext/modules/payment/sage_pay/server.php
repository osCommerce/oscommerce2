<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');

  if ( !defined('MODULE_PAYMENT_SAGE_PAY_SERVER_STATUS') || (MODULE_PAYMENT_SAGE_PAY_SERVER_STATUS != 'True') ) {
    exit;
  }

  include(DIR_WS_LANGUAGES . $language . '/modules/payment/sage_pay_server.php');
  include('includes/modules/payment/sage_pay_server.php');
  $sage_pay_server = new sage_pay_server();

  $result = null;

  if ( isset($HTTP_GET_VARS['skcode']) && isset($HTTP_POST_VARS['VPSSignature']) && isset($HTTP_POST_VARS['VPSTxId']) && isset($HTTP_POST_VARS['VendorTxCode']) && isset($HTTP_POST_VARS['Status']) ) {
    $skcode = tep_db_prepare_input($HTTP_GET_VARS['skcode']);

    $sp_query = tep_db_query('select securitykey from sagepay_server_securitykeys where code = "' . tep_db_input($skcode) . '" limit 1');
    if ( tep_db_num_rows($sp_query) ) {
      $sp = tep_db_fetch_array($sp_query);

      $transaction_details = array('ID' => $HTTP_POST_VARS['VPSTxId']);

      $sig = $HTTP_POST_VARS['VPSTxId'] . $HTTP_POST_VARS['VendorTxCode'] . $HTTP_POST_VARS['Status'];

      if ( isset($HTTP_POST_VARS['TxAuthNo']) ) {
        $sig .= $HTTP_POST_VARS['TxAuthNo'];
      }

      $sig .= strtolower(substr(MODULE_PAYMENT_SAGE_PAY_SERVER_VENDOR_LOGIN_NAME, 0, 15));

      if ( isset($HTTP_POST_VARS['AVSCV2']) ) {
        $sig .= $HTTP_POST_VARS['AVSCV2'];

        $transaction_details['AVS/CV2'] = $HTTP_POST_VARS['AVSCV2'];
      }

      $sig .= $sp['securitykey'];

      if ( isset($HTTP_POST_VARS['AddressResult']) ) {
        $sig .= $HTTP_POST_VARS['AddressResult'];

        $transaction_details['Address'] = $HTTP_POST_VARS['AddressResult'];
      }

      if ( isset($HTTP_POST_VARS['PostCodeResult']) ) {
        $sig .= $HTTP_POST_VARS['PostCodeResult'];

        $transaction_details['Post Code'] = $HTTP_POST_VARS['PostCodeResult'];
      }

      if ( isset($HTTP_POST_VARS['CV2Result']) ) {
        $sig .= $HTTP_POST_VARS['CV2Result'];

        $transaction_details['CV2'] = $HTTP_POST_VARS['CV2Result'];
      }

      if ( isset($HTTP_POST_VARS['GiftAid']) ) {
        $sig .= $HTTP_POST_VARS['GiftAid'];
      }

      if ( isset($HTTP_POST_VARS['3DSecureStatus']) ) {
        $sig .= $HTTP_POST_VARS['3DSecureStatus'];

        $transaction_details['3D Secure'] = $HTTP_POST_VARS['3DSecureStatus'];
      }

      if ( isset($HTTP_POST_VARS['CAVV']) ) {
        $sig .= $HTTP_POST_VARS['CAVV'];
      }

      if ( isset($HTTP_POST_VARS['AddressStatus']) ) {
        $sig .= $HTTP_POST_VARS['AddressStatus'];

        $transaction_details['PayPal Payer Address'] = $HTTP_POST_VARS['AddressStatus'];
      }

      if ( isset($HTTP_POST_VARS['PayerStatus']) ) {
        $sig .= $HTTP_POST_VARS['PayerStatus'];

        $transaction_details['PayPal Payer Status'] = $HTTP_POST_VARS['PayerStatus'];
      }

      if ( isset($HTTP_POST_VARS['CardType']) ) {
        $sig .= $HTTP_POST_VARS['CardType'];

        $transaction_details['Card'] = $HTTP_POST_VARS['CardType'];
      }

      if ( isset($HTTP_POST_VARS['Last4Digits']) ) {
        $sig .= $HTTP_POST_VARS['Last4Digits'];
      }

      if ( isset($HTTP_POST_VARS['DeclineCode']) ) {
        $sig .= $HTTP_POST_VARS['DeclineCode'];
      }

      if ( isset($HTTP_POST_VARS['ExpiryDate']) ) {
        $sig .= $HTTP_POST_VARS['ExpiryDate'];
      }

      if ( isset($HTTP_POST_VARS['FraudResponse']) ) {
        $sig .= $HTTP_POST_VARS['FraudResponse'];
      }

      if ( isset($HTTP_POST_VARS['BankAuthCode']) ) {
        $sig .= $HTTP_POST_VARS['BankAuthCode'];
      }

      $sig = strtoupper(md5($sig));

      if ( $HTTP_POST_VARS['VPSSignature'] == $sig ) {
        if ( ($HTTP_POST_VARS['Status'] == 'OK') || ($HTTP_POST_VARS['Status'] == 'AUTHENTICATED') || ($HTTP_POST_VARS['Status'] == 'REGISTERED') ) {
          $transaction_details_string = '';

          foreach ( $transaction_details as $k => $v ) {
            $transaction_details_string .= $k . ': ' . $v . "\n";
          }

          $transaction_details_string = tep_db_prepare_input($transaction_details_string);

          tep_db_query('update sagepay_server_securitykeys set verified = 1, transaction_details = "' . tep_db_input($transaction_details_string) . '" where code = "' . tep_db_input($skcode) . '"');

          $result = 'Status=OK' . chr(13) . chr(10) .
                    'RedirectURL=' . $sage_pay_server->formatURL(tep_href_link(FILENAME_CHECKOUT_PROCESS, 'check=PROCESS&skcode=' . $skcode, 'SSL', false));
        } else {
          $error = isset($HTTP_POST_VARS['StatusDetail']) ? $sage_pay_server->getErrorMessageNumber($HTTP_POST_VARS['StatusDetail']) : null;

          if ( MODULE_PAYMENT_SAGE_PAY_SERVER_PROFILE_PAGE == 'Normal' ) {
            $error_url = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $sage_pay_server->code . (tep_not_null($error) ? '&error=' . $error : ''), 'SSL', false);
          } else {
            $error_url = tep_href_link('ext/modules/payment/sage_pay/redirect.php', 'payment_error=' . $sage_pay_server->code . (tep_not_null($error) ? '&error=' . $error : ''), 'SSL', false);
          }

          $result = 'Status=OK' . chr(13) . chr(10) .
                    'RedirectURL=' . $sage_pay_server->formatURL($error_url);

          tep_db_query('delete from sagepay_server_securitykeys where code = "' . tep_db_input($skcode) . '"');

          $sage_pay_server->sendDebugEmail();
        }
      } else {
        $result = 'Status=INVALID' . chr(13) . chr(10) .
                  'RedirectURL=' . $sage_pay_server->formatURL(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL', false));

        $sage_pay_server->sendDebugEmail();
      }
    }
  }

  if ( !isset($result) ) {
    $result = 'Status=ERROR' . chr(13) . chr(10) .
              'RedirectURL=' . $sage_pay_server->formatURL(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL', false));
  }

  echo $result;

  tep_session_destroy();

  exit;

  require('includes/application_bottom.php');
?>
