<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $content = 'credentials.php';

  $modules = array('PP', 'PF');
  $current_module = (isset($HTTP_GET_VARS['module']) && in_array($HTTP_GET_VARS['module'], $modules) ? $HTTP_GET_VARS['module'] : $modules[0]);

  $data = array('OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL',
                'OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL_PRIMARY',
                'OSCOM_APP_PAYPAL_LIVE_API_USERNAME',
                'OSCOM_APP_PAYPAL_LIVE_API_PASSWORD',
                'OSCOM_APP_PAYPAL_LIVE_API_SIGNATURE',
                'OSCOM_APP_PAYPAL_LIVE_MERCHANT_ID',
                'OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL',
                'OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL_PRIMARY',
                'OSCOM_APP_PAYPAL_SANDBOX_API_USERNAME',
                'OSCOM_APP_PAYPAL_SANDBOX_API_PASSWORD',
                'OSCOM_APP_PAYPAL_SANDBOX_API_SIGNATURE',
                'OSCOM_APP_PAYPAL_SANDBOX_MERCHANT_ID',
                'OSCOM_APP_PAYPAL_PF_LIVE_PARTNER',
                'OSCOM_APP_PAYPAL_PF_LIVE_VENDOR',
                'OSCOM_APP_PAYPAL_PF_LIVE_USER',
                'OSCOM_APP_PAYPAL_PF_LIVE_PASSWORD',
                'OSCOM_APP_PAYPAL_PF_SANDBOX_PARTNER',
                'OSCOM_APP_PAYPAL_PF_SANDBOX_VENDOR',
                'OSCOM_APP_PAYPAL_PF_SANDBOX_USER',
                'OSCOM_APP_PAYPAL_PF_SANDBOX_PASSWORD');

  foreach ( $data as $key ) {
    if ( !defined($key) ) {
      $OSCOM_PayPal->saveParameter($key, '');
    }
  }
?>
