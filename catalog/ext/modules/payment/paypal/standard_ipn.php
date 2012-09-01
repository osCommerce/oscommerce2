<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');

  if (!defined('MODULE_PAYMENT_PAYPAL_STANDARD_STATUS') || (MODULE_PAYMENT_PAYPAL_STANDARD_STATUS  != 'True')) {
    exit;
  }

  if (!class_exists('httpClient')) {
    include('includes/classes/http_client.php');
  }

  $result = false;

  if ($HTTP_POST_VARS['receiver_email'] == MODULE_PAYMENT_PAYPAL_STANDARD_ID) {
    if (MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER == 'Live') {
      $server = 'www.paypal.com';
    } else {
      $server = 'www.sandbox.paypal.com';
    }

    $parameters = 'cmd=_notify-validate';

    foreach ($HTTP_POST_VARS as $key => $value) {
      $parameters .= '&' . $key . '=' . urlencode(stripslashes($value));
    }

    $http = new httpClient($server, 443);

    if ($http->post('/cgi-bin/webscr', $parameters) == 200) {
      $result = $http->getBody();
    }
  }

  if ($result == 'VERIFIED') {
    if (isset($HTTP_POST_VARS['invoice']) && is_numeric($HTTP_POST_VARS['invoice']) && ($HTTP_POST_VARS['invoice'] > 0)) {
      $order_query = tep_db_query("select orders_status, currency, currency_value from " . TABLE_ORDERS . " where orders_id = '" . $HTTP_POST_VARS['invoice'] . "' and customers_id = '" . (int)$HTTP_POST_VARS['custom'] . "'");
      if (tep_db_num_rows($order_query) > 0) {
        $order = tep_db_fetch_array($order_query);

        $new_order_status = DEFAULT_ORDERS_STATUS_ID;

        if ($order['orders_status'] == MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID) {
          $sql_data_array = array('orders_id' => $HTTP_POST_VARS['invoice'],
                                  'orders_status_id' => MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID,
                                  'date_added' => 'now()',
                                  'customer_notified' => '0',
                                  'comments' => 'PayPal IPN Verified');

          tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        } else {
          $new_order_status = $order['orders_status'];
        }

        $total_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . $HTTP_POST_VARS['invoice'] . "' and class = 'ot_total' limit 1");
        $total = tep_db_fetch_array($total_query);

        $comment_status = $HTTP_POST_VARS['payment_status'] . ' (' . ucfirst($HTTP_POST_VARS['payer_status']) . '; ' . $currencies->format($HTTP_POST_VARS['mc_gross'], false, $HTTP_POST_VARS['mc_currency']) . ')';

        if ($HTTP_POST_VARS['payment_status'] == 'Pending') {
          $comment_status .= '; ' . $HTTP_POST_VARS['pending_reason'];
        } elseif ( ($HTTP_POST_VARS['payment_status'] == 'Reversed') || ($HTTP_POST_VARS['payment_status'] == 'Refunded') ) {
          $comment_status .= '; ' . $HTTP_POST_VARS['reason_code'];
        }

        if ($HTTP_POST_VARS['mc_gross'] != number_format($total['value'] * $order['currency_value'], $currencies->get_decimal_places($order['currency']))) {
          $comment_status .= '; PayPal transaction value (' . tep_output_string_protected($HTTP_POST_VARS['mc_gross']) . ') does not match order value (' . number_format($total['value'] * $order['currency_value'], $currencies->get_decimal_places($order['currency'])) . ')';
        } elseif ($HTTP_POST_VARS['payment_status'] == 'Completed') {
          $new_order_status = (MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID > 0 ? MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID : $new_order_status);
        }

        tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . (int)$new_order_status . "', last_modified = now() where orders_id = '" . (int)$HTTP_POST_VARS['invoice'] . "'");

        $sql_data_array = array('orders_id' => $HTTP_POST_VARS['invoice'],
                                'orders_status_id' => (int)$new_order_status,
                                'date_added' => 'now()',
                                'customer_notified' => '0',
                                'comments' => 'PayPal IPN Verified [' . $comment_status . ']');

        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
      }
    }
  } else {
    if (tep_not_null(MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL)) {
      $email_body = '$HTTP_POST_VARS:' . "\n\n";

      reset($HTTP_POST_VARS);
      while (list($key, $value) = each($HTTP_POST_VARS)) {
        $email_body .= $key . '=' . $value . "\n";
      }

      $email_body .= "\n" . '$HTTP_GET_VARS:' . "\n\n";

      reset($HTTP_GET_VARS);
      while (list($key, $value) = each($HTTP_GET_VARS)) {
        $email_body .= $key . '=' . $value . "\n";
      }

      tep_mail('', MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL, 'PayPal IPN Invalid Process', $email_body, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
    }
  }

  require('includes/application_bottom.php');
?>
