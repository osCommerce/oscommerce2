<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2009 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');
  require('../includes/modules/payment/paypal_express.php');

  if (isset($HTTP_GET_VARS['action']) && ($HTTP_GET_VARS['action'] == 'test')) {
    if (defined(MODULE_PAYMENT_PAYPAL_EXPRESS_STATUS)) {
      $paypal_express = new paypal_express();

      $params = array('CURRENCYCODE' => DEFAULT_CURRENCY,
                      'AMT' => '1.00');

      $response_array = $paypal_express->setExpressCheckout($params);

      if (($response_array['ACK'] == 'Success') || ($response_array['ACK'] == 'SuccessWithWarning')) {
        echo '<h1>Success!</h1>';

        if (MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER == 'Live') {
          echo '<p>The PayPal Express Checkout credentials have been set up correctly for live production use.</p>';
        } else {
          echo '<p>The PayPal Express Checkout credentials have been set up correctly for the sandbox environment.</p>';
        }
      } else {
        echo '<h1>Failure!</h1>';

        if (MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER == 'Live') {
          echo '<p>The PayPal Express Checkout credentials are not set up correctly for live production use.</p>';
        } else {
          echo '<p>The PayPal Express Checkout credentials are not set up correctly for the sandbox environment.</p>';
        }

        echo '<pre>';
        var_dump($response_array);
        echo '</pre>';
      }
    } else {
      echo '<p>The PayPal Express Checkout payment module is not yet installed. Please install it to verify your API credentials.</p>';
    }
  }
?>
