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
  require('../includes/languages/' . $language . '/modules/payment/sage_pay_direct.php');
  require('../includes/modules/payment/sage_pay_direct.php');

  if ( defined(MODULE_PAYMENT_SAGE_PAY_DIRECT_STATUS) ) {
    $sage_pay_direct = new sage_pay_direct();

    switch (MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER) {
      case 'Live':
        $gateway_url = 'https://live.sagepay.com/gateway/service/vspdirect-register.vsp';
        break;

      case 'Test':
      default:
        $gateway_url = 'https://test.sagepay.com/gateway/service/vspdirect-register.vsp';
        break;
    }

    $params = array('VPSProtocol' => '3.00',
                    'ReferrerID' => 'C74D7B82-E9EB-4FBD-93DB-76F0F551C802',
                    'Vendor' => substr(MODULE_PAYMENT_SAGE_PAY_DIRECT_VENDOR_LOGIN_NAME, 0, 15),
                    'Amount' => 0,
                    'Currency' => DEFAULT_CURRENCY);

    $ip_address = tep_get_ip_address();

    if ( !empty($ip_address) && (ip2long($ip_address) != -1) && (ip2long($ip_address) != false) ) {
      $params['ClientIPAddress']= $ip_address;
    }

    $post_string = '';

    foreach ($params as $key => $value) {
      $post_string .= $key . '=' . urlencode(trim($value)) . '&';
    }

    $response = $sage_pay_direct->sendTransactionToGateway($gateway_url, $post_string);

    if ( $response != false ) {
      echo '<h1 id="spctresult">' . MODULE_PAYMENT_SAGE_PAY_DIRECT_DIALOG_CONNECTION_SUCCESS_TITLE . '</h1>';

      if (MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER == 'Live') {
        echo '<p>' . MODULE_PAYMENT_SAGE_PAY_DIRECT_DIALOG_CONNECTION_SUCCESS_TEXT_LIVE . '</p>';
      } else {
        echo '<p>' . MODULE_PAYMENT_SAGE_PAY_DIRECT_DIALOG_CONNECTION_SUCCESS_TEXT_TEST . '</p>';
      }
    } else {
      echo '<h1 id="spctresult">' . MODULE_PAYMENT_SAGE_PAY_DIRECT_DIALOG_CONNECTION_ERROR_TITLE . '</h1>';

      if (MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER == 'Live') {
        echo '<p>' . MODULE_PAYMENT_SAGE_PAY_DIRECT_DIALOG_CONNECTION_ERROR_TEXT_LIVE . '</p>';
      } else {
        echo '<p>' . MODULE_PAYMENT_SAGE_PAY_DIRECT_DIALOG_CONNECTION_ERROR_TEXT_TEST . '</p>';
      }
    }
  } else {
    echo '<h1 id="spctresult">' . MODULE_PAYMENT_SAGE_PAY_DIRECT_DIALOG_CONNECTION_TITLE_ERROR . '</h1>' .
         '<p>' . MODULE_PAYMENT_SAGE_PAY_DIRECT_DIALOG_CONNECTION_NOT_INSTALLED . '</p>';
  }
?>
