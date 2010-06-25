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

  if (!defined('MODULE_PAYMENT_MONEYBOOKERS_STATUS') || (MODULE_PAYMENT_MONEYBOOKERS_STATUS  != 'True')) {
    exit;
  }

  $pass = false;

  if (isset($HTTP_POST_VARS['transaction_id']) && is_numeric($HTTP_POST_VARS['transaction_id']) && ($HTTP_POST_VARS['transaction_id'] > 0)) {
    if ($HTTP_POST_VARS['md5sig'] == strtoupper(md5(MODULE_PAYMENT_MONEYBOOKERS_MERCHANT_ID . $HTTP_POST_VARS['transaction_id'] . strtoupper(md5(MODULE_PAYMENT_MONEYBOOKERS_SECRET_WORD)) . $HTTP_POST_VARS['mb_amount'] . $HTTP_POST_VARS['mb_currency'] . $HTTP_POST_VARS['status']))) {
      $order_query = tep_db_query("select orders_status, currency, currency_value from " . TABLE_ORDERS . " where orders_id = '" . $HTTP_POST_VARS['transaction_id'] . "' and customers_id = '" . (int)$HTTP_POST_VARS['osc_custid'] . "'");
      if (tep_db_num_rows($order_query) > 0) {
        $pass = true;

        $order = tep_db_fetch_array($order_query);

        $status = $HTTP_POST_VARS['status'];
        switch ($HTTP_POST_VARS['status']) {
          case '2':
            $status = 'Processed';
            break;
          case '0':
            $status = 'Pending';
            break;
          case '-1':
            $status = 'Cancelled';
            break;
          case '-2':
            $status = 'Failed';
            break;
          case '-3':
            $status = 'Chargeback';
            break;
        }

        $comment_status = $status . ' (' . $currencies->format($HTTP_POST_VARS['amount'], false, $HTTP_POST_VARS['currency']) . ')';

        $sql_data_array = array('orders_id' => $HTTP_POST_VARS['transaction_id'],
                                'orders_status_id' => (MODULE_PAYMENT_MONEYBOOKERS_TRANSACTIONS_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_MONEYBOOKERS_TRANSACTIONS_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID),
                                'date_added' => 'now()',
                                'customer_notified' => '0',
                                'comments' => 'Moneybookers Verified [' . $comment_status . ']');

        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
      }
    }
  }

  if (($pass == false) && tep_not_null(MODULE_PAYMENT_MONEYBOOKERS_DEBUG_EMAIL)) {
    $email_body = 'IP Address: ' . tep_get_ip_address() . "\n\n" .
                  'MD5: ' . strtoupper(md5(MODULE_PAYMENT_MONEYBOOKERS_MERCHANT_ID . (isset($HTTP_POST_VARS['transaction_id']) ? $HTTP_POST_VARS['transaction_id'] : '') . strtoupper(md5(MODULE_PAYMENT_MONEYBOOKERS_SECRET_WORD)) . (isset($HTTP_POST_VARS['mb_amount']) ? $HTTP_POST_VARS['mb_amount'] : '') . (isset($HTTP_POST_VARS['mb_currency']) ? $HTTP_POST_VARS['mb_currency'] : '') . (isset($HTTP_POST_VARS['status']) ? $HTTP_POST_VARS['status'] : ''))) . "\n\n" .
                  '$HTTP_POST_VARS:' . "\n\n";

    reset($HTTP_POST_VARS);
    while (list($key, $value) = each($HTTP_POST_VARS)) {
      $email_body .= $key . '=' . $value . "\n";
    }

    $email_body .= "\n" . '$HTTP_GET_VARS:' . "\n\n";

    reset($HTTP_GET_VARS);
    while (list($key, $value) = each($HTTP_GET_VARS)) {
      $email_body .= $key . '=' . $value . "\n";
    }

    tep_mail('', MODULE_PAYMENT_MONEYBOOKERS_DEBUG_EMAIL, 'Moneybookers Invalid Process', $email_body, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
  }

  require('includes/application_bottom.php');
?>
