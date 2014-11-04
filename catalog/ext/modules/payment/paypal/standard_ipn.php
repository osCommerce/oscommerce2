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

  if (!defined('MODULE_PAYMENT_PAYPAL_STANDARD_STATUS') || (MODULE_PAYMENT_PAYPAL_STANDARD_STATUS  != 'True')) {
    exit;
  }

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/paypal_standard.php');
  require('includes/modules/payment/paypal_standard.php');

  $result = false;

  if ( isset($_POST['receiver_email']) && (($_POST['receiver_email'] == MODULE_PAYMENT_PAYPAL_STANDARD_ID) || (defined('MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID') && osc_not_null(MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID) && ($_POST['receiver_email'] == MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID))) ) {
    $paypal_standard = new paypal_standard();

    $parameters = 'cmd=_notify-validate';

    foreach ($_POST as $key => $value) {
      $parameters .= '&' . $key . '=' . urlencode(stripslashes($value));
    }

    $result = $paypal_standard->sendTransactionToGateway($paypal_standard->form_action_url, $parameters);
  }

  if ( $result == 'VERIFIED' ) {
    $paypal_standard->verifyTransaction(true);
  } else {
    $paypal_standard->sendDebugEmail($result, true);
  }

  tep_session_destroy();

  require('includes/application_bottom.php');
?>
