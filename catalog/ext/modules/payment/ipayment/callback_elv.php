<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $language . '/modules/payment/ipayment_elv.php');
  require('includes/modules/payment/ipayment_elv.php');
  $ipayment_elv = new ipayment_elv();

  if (!$ipayment_elv->check() || !$ipayment_elv->enabled) {
    exit;
  }

  if (in_array(tep_get_ip_address(), $ipayment_elv->gateway_addresses)) {
    $checksum_pass = 0; // unknown

    if (tep_not_null(MODULE_PAYMENT_IPAYMENT_ELV_SECRET_HASH_PASSWORD)) {
// verify ret_param_checksum
      if ($HTTP_POST_VARS['ret_param_checksum'] == md5(MODULE_PAYMENT_IPAYMENT_ELV_USER_ID . $HTTP_POST_VARS['trx_amount'] . $HTTP_POST_VARS['trx_currency'] . $HTTP_POST_VARS['ret_authcode'] . $HTTP_POST_VARS['ret_booknr'] . MODULE_PAYMENT_IPAYMENT_ELV_SECRET_HASH_PASSWORD)) {
        $checksum_pass = 1; // true
      } else {
        $checksum_pass = -1; // false
      }
    }

    $ipayment_elv->sendDebugEmail($checksum_pass);
  }
?>
