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

  if (!defined('MODULE_PAYMENT_PAYPAL_STANDARD_STATUS') || (MODULE_PAYMENT_PAYPAL_STANDARD_STATUS  != 'True')) {
    exit;
  }

  $parameters = 'cmd=_notify-validate';

  reset($HTTP_POST_VARS);
  while (list($key, $value) = each($HTTP_POST_VARS)) {
    $parameters .= '&' . $key . '=' . urlencode(stripslashes($value));
  }

  if (MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER == 'Live') {
    $server = 'www.paypal.com';
  } else {
    $server = 'www.sandbox.paypal.com';
  }

  $fsocket = false;
  $curl = false;
  $result = false;

  if ( (PHP_VERSION >= 4.3) && ($fp = @fsockopen('ssl://' . $server, 443, $errno, $errstr, 30)) ) {
    $fsocket = true;
  } elseif (function_exists('curl_exec')) {
    $curl = true;
  } elseif ($fp = @fsockopen($server, 80, $errno, $errstr, 30)) {
    $fsocket = true;
  }

  if ($fsocket == true) {
    $header = 'POST /cgi-bin/webscr HTTP/1.0' . "\r\n" .
              'Host: ' . $server . "\r\n" .
              'Content-Type: application/x-www-form-urlencoded' . "\r\n" .
              'Content-Length: ' . strlen($parameters) . "\r\n" .
              'Connection: close' . "\r\n\r\n";

    @fputs($fp, $header . $parameters);

    $string = '';
    while (!@feof($fp)) {
      $res = @fgets($fp, 1024);
      $string .= $res;

      if ( ($res == 'VERIFIED') || ($res == 'INVALID') ) {
        $result = $res;

        break;
      }
    }

    @fclose($fp);
  } elseif ($curl == true) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://' . $server . '/cgi-bin/webscr');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $result = curl_exec($ch);

    curl_close($ch);
  }

  if ($result == 'VERIFIED') {
    if (isset($HTTP_POST_VARS['invoice']) && is_numeric($HTTP_POST_VARS['invoice']) && ($HTTP_POST_VARS['invoice'] > 0)) {
      $order_query = tep_db_query("select orders_status, currency, currency_value from " . TABLE_ORDERS . " where orders_id = '" . $HTTP_POST_VARS['invoice'] . "' and customers_id = '" . (int)$HTTP_POST_VARS['custom'] . "'");
      if (tep_db_num_rows($order_query) > 0) {
        $order = tep_db_fetch_array($order_query);

        if ($order['orders_status'] == MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID) {
          $sql_data_array = array('orders_id' => $HTTP_POST_VARS['invoice'],
                                  'orders_status_id' => MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID,
                                  'date_added' => 'now()',
                                  'customer_notified' => '0',
                                  'comments' => '');

          tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);


          tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . (MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID) . "', last_modified = now() where orders_id = '" . (int)$HTTP_POST_VARS['invoice'] . "'");
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
        }

        $sql_data_array = array('orders_id' => $HTTP_POST_VARS['invoice'],
                                'orders_status_id' => (MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID),
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
