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

  require('includes/languages/' . $language . '/modules/payment/authorizenet_cc_aim.php');
  require('includes/modules/payment/authorizenet_cc_aim.php');

  if (defined('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_STATUS')) {
    $authorizenet_cc_aim = new authorizenet_cc_aim();

    if ( MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_SERVER == 'Live' ) {
      $gateway_url = 'https://secure.authorize.net/gateway/transact.dll';
    } else {
      $gateway_url = 'https://test.authorize.net/gateway/transact.dll';
    }

    $params = array('x_login' => substr(MODULE_PAYMENT_AUTHORIZENET_CC_AIM_LOGIN_ID, 0, 20),
                    'x_tran_key' => substr(MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TRANSACTION_KEY, 0, 16),
                    'x_version' => $authorizenet_cc_aim->api_version,
                    'x_customer_ip' => tep_get_ip_address(),
                    'x_relay_response' => 'FALSE',
                    'x_delim_data' => 'TRUE',
                    'x_delim_char' => ',',
                    'x_encap_char' => '|');

    $post_string = '';

    foreach ($params as $key => $value) {
      $post_string .= $key . '=' . urlencode(trim($value)) . '&';
    }

    $post_string = substr($post_string, 0, -1);

    $result = $authorizenet_cc_aim->sendTransactionToGateway($gateway_url, $post_string);

    $response = array('x_response_code' => '-1');

    if ( !empty($result) ) {
      $raw = explode('|,|', substr($result, 1, -1));

      if ( count($raw) > 54 ) {
        $response['x_response_code'] = $raw[0];
      }
    }

    if ( $response['x_response_code'] != '-1' ) {
      echo '<h1 id="actresult">' . MODULE_PAYMENT_AUTHORIZENET_CC_AIM_DIALOG_CONNECTION_SUCCESS_TITLE . '</h1>' .
           '<p>' . MODULE_PAYMENT_AUTHORIZENET_CC_AIM_DIALOG_CONNECTION_SUCCESS_TEXT . '</p>';
    } else {
      echo '<h1 id="actresult">' . MODULE_PAYMENT_AUTHORIZENET_CC_AIM_DIALOG_CONNECTION_ERROR_TITLE . '</h1>' .
           '<p>' . MODULE_PAYMENT_AUTHORIZENET_CC_AIM_DIALOG_CONNECTION_ERROR_TEXT . '</p>';
    }
  } else {
    echo '<h1 id="actresult">' . MODULE_PAYMENT_AUTHORIZENET_CC_AIM_DIALOG_CONNECTION_TITLE_ERROR . '</h1>' .
         '<p>' . MODULE_PAYMENT_AUTHORIZENET_CC_AIM_DIALOG_CONNECTION_NOT_INSTALLED . '</p>';
  }
?>
