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
  require('../includes/languages/' . $language . '/modules/payment/paypal_pro_payflow_dp.php');
  require('../includes/modules/payment/paypal_pro_payflow_dp.php');

  if (defined(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_STATUS)) {
    $paypal_pro_payflow_dp = new paypal_pro_payflow_dp();

    if (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_SERVER == 'Live') {
      $api_url = 'https://payflowpro.paypal.com';
    } else {
      $api_url = 'https://pilot-payflowpro.paypal.com';
    }

    $params = array('USER' => (tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_USERNAME) ? MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_USERNAME : MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_VENDOR),
                    'VENDOR' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_VENDOR,
                    'PARTNER' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PARTNER,
                    'PWD' => MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PASSWORD,
                    'TENDER' => 'C',
                    'TRXTYPE' => ((MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_METHOD == 'Sale') ? 'S' : 'A'));

    $post_string = '';

    foreach ($params as $key => $value) {
      $post_string .= $key . '[' . strlen(trim($value)) . ']=' . trim($value) . '&';
    }

    $post_string = substr($post_string, 0, -1);

    $response = $paypal_pro_payflow_dp->sendTransactionToGateway($api_url, $post_string);

    $response_array = array();
    parse_str($response, $response_array);

    if ( isset($response_array['RESULT']) ) {
      echo '<h1 id="ppctresult">' . MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_DIALOG_CONNECTION_SUCCESS_TITLE . '</h1>';

      if (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_SERVER == 'Live') {
        echo '<p>' . MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_DIALOG_CONNECTION_SUCCESS_TEXT_LIVE . '</p>';
      } else {
        echo '<p>' . MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_DIALOG_CONNECTION_SUCCESS_TEXT_TEST . '</p>';
      }
    } else {
      echo '<h1 id="ppctresult">' . MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_DIALOG_CONNECTION_ERROR_TITLE . '</h1>';

      if (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_SERVER == 'Live') {
        echo '<p>' . MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_DIALOG_CONNECTION_ERROR_TEXT_LIVE . '</p>';
      } else {
        echo '<p>' . MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_DIALOG_CONNECTION_ERROR_TEXT_TEST . '</p>';
      }
    }
  } else {
    echo '<h1 id="ppctresult">' . MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_DIALOG_CONNECTION_TITLE_ERROR . '</h1>' .
         '<p>' . MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_DIALOG_CONNECTION_NOT_INSTALLED . '</p>';
  }
?>
