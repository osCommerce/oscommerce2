<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../../');
  require('includes/application_top.php');

  require('includes/languages/' . $language . '/modules/payment/stripe.php');
  require('includes/modules/payment/stripe.php');

  if (defined('MODULE_PAYMENT_STRIPE_STATUS')) {
    $stripe = new stripe();

    $stripe_result = json_decode($stripe->sendTransactionToGateway('https://api.stripe.com/v1/charges/oscommerce_connection_test'), true);

    if ( is_array($stripe_result) && !empty($stripe_result) && isset($stripe_result['error']) ) {
      echo '<h1 id="sctresult">' . MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_SUCCESS_TITLE . '</h1>' .
           '<p>' . MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_SUCCESS_TEXT . '</p>';
    } else {
      echo '<h1 id="sctresult">' . MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_ERROR_TITLE . '</h1>' .
           '<p>' . MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_ERROR_TEXT . '</p>';
    }
  } else {
    echo '<h1 id="sctresult">' . MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_TITLE_ERROR . '</h1>' .
         '<p>' . MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_NOT_INSTALLED . '</p>';
  }
?>
