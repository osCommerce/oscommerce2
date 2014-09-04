<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  if ( isset($HTTP_GET_VARS['type']) && in_array($HTTP_GET_VARS['type'], array('live', 'sandbox')) ) {
    $params = array('return_url' => tep_href_link('paypal.php', 'action=start&subaction=retrieve', 'SSL'),
                    'type' => $HTTP_GET_VARS['type']);

    $result_string = $OSCOM_PayPal->makeApiCall('https://ssl.oscommerce.com/index.php?RPC&Website&Index&PayPalStart', $params);
    $result = array();

    if ( !empty($result_string) && (substr($result_string, 0, 9) == 'rpcStatus') ) {
      $raw = explode("\n", $result_string);

      foreach ( $raw as $r ) {
        $key = explode('=', $r, 2);

        if ( is_array($key) && (count($key) === 2) && !empty($key[0]) && !empty($key[1]) ) {
          $result[$key[0]] = $key[1];
        }
      }

      if ( isset($result['rpcStatus']) && ($result['rpcStatus'] === '1') && isset($result['merchant_id']) && (preg_match('/^[A-Za-z0-9]{32}$/', $result['merchant_id']) === 1) && isset($result['redirect_url']) && isset($result['secret']) ) {
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_START_MERCHANT_ID', $result['merchant_id']);
        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_START_SECRET', $result['secret']);

        tep_redirect($result['redirect_url']);
      } else {
        $OSCOM_PayPal->addAlert('Could not initiate the start account procedure. Please try again in a short while.', 'error');
      }
    } else {
      $OSCOM_PayPal->addAlert('Could not connect to the osCommerce website. Please try again in a short while.', 'error');
    }
  } else {
    $OSCOM_PayPal->addAlert('Please select to start with a Live or Sandbox account.', 'error');
  }
?>
