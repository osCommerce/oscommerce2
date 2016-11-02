<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  function OSCOM_PayPal_EC_Api_PayflowDoExpressCheckoutPayment($OSCOM_PayPal, $server, $extra_params) {
    if ( $server == 'live' ) {
      $api_url = 'https://payflowpro.paypal.com';
    } else {
      $api_url = 'https://pilot-payflowpro.paypal.com';
    }

    $params = array('USER' => $OSCOM_PayPal->hasCredentials('DP', 'payflow_user') ? $OSCOM_PayPal->getCredentials('DP', 'payflow_user') : $OSCOM_PayPal->getCredentials('DP', 'payflow_vendor'),
                    'VENDOR' => $OSCOM_PayPal->getCredentials('DP', 'payflow_vendor'),
                    'PARTNER' => $OSCOM_PayPal->getCredentials('DP', 'payflow_partner'),
                    'PWD' => $OSCOM_PayPal->getCredentials('DP', 'payflow_password'),
                    'TENDER' => 'P',
                    'TRXTYPE' => (OSCOM_APP_PAYPAL_DP_TRANSACTION_METHOD == '1') ? 'S' : 'A',
                    'ACTION' => 'D',
                    'BUTTONSOURCE' => $OSCOM_PayPal->getIdentifier());

    if ( is_array($extra_params) && !empty($extra_params) ) {
      $params = array_merge($params, $extra_params);
    }

    $post_string = '';

    foreach ($params as $key => $value) {
      $post_string .= $key . '[' . strlen(trim($value)) . ']=' . trim($value) . '&';
    }

    $post_string = substr($post_string, 0, -1);

    $response = $OSCOM_PayPal->makeApiCall($api_url, $post_string, $headers);
    parse_str($response, $response_array);

    if ( $response_array['RESULT'] != '0' ) {
      switch ( $response_array['RESULT'] ) {
        case '1':
        case '26':
          $error_message = $OSCOM_PayPal->getDef('module_ec_error_configuration');
          break;

        case '7':
          $error_message = $OSCOM_PayPal->getDef('module_ec_error_address');
          break;

        case '12':
          $error_message = $OSCOM_PayPal->getDef('module_ec_error_declined');
          break;

        case '1000':
          $error_message = $OSCOM_PayPal->getDef('module_ec_error_express_disabled');
          break;

        default:
          $error_message = $OSCOM_PayPal->getDef('module_ec_error_general');
          break;
      }

      $response_array['OSCOM_ERROR_MESSAGE'] = $error_message;
    }

    return array('res' => $response_array,
                 'success' => ($response_array['RESULT'] == '0'),
                 'req' => $params);
  }
?>
