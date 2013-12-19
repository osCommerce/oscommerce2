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
  require('../includes/languages/' . $language . '/modules/payment/paypal_express.php');
  require('../includes/modules/payment/paypal_express.php');

  if (defined(MODULE_PAYMENT_PAYPAL_EXPRESS_STATUS)) {
    $paypal_express = new paypal_express();

    $params = array('PAYMENTREQUEST_0_CURRENCYCODE' => DEFAULT_CURRENCY,
                    'PAYMENTREQUEST_0_AMT' => '1.00');

    $response_array = $paypal_express->setExpressCheckout($params);

    if (($response_array['ACK'] == 'Success') || ($response_array['ACK'] == 'SuccessWithWarning')) {
      echo '<h1 id="ppctresult">' . MODULE_PAYMENT_PAYPAL_EXPRESS_DIALOG_CONNECTION_SUCCESS_TITLE . '</h1>';

      if (MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER == 'Live') {
        echo '<p>' . MODULE_PAYMENT_PAYPAL_EXPRESS_DIALOG_CONNECTION_SUCCESS_TEXT_LIVE . '</p>';
      } else {
        echo '<p>' . MODULE_PAYMENT_PAYPAL_EXPRESS_DIALOG_CONNECTION_SUCCESS_TEXT_TEST . '</p>';
      }
    } else {
      echo '<h1 id="ppctresult">' . MODULE_PAYMENT_PAYPAL_EXPRESS_DIALOG_CONNECTION_ERROR_TITLE . '</h1>';

      if (MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER == 'Live') {
        echo '<p>' . MODULE_PAYMENT_PAYPAL_EXPRESS_DIALOG_CONNECTION_ERROR_TEXT_LIVE . '</p>';
      } else {
        echo '<p>' . MODULE_PAYMENT_PAYPAL_EXPRESS_DIALOG_CONNECTION_ERROR_TEXT_TEST . '</p>';
      }
    }
  } else {
    echo '<h1 id="ppctresult">' . MODULE_PAYMENT_PAYPAL_EXPRESS_DIALOG_CONNECTION_TITLE_ERROR . '</h1>' .
         '<p>' . MODULE_PAYMENT_PAYPAL_EXPRESS_DIALOG_CONNECTION_NOT_INSTALLED . '</p>';
  }
?>
