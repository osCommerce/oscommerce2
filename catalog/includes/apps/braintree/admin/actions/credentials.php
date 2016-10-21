<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  $content = 'credentials.php';

  $data = array('OSCOM_APP_PAYPAL_BRAINTREE_MERCHANT_ID',
                'OSCOM_APP_PAYPAL_BRAINTREE_PRIVATE_KEY',
                'OSCOM_APP_PAYPAL_BRAINTREE_PUBLIC_KEY',
                'OSCOM_APP_PAYPAL_BRAINTREE_CURRENCIES_MA',
                'OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_MERCHANT_ID',
                'OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_PRIVATE_KEY',
                'OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_PUBLIC_KEY',
                'OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_CURRENCIES_MA');

  foreach ( $data as $key ) {
    if ( !defined($key) ) {
      $OSCOM_Braintree->saveParameter($key, '');
    }
  }
?>
