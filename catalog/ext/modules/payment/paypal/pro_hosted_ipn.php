<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2017 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');

  if ( !defined('OSCOM_APP_PAYPAL_HS_STATUS') || !in_array(OSCOM_APP_PAYPAL_HS_STATUS, array('1', '0')) ) {
    exit;
  }

  require('includes/modules/payment/paypal_pro_hs.php');

  $result = false;

  if ( isset($HTTP_POST_VARS['txn_id']) && !empty($HTTP_POST_VARS['txn_id']) ) {
    $paypal_pro_hs = new paypal_pro_hs();

    $result = $paypal_pro_hs->_app->getApiResult('APP', 'GetTransactionDetails', array('TRANSACTIONID' => $HTTP_POST_VARS['txn_id']), (OSCOM_APP_PAYPAL_HS_STATUS == '1') ? 'live' : 'sandbox', true);
  }

  if ( is_array($result) && isset($result['ACK']) && (($result['ACK'] == 'Success') || ($result['ACK'] == 'SuccessWithWarning')) ) {
    $pphs_result = $result;

    $paypal_pro_hs->verifyTransaction(true);
  }

  require('includes/application_bottom.php');
?>
