<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  chdir('../../../../');
  require('includes/application_top.php');

  if ( !defined('MODULE_PAYMENT_SAGE_PAY_SERVER_STATUS') || (MODULE_PAYMENT_SAGE_PAY_SERVER_STATUS != 'True') ) {
    exit;
  }

  $OSCOM_Language->loadDefinitions('modules/payment/sage_pay_server');
  include('includes/modules/payment/sage_pay_server.php');
  $sage_pay_server = new sage_pay_server();

  $result = null;

  if ( isset($_GET['skcode']) && isset($_POST['VPSSignature']) && isset($_POST['VPSTxId']) && isset($_POST['VendorTxCode']) && isset($_POST['Status']) ) {
    $skcode = HTML::sanitize($_GET['skcode']);

    $Qsp = $OSCOM_Db->get('sagepay_server_securitykeys', 'securitykey', ['code' => $skcode], null, 1);

    if ($Qsp->fetch() !== false) {
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

      $sig .= $Qsp->value('securitykey');

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

          $transaction_details_string = HTML::sanitize($transaction_details_string);

          $OSCOM_Db->save('sagepay_server_securitykeys', ['verified' => 1, 'transaction_details' => $transaction_details_string], ['code' => $skcode]);

          $result = 'Status=OK' . chr(13) . chr(10) .
                    'RedirectURL=' . $sage_pay_server->formatURL(OSCOM::link('checkout_process.php', 'check=PROCESS&skcode=' . $skcode, false));
        } else {
          $error = isset($_POST['StatusDetail']) ? $sage_pay_server->getErrorMessageNumber($_POST['StatusDetail']) : null;

          if ( MODULE_PAYMENT_SAGE_PAY_SERVER_PROFILE_PAGE == 'Normal' ) {
            $error_url = OSCOM::link('checkout_payment.php', 'payment_error=' . $sage_pay_server->code . (tep_not_null($error) ? '&error=' . $error : ''), false);
          } else {
            $error_url = OSCOM::link('ext/modules/payment/sage_pay/redirect.php', 'payment_error=' . $sage_pay_server->code . (tep_not_null($error) ? '&error=' . $error : ''), false);
          }

          $result = 'Status=OK' . chr(13) . chr(10) .
                    'RedirectURL=' . $sage_pay_server->formatURL($error_url);

          $OSCOM_Db->delete('sagepay_server_securitykeys', ['code' => $skcode]);

          $sage_pay_server->sendDebugEmail();
        }
      } else {
        $result = 'Status=INVALID' . chr(13) . chr(10) .
                  'RedirectURL=' . $sage_pay_server->formatURL(OSCOM::link('shopping_cart.php', '', false));

        $sage_pay_server->sendDebugEmail();
      }
    }
  }

  if ( !isset($result) ) {
    $result = 'Status=ERROR' . chr(13) . chr(10) .
              'RedirectURL=' . $sage_pay_server->formatURL(OSCOM::link('shopping_cart.php', '', false));
  }

  echo $result;

  Registry::get('Session')->kill();

  exit;

  require('includes/application_bottom.php');
?>
