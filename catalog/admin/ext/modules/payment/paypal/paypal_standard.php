<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');
  require('../includes/languages/' . $language . '/modules/payment/paypal_standard.php');
  require('../includes/modules/payment/paypal_standard.php');

  if (defined(MODULE_PAYMENT_PAYPAL_STANDARD_STATUS)) {
    $paypal_standard = new paypal_standard();

    $parameters = 'cmd=_notify-validate&business=' . urlencode(MODULE_PAYMENT_PAYPAL_STANDARD_ID);

    $result = $paypal_standard->sendTransactionToGateway($paypal_standard->form_action_url, $parameters);

    if ( $result == 'INVALID' ) {
      echo '<h1 id="ppctresult">' . MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_SUCCESS_TITLE . '</h1>';

      if (MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTION_SERVER == 'Live') {
        echo '<p>' . MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_SUCCESS_TEXT_LIVE . '</p>';
      } else {
        echo '<p>' . MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_SUCCESS_TEXT_TEST . '</p>';
      }
    } else {
      echo '<h1 id="ppctresult">' . MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_ERROR_TITLE . '</h1>';

      if (MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTION_SERVER == 'Live') {
        echo '<p>' . MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_ERROR_TEXT_LIVE . '</p>';
      } else {
        echo '<p>' . MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_ERROR_TEXT_TEST . '</p>';
      }
    }
  } else {
    echo '<h1 id="ppctresult">' . MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_TITLE_ERROR . '</h1>' .
         '<p>' . MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_NOT_INSTALLED . '</p>';
  }
?>
