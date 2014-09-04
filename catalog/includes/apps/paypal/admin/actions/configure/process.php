<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  if ( $current_module == 'G' ) {
    $cut = 'OSCOM_APP_PAYPAL_';
  } else {
    $cut = 'OSCOM_APP_PAYPAL_' . $current_module . '_';
  }

  $cut_length = strlen($cut);

  foreach ( $OSCOM_PayPal->getParameters($current_module) as $key ) {
    $p = strtolower(substr($key, $cut_length));

    if ( isset($HTTP_POST_VARS[$p]) ) {
      $OSCOM_PayPal->saveParameter($key, $HTTP_POST_VARS[$p]);
    }
  }

  $OSCOM_PayPal->addAlert('Configuration parameters have been successfully saved.', 'success');

  tep_redirect(tep_href_link('paypal.php', 'action=configure&module=' . $current_module));
?>
