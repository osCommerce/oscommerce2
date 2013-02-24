<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

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

  if ( ($_POST['receiver_email'] == MODULE_PAYMENT_PAYPAL_STANDARD_ID) || (defined('MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID') && tep_not_null(MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID) && ($_POST['receiver_email'] == MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID)) ) {
    if (MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER == 'Live') {
      $server = 'www.paypal.com';
    } else {
      $server = 'www.sandbox.paypal.com';
    }

    $parameters = 'cmd=_notify-validate';

    foreach ($_POST as $key => $value) {
      $parameters .= '&' . $key . '=' . urlencode($value);
    }

    $http = new httpClient($server, 443);

    if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_PROXY') && tep_not_null(MODULE_PAYMENT_PAYPAL_STANDARD_PROXY)) {
      $proxy_server = MODULE_PAYMENT_PAYPAL_STANDARD_PROXY;
      $proxy_port = null;

      if (strpos(MODULE_PAYMENT_PAYPAL_STANDARD_PROXY, ':') !== false) {
        list($proxy_server, $proxy_port) = explode(':', MODULE_PAYMENT_PAYPAL_STANDARD_PROXY, 2);
      }

      $http->setProxy($proxy_server, $proxy_port);
    }

    if (file_exists(DIR_FS_CACHE . $server . '.crt')) {
      $http->addParameter('cafile', DIR_FS_CACHE . $server . '.crt');
    }

    if ($http->post('/cgi-bin/webscr', $parameters) == 200) {
      $result = $http->getBody();
    }
  }

  if ($result == 'VERIFIED') {
    if (isset($_POST['invoice']) && is_numeric($_POST['invoice']) && ($_POST['invoice'] > 0)) {
      $order_query = tep_db_query("select orders_status, currency, currency_value from " . TABLE_ORDERS . " where orders_id = '" . $_POST['invoice'] . "' and customers_id = '" . (int)$_POST['custom'] . "'");
      if (tep_db_num_rows($order_query) > 0) {
        $order = tep_db_fetch_array($order_query);

        $new_order_status = DEFAULT_ORDERS_STATUS_ID;

        if ($order['orders_status'] == MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID) {
          $sql_data_array = array('orders_id' => $_POST['invoice'],
                                  'orders_status_id' => MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID,
                                  'date_added' => 'now()',
                                  'customer_notified' => '0',
                                  'comments' => 'PayPal IPN Verified');

          tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        } else {
          $new_order_status = $order['orders_status'];
        }

        $total_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . $_POST['invoice'] . "' and class = 'ot_total' limit 1");
        $total = tep_db_fetch_array($total_query);

        $comment_status = $_POST['payment_status'] . ' (' . ucfirst($_POST['payer_status']) . '; ' . $currencies->format($_POST['mc_gross'], false, $_POST['mc_currency']) . ')';

        if ($_POST['payment_status'] == 'Pending') {
          $comment_status .= '; ' . $_POST['pending_reason'];
        } elseif ( ($_POST['payment_status'] == 'Reversed') || ($_POST['payment_status'] == 'Refunded') ) {
          $comment_status .= '; ' . $_POST['reason_code'];
        }

        if ($_POST['mc_gross'] != number_format($total['value'] * $order['currency_value'], $currencies->get_decimal_places($order['currency']))) {
          $comment_status .= '; PayPal transaction value (' . tep_output_string_protected($_POST['mc_gross']) . ') does not match order value (' . number_format($total['value'] * $order['currency_value'], $currencies->get_decimal_places($order['currency'])) . ')';
        } elseif ($_POST['payment_status'] == 'Completed') {
          $new_order_status = (MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID > 0 ? MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID : $new_order_status);
        }

        tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . (int)$new_order_status . "', last_modified = now() where orders_id = '" . (int)$_POST['invoice'] . "'");

        $sql_data_array = array('orders_id' => $_POST['invoice'],
                                'orders_status_id' => (int)$new_order_status,
                                'date_added' => 'now()',
                                'customer_notified' => '0',
                                'comments' => 'PayPal IPN Verified [' . $comment_status . ']');

        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
      }
    }
  } else {
    if (tep_not_null(MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL)) {
      $email_body = '$_POST:' . "\n\n";

      reset($_POST);
      while (list($key, $value) = each($_POST)) {
        $email_body .= $key . '=' . $value . "\n";
      }

      $email_body .= "\n" . '$_GET:' . "\n\n";

      reset($_GET);
      while (list($key, $value) = each($_GET)) {
        $email_body .= $key . '=' . $value . "\n";
      }

      tep_mail('', MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL, 'PayPal IPN Invalid Process', $email_body, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
    }
  }

  require('includes/application_bottom.php');
?>
