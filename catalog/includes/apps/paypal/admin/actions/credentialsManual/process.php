<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $data = array();

  if ( $current_module == 'PP' ) {
    $data = array('OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL' => isset($HTTP_POST_VARS['live_email']) ? tep_db_prepare_input($HTTP_POST_VARS['live_email']) : '',
                  'OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL_PRIMARY' => isset($HTTP_POST_VARS['live_email_primary']) ? tep_db_prepare_input($HTTP_POST_VARS['live_email_primary']) : '',
                  'OSCOM_APP_PAYPAL_LIVE_API_USERNAME' => isset($HTTP_POST_VARS['live_username']) ? tep_db_prepare_input($HTTP_POST_VARS['live_username']) : '',
                  'OSCOM_APP_PAYPAL_LIVE_API_PASSWORD' => isset($HTTP_POST_VARS['live_password']) ? tep_db_prepare_input($HTTP_POST_VARS['live_password']) : '',
                  'OSCOM_APP_PAYPAL_LIVE_API_SIGNATURE' => isset($HTTP_POST_VARS['live_signature']) ? tep_db_prepare_input($HTTP_POST_VARS['live_signature']) : '',
                  'OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL' => isset($HTTP_POST_VARS['sandbox_email']) ? tep_db_prepare_input($HTTP_POST_VARS['sandbox_email']) : '',
                  'OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL_PRIMARY' => isset($HTTP_POST_VARS['sandbox_email_primary']) ? tep_db_prepare_input($HTTP_POST_VARS['sandbox_email_primary']) : '',
                  'OSCOM_APP_PAYPAL_SANDBOX_API_USERNAME' => isset($HTTP_POST_VARS['sandbox_username']) ? tep_db_prepare_input($HTTP_POST_VARS['sandbox_username']) : '',
                  'OSCOM_APP_PAYPAL_SANDBOX_API_PASSWORD' => isset($HTTP_POST_VARS['sandbox_password']) ? tep_db_prepare_input($HTTP_POST_VARS['sandbox_password']) : '',
                  'OSCOM_APP_PAYPAL_SANDBOX_API_SIGNATURE' => isset($HTTP_POST_VARS['sandbox_signature']) ? tep_db_prepare_input($HTTP_POST_VARS['sandbox_signature']) : '');
  } elseif ( $current_module == 'PF' ) {
    $data = array('OSCOM_APP_PAYPAL_PF_LIVE_PARTNER' => isset($HTTP_POST_VARS['live_partner']) ? tep_db_prepare_input($HTTP_POST_VARS['live_partner']) : '',
                  'OSCOM_APP_PAYPAL_PF_LIVE_VENDOR' => isset($HTTP_POST_VARS['live_vendor']) ? tep_db_prepare_input($HTTP_POST_VARS['live_vendor']) : '',
                  'OSCOM_APP_PAYPAL_PF_LIVE_USER' => isset($HTTP_POST_VARS['live_user']) ? tep_db_prepare_input($HTTP_POST_VARS['live_user']) : '',
                  'OSCOM_APP_PAYPAL_PF_LIVE_PASSWORD' => isset($HTTP_POST_VARS['live_password']) ? tep_db_prepare_input($HTTP_POST_VARS['live_password']) : '',
                  'OSCOM_APP_PAYPAL_PF_SANDBOX_PARTNER' => isset($HTTP_POST_VARS['sandbox_partner']) ? tep_db_prepare_input($HTTP_POST_VARS['sandbox_partner']) : '',
                  'OSCOM_APP_PAYPAL_PF_SANDBOX_VENDOR' => isset($HTTP_POST_VARS['sandbox_vendor']) ? tep_db_prepare_input($HTTP_POST_VARS['sandbox_vendor']) : '',
                  'OSCOM_APP_PAYPAL_PF_SANDBOX_USER' => isset($HTTP_POST_VARS['sandbox_user']) ? tep_db_prepare_input($HTTP_POST_VARS['sandbox_user']) : '',
                  'OSCOM_APP_PAYPAL_PF_SANDBOX_PASSWORD' => isset($HTTP_POST_VARS['sandbox_password']) ? tep_db_prepare_input($HTTP_POST_VARS['sandbox_password']) : '');
  }

  foreach ( $data as $key => $value ) {
    $OSCOM_PayPal->saveParameter($key, $value);
  }

  $OSCOM_PayPal->addAlert('Account credentials have been successfully saved.', 'success');

  tep_redirect(tep_href_link('paypal.php', 'action=credentialsManual&module=' . $current_module));
?>
