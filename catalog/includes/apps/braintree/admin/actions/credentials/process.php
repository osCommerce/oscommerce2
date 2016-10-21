<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  $data = array('OSCOM_APP_PAYPAL_BRAINTREE_MERCHANT_ID' => isset($HTTP_POST_VARS['live_merchant_id']) ? tep_db_prepare_input($HTTP_POST_VARS['live_merchant_id']) : '',
                'OSCOM_APP_PAYPAL_BRAINTREE_PRIVATE_KEY' => isset($HTTP_POST_VARS['live_private_key']) ? tep_db_prepare_input($HTTP_POST_VARS['live_private_key']) : '',
                'OSCOM_APP_PAYPAL_BRAINTREE_PUBLIC_KEY' => isset($HTTP_POST_VARS['live_public_key']) ? tep_db_prepare_input($HTTP_POST_VARS['live_public_key']) : '',
                'OSCOM_APP_PAYPAL_BRAINTREE_CURRENCIES_MA' => isset($HTTP_POST_VARS['live_currencies_ma']) ? tep_db_prepare_input($HTTP_POST_VARS['live_currencies_ma']) : '',
                'OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_MERCHANT_ID' => isset($HTTP_POST_VARS['sandbox_merchant_id']) ? tep_db_prepare_input($HTTP_POST_VARS['sandbox_merchant_id']) : '',
                'OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_PRIVATE_KEY' => isset($HTTP_POST_VARS['sandbox_private_key']) ? tep_db_prepare_input($HTTP_POST_VARS['sandbox_private_key']) : '',
                'OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_PUBLIC_KEY' => isset($HTTP_POST_VARS['sandbox_public_key']) ? tep_db_prepare_input($HTTP_POST_VARS['sandbox_public_key']) : '',
                'OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_CURRENCIES_MA' => isset($HTTP_POST_VARS['sandbox_currencies_ma']) ? tep_db_prepare_input($HTTP_POST_VARS['sandbox_currencies_ma']) : '');

  foreach ( $data as $key => $value ) {
    $OSCOM_Braintree->saveParameter($key, $value);
  }

  $OSCOM_Braintree->addAlert($OSCOM_Braintree->getDef('alert_credentials_saved_success'), 'success');

  tep_redirect(tep_href_link('braintree.php', 'action=credentials'));
?>
