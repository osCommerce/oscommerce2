<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/
?>

<h3>PayPal Balance</h3>

<?php
  $result = $OSCOM_PayPal->getApiResult('APP', 'GetBalance', null, 'sandbox');

  $counter = 0;

  while ( true ) {
    if ( isset($result['L_AMT' . $counter]) && isset($result['L_CURRENCYCODE' . $counter]) ) {
      echo '<p><strong>' . $result['L_CURRENCYCODE' . $counter] . ':</strong> ' . $currencies->format($result['L_AMT' . $counter], false, $result['L_CURRENCYCODE' . $counter]) . '</p>';

      $counter++;
    } else {
      break;
    }
  }
?>
