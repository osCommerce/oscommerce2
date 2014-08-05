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

  if ( isset($_GET['skcode']) && isset($_POST['VPSSignature']) && isset($_POST['VPSTxId']) && isset($_POST['VendorTxCode']) && isset($_POST['Status']) ) {
    $skcode = tep_db_prepare_input($_GET['skcode']);

    $sp_query = tep_db_query('select securitykey from sagepay_server_securitykeys where code = "' . tep_db_input($skcode) . '" limit 1');
    if ( tep_db_num_rows($sp_query) ) {
      $sp = tep_db_fetch_array($sp_query);

      $transaction_details = array('ID' => $_POST['VPSTxId']);

      $sig = $_POST['VPSTxId'] . $_POST['VendorTxCode'] . $_POST['Status'];

      if ( isset($_POST['TxAuthNo']) ) {
        $sig .= $_POST['TxAuthNo'];
      }

      $sig .= strtolower(substr(MODULE_PAYMENT_SAGE_PAY_SERVER_VENDOR_LOGIN_NAME, 0, 15));

      if ( isset($_POST['AVSCV2']) ) {
        $sig .= $_POST['AVSCV2'];

        $transaction_details['AVS/CV2'] = $_POST['AVSCV2'];
      }

      $sig .= $sp['securitykey'];

      if ( isset($_POST['AddressResult']) ) {
        $sig .= $_POST['AddressResult'];

        $transaction_details['Address'] = $_POST['AddressResult'];
      }

      if ( isset($_POST['PostCodeResult']) ) {
        $sig .= $_POST['PostCodeResult'];

        $transaction_details['Post Code'] = $_POST['PostCodeResult'];
      }

      if ( isset($_POST['CV2Result']) ) {
        $sig .= $_POST['CV2Result'];

        $transaction_details['CV2'] = $_POST['CV2Result'];
      }

      if ( isset($_POST['GiftAid']) ) {
        $sig .= $_POST['GiftAid'];
      }

      if ( isset($_POST['3DSecureStatus']) ) {
        $sig .= $_POST['3DSecureStatus'];

        $transaction_details['3D Secure'] = $_POST['3DSecureStatus'];
      }

      if ( isset($_POST['CAVV']) ) {
        $sig .= $_POST['CAVV'];
      }

      if ( isset($_POST['AddressStatus']) ) {
        $sig .= $_POST['AddressStatus'];

        $transaction_details['PayPal Payer Address'] = $_POST['AddressStatus'];
      }

      if ( isset($_POST['PayerStatus']) ) {
        $sig .= $_POST['PayerStatus'];

        $transaction_details['PayPal Payer Status'] = $_POST['PayerStatus'];
      }

      if ( isset($_POST['CardType']) ) {
        $sig .= $_POST['CardType'];

        $transaction_details['Card'] = $_POST['CardType'];
      }

      if ( isset($_POST['Last4Digits']) ) {
        $sig .= $_POST['Last4Digits'];
      }

      if ( isset($_POST['DeclineCode']) ) {
        $sig .= $_POST['DeclineCode'];
      }

      if ( isset($_POST['ExpiryDate']) ) {
        $sig .= $_POST['ExpiryDate'];
      }

      if ( isset($_POST['FraudResponse']) ) {
        $sig .= $_POST['FraudResponse'];
      }

      if ( isset($_POST['BankAuthCode']) ) {
        $sig .= $_POST['BankAuthCode'];
      }

      $sig = strtoupper(md5($sig));

      if ( $_POST['VPSSignature'] == $sig ) {
        if ( ($_POST['Status'] == 'OK') || ($_POST['Status'] == 'AUTHENTICATED') || ($_POST['Status'] == 'REGISTERED') ) {
          $transaction_details_string = '';

          foreach ( $transaction_details as $k => $v ) {
            $transaction_details_string .= $k . ': ' . $v . "\n";
          }

          $transaction_details_string = tep_db_prepare_input($transaction_details_string);

          tep_db_query('update sagepay_server_securitykeys set verified = 1, transaction_details = "' . tep_db_input($transaction_details_string) . '" where code = "' . tep_db_input($skcode) . '"');

          $result = 'Status=OK' . chr(13) . chr(10) .
                    'RedirectURL=' . $sage_pay_server->formatURL(tep_href_link(FILENAME_CHECKOUT_PROCESS, 'check=PROCESS&skcode=' . $skcode, 'SSL', false));
        } else {
          $error = isset($_POST['StatusDetail']) ? $sage_pay_server->getErrorMessageNumber($_POST['StatusDetail']) : null;

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
