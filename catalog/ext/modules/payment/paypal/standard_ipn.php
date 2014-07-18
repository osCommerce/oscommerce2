<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');

  if ( !defined('OSCOM_APP_PAYPAL_PS_STATUS') || !in_array(OSCOM_APP_PAYPAL_PS_STATUS, array('1', '0')) ) {
    exit;
  }

  require(DIR_WS_LANGUAGES . $language . '/modules/payment/paypal_standard.php');
  require('includes/modules/payment/paypal_standard.php');

  $paypal_standard = new paypal_standard();

  $result = false;

  $seller_accounts = array($paypal_standard->_app->getCredentials('PS', 'email'));

  if ( tep_not_null($paypal_standard->_app->getCredentials('PS', 'email_primary')) ) {
    $seller_accounts[] = $paypal_standard->_app->getCredentials('PS', 'email_primary');
  }

  if ( isset($HTTP_POST_VARS['receiver_email']) && in_array($HTTP_POST_VARS['receiver_email'], $seller_accounts) ) {
    $parameters = 'cmd=_notify-validate&';

    foreach ( $HTTP_POST_VARS as $key => $value ) {
      if ( $key != 'cmd' ) {
        $parameters .= $key . '=' . urlencode(stripslashes($value)) . '&';
      }
    }

    $parameters = substr($parameters, 0, -1);

    $result = $paypal_standard->_app->makeApiCall($paypal_standard->form_action_url, $parameters);
  }

  $log_params = $HTTP_POST_VARS;
  $log_params['cmd'] = '_notify-validate';

  foreach ( $HTTP_GET_VARS as $key => $value ) {
    $log_params['GET ' . $key] = $value;
  }

  $paypal_standard->_app->log('PS', '_notify-validate', ($result == 'VERIFIED') ? 1 : -1, $log_params, $result, (OSCOM_APP_PAYPAL_PS_STATUS == '1') ? 'live' : 'sandbox', true);

  if ( $result == 'VERIFIED' ) {
    $paypal_standard->verifyTransaction(true);
  }

  tep_session_destroy();

  require('includes/application_bottom.php');
?>
