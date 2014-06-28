<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $content = 'credentials_manual.php';

  $data = array('OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL',
                'OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL_PRIMARY',
                'OSCOM_APP_PAYPAL_LIVE_API_USERNAME',
                'OSCOM_APP_PAYPAL_LIVE_API_PASSWORD',
                'OSCOM_APP_PAYPAL_LIVE_API_SIGNATURE',
                'OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL',
                'OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL_PRIMARY',
                'OSCOM_APP_PAYPAL_SANDBOX_API_USERNAME',
                'OSCOM_APP_PAYPAL_SANDBOX_API_PASSWORD',
                'OSCOM_APP_PAYPAL_SANDBOX_API_SIGNATURE');

  foreach ( $data as $key ) {
    if ( !defined($key) ) {
      $OSCOM_PayPal->saveParameter($key, '');
    }
  }
?>
