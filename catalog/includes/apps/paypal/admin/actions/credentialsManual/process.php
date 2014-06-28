<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $data = array('OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL' => isset($HTTP_POST_VARS['live_email']) ? tep_db_prepare_input($HTTP_POST_VARS['live_email']) : '',
                'OSCOM_APP_PAYPAL_LIVE_API_USERNAME' => isset($HTTP_POST_VARS['live_username']) ? tep_db_prepare_input($HTTP_POST_VARS['live_username']) : '',
                'OSCOM_APP_PAYPAL_LIVE_API_PASSWORD' => isset($HTTP_POST_VARS['live_password']) ? tep_db_prepare_input($HTTP_POST_VARS['live_password']) : '',
                'OSCOM_APP_PAYPAL_LIVE_API_SIGNATURE' => isset($HTTP_POST_VARS['live_signature']) ? tep_db_prepare_input($HTTP_POST_VARS['live_signature']) : '',
                'OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL' => isset($HTTP_POST_VARS['sandbox_email']) ? tep_db_prepare_input($HTTP_POST_VARS['sandbox_email']) : '',
                'OSCOM_APP_PAYPAL_SANDBOX_API_USERNAME' => isset($HTTP_POST_VARS['sandbox_username']) ? tep_db_prepare_input($HTTP_POST_VARS['sandbox_username']) : '',
                'OSCOM_APP_PAYPAL_SANDBOX_API_PASSWORD' => isset($HTTP_POST_VARS['sandbox_password']) ? tep_db_prepare_input($HTTP_POST_VARS['sandbox_password']) : '',
                'OSCOM_APP_PAYPAL_SANDBOX_API_SIGNATURE' => isset($HTTP_POST_VARS['sandbox_signature']) ? tep_db_prepare_input($HTTP_POST_VARS['sandbox_signature']) : '');

  foreach ( $data as $key => $value ) {
    $OSCOM_PayPal->saveParameter($key, $value);
  }

  $messageStack->add_session('API Credentials have been successfully saved.', 'success');

  tep_redirect(tep_href_link('paypal.php'));
?>
