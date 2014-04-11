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

  require(DIR_WS_LANGUAGES . $language . '/modules/payment/paypal_standard.php');
  require('includes/modules/payment/paypal_standard.php');

  $result = false;

  if ( isset($HTTP_POST_VARS['receiver_email']) && (($HTTP_POST_VARS['receiver_email'] == MODULE_PAYMENT_PAYPAL_STANDARD_ID) || (defined('MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID') && tep_not_null(MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID) && ($HTTP_POST_VARS['receiver_email'] == MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID))) ) {
    $paypal_standard = new paypal_standard();

    $parameters = 'cmd=_notify-validate';

    foreach ($HTTP_POST_VARS as $key => $value) {
      $parameters .= '&' . $key . '=' . urlencode(stripslashes($value));
    }

    $result = $paypal_standard->sendTransactionToGateway($paypal_standard->form_action_url, $parameters);
  }

  if ( $result == 'VERIFIED' ) {
    $paypal_standard->verifyTransaction(true);
  } else {
    if (tep_not_null(MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL)) {
      $email_body = $result . "\n\n" .
                    '$HTTP_POST_VARS:' . "\n\n";

      foreach ($HTTP_POST_VARS as $key => $value) {
        $email_body .= $key . '=' . $value . "\n";
      }

      $email_body .= "\n" . '$HTTP_GET_VARS:' . "\n\n";

      foreach ($HTTP_GET_VARS as $key => $value) {
        $email_body .= $key . '=' . $value . "\n";
      }

      tep_mail('', MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL, 'PayPal IPN Invalid Process', $email_body, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
    }
  }

  require('includes/application_bottom.php');
?>
