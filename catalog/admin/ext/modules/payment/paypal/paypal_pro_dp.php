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
  require('../includes/languages/' . $language . '/modules/payment/paypal_pro_dp.php');
  require('../includes/modules/payment/paypal_pro_dp.php');

  if (defined(MODULE_PAYMENT_PAYPAL_PRO_DP_STATUS)) {
    $paypal_pro_dp = new paypal_pro_dp();

    if (MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER == 'Live') {
      $api_url = 'https://api-3t.paypal.com/nvp';
    } else {
      $api_url = 'https://api-3t.sandbox.paypal.com/nvp';
    }

    $params = array('USER' => MODULE_PAYMENT_PAYPAL_PRO_DP_API_USERNAME,
                    'PWD' => MODULE_PAYMENT_PAYPAL_PRO_DP_API_PASSWORD,
                    'VERSION' => $paypal_pro_dp->api_version,
                    'SIGNATURE' => MODULE_PAYMENT_PAYPAL_PRO_DP_API_SIGNATURE,
                    'METHOD' => 'DoDirectPayment',
                    'PAYMENTACTION' => ((MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_METHOD == 'Sale') ? 'Sale' : 'Authorization'),
                    'IPADDRESS' => tep_get_ip_address());

    $post_string = '';

    foreach ($params as $key => $value) {
      $post_string .= $key . '=' . urlencode(utf8_encode(trim($value))) . '&';
    }

    $post_string = substr($post_string, 0, -1);

    $response = $paypal_pro_dp->sendTransactionToGateway($api_url, $post_string);
    $response_array = array();
    parse_str($response, $response_array);

    if ( isset($response_array['ACK']) && ($response_array['ACK'] == 'Failure') ) {
      echo '<h1 id="ppctresult">' . MODULE_PAYMENT_PAYPAL_PRO_DP_DIALOG_CONNECTION_SUCCESS_TITLE . '</h1>';

      if (MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER == 'Live') {
        echo '<p>' . MODULE_PAYMENT_PAYPAL_PRO_DP_DIALOG_CONNECTION_SUCCESS_TEXT_LIVE . '</p>';
      } else {
        echo '<p>' . MODULE_PAYMENT_PAYPAL_PRO_DP_DIALOG_CONNECTION_SUCCESS_TEXT_TEST . '</p>';
      }
    } else {
      echo '<h1 id="ppctresult">' . MODULE_PAYMENT_PAYPAL_PRO_DP_DIALOG_CONNECTION_ERROR_TITLE . '</h1>';

      if (MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER == 'Live') {
        echo '<p>' . MODULE_PAYMENT_PAYPAL_PRO_DP_DIALOG_CONNECTION_ERROR_TEXT_LIVE . '</p>';
      } else {
        echo '<p>' . MODULE_PAYMENT_PAYPAL_PRO_DP_DIALOG_CONNECTION_ERROR_TEXT_TEST . '</p>';
      }
    }
  } else {
    echo '<h1 id="ppctresult">' . MODULE_PAYMENT_PAYPAL_PRO_DP_DIALOG_CONNECTION_TITLE_ERROR . '</h1>' .
         '<p>' . MODULE_PAYMENT_PAYPAL_PRO_DP_DIALOG_CONNECTION_NOT_INSTALLED . '</p>';
  }
?>
