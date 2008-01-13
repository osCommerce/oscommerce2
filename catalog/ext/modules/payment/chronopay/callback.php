<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2008 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');

  $ip_address = tep_get_ip_address();

  if ( ($ip_address == '69.20.58.35') || ($ip_address == '207.97.201.192') ) {
    if (isset($HTTP_POST_VARS['cs1']) && is_numeric($HTTP_POST_VARS['cs1']) && isset($HTTP_POST_VARS['cs2']) && is_numeric($HTTP_POST_VARS['cs2']) && isset($HTTP_POST_VARS['cs3']) && !empty($HTTP_POST_VARS['cs3']) && isset($HTTP_POST_VARS['product_id']) && ($HTTP_POST_VARS['product_id'] == MODULE_PAYMENT_CHRONOPAY_PRODUCT_ID) && isset($HTTP_POST_VARS['total']) && !empty($HTTP_POST_VARS['total']) && isset($HTTP_POST_VARS['transaction_type']) && !empty($HTTP_POST_VARS['transaction_type'])) {
      if ($HTTP_POST_VARS['cs3'] == md5(MODULE_PAYMENT_CHRONOPAY_PRODUCT_ID . $HTTP_POST_VARS['cs2'] . $HTTP_POST_VARS['cs1'] . $HTTP_POST_VARS['total'] . MODULE_PAYMENT_CHRONOPAY_MD5_HASH)) {
        $order_query = tep_db_query("select order_status, currency, currency_value from " . TABLE_ORDERS . " where orders_id = '" . (int)$HTTP_POST_VARS['cs2'] . "' and customers_id = '" . (int)$HTTP_POST_VARS['cs1'] . "'");

        if (tep_db_num_rows($order_query) > 0) {
          $order = tep_db_fetch_array($order_query);

          if ($order['order_status'] == MODULE_PAYMENT_CHRONOPAY_PREPARE_ORDER_STATUS_ID) {
            $total_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$HTTP_POST_VARS['cs2'] . "' and class = 'ot_total' limit 1");
            $total = tep_db_fetch_array($total_query);

            $comment_status = $HTTP_POST_VARS['transaction_type'] . ' (' . $HTTP_POST_VARS['transaction_id'] . '; ' . $currencies->format($HTTP_POST_VARS['total'], false, $HTTP_POST_VARS['currency']) . ')';

            $order_status_id = (MODULE_PAYMENT_CHRONOPAY_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_CHRONOPAY_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID);

            tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . $order_status_id . "', last_modified = now() where orders_id = '" . (int)$HTTP_POST_VARS['cs2'] . "'");

            $sql_data_array = array('orders_id' => $HTTP_POST_VARS['cs2'],
                                    'orders_status_id' => $order_status_id,
                                    'date_added' => 'now()',
                                    'customer_notified' => '0',
                                    'comments' => 'ChronoPay Verified [' . $comment_status . ']');

            tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
          }
        }
      }
    }
  }

  require('includes/application_bottom.php');
?>
