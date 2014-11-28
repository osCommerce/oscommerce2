<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  if ( !class_exists('OSCOM_PayPal') ) {
    include(DIR_FS_CATALOG . 'includes/apps/paypal/OSCOM_PayPal.php');
  }

  class paypal_hook_admin_orders_action {
    function paypal_hook_admin_orders_action() {
      global $OSCOM_PayPal;

      if ( !isset($OSCOM_PayPal) || !is_object($OSCOM_PayPal) || (get_class($OSCOM_PayPal) != 'OSCOM_PayPal') ) {
        $OSCOM_PayPal = new OSCOM_PayPal();
      }

      $this->_app = $OSCOM_PayPal;
    }

    function execute() {
      global $HTTP_GET_VARS, $HTTP_POST_VARS;

      if ( isset($HTTP_GET_VARS['tabaction']) ) {
        $ppstatus_query = tep_db_query("select comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$HTTP_GET_VARS['oID'] . "' and orders_status_id = '" . (int)OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID . "' and comments like '%Transaction ID:%' order by date_added limit 1");
        if ( tep_db_num_rows($ppstatus_query) ) {
          $ppstatus = tep_db_fetch_array($ppstatus_query);

          $pp = array();

          foreach ( explode("\n", $ppstatus['comments']) as $s ) {
            if ( !empty($s) && (strpos($s, ':') !== false) ) {
              $entry = explode(':', $s, 2);

              $pp[trim($entry[0])] = trim($entry[1]);
            }
          }

          if ( isset($pp['Transaction ID']) ) {
            $o_query = tep_db_query("select o.orders_id, o.payment_method, o.currency, o.currency_value, ot.value as total from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . " ot where o.orders_id = '" . (int)$HTTP_GET_VARS['oID'] . "' and o.orders_id = ot.orders_id and ot.class = 'ot_total'");
            $o = tep_db_fetch_array($o_query);

            switch ( $HTTP_GET_VARS['tabaction'] ) {
              case 'getTransactionDetails':
                $this->getTransactionDetails($pp, $o);
                break;

              case 'doCapture':
                $this->doCapture($pp, $o);
                break;

              case 'doVoid':
                $this->doVoid($pp, $o);
                break;

              case 'refundTransaction':
                $this->refundTransaction($pp, $o);
                break;
            }

            tep_redirect(tep_href_link(FILENAME_ORDERS, 'page=' . $HTTP_GET_VARS['page'] . '&oID=' . $HTTP_GET_VARS['oID'] . '&action=edit#section_status_history_content'));
          }
        }
      }
    }

    function getTransactionDetails($comments, $order) {
      $result = null;

      if ( !isset($comments['Gateway']) ) {
        $response = $this->_app->getApiResult('APP', 'GetTransactionDetails', array('TRANSACTIONID' => $comments['Transaction ID']), (strpos($order['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

        if ( in_array($response['ACK'], array('Success', 'SuccessWithWarning')) ) {
          $result = 'Transaction ID: ' . tep_output_string_protected($response['TRANSACTIONID']) . "\n" .
                    'Payer Status: ' . tep_output_string_protected($response['PAYERSTATUS']) . "\n" .
                    'Address Status: ' . tep_output_string_protected($response['ADDRESSSTATUS']) . "\n" .
                    'Payment Status: ' . tep_output_string_protected($response['PAYMENTSTATUS']) . "\n" .
                    'Payment Type: ' . tep_output_string_protected($response['PAYMENTTYPE']) . "\n" .
                    'Pending Reason: ' . tep_output_string_protected($response['PENDINGREASON']);
        }
      } elseif ( $comments['Gateway'] == 'Payflow' ) {
        $response = $this->_app->getApiResult('APP', 'PayflowInquiry', array('ORIGID' => $comments['Transaction ID']), (strpos($order['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

        if ( isset($response['RESULT']) && ($response['RESULT'] == '0') ) {
          $result = 'Transaction ID: ' . tep_output_string_protected($response['ORIGPNREF']) . "\n" .
                    'Gateway: Payflow' . "\n";

          $pending_reason = $response['TRANSSTATE'];
          $payment_status = null;

          switch ( $response['TRANSSTATE'] ) {
            case '3':
              $pending_reason = 'authorization';
              $payment_status = 'Pending';
              break;

            case '4':
              $pending_reason = 'other';
              $payment_status = 'In-Progress';
              break;

            case '6':
              $pending_reason = 'scheduled';
              $payment_status = 'Pending';
              break;

            case '8':
            case '9':
              $pending_reason = 'None';
              $payment_status = 'Completed';
              break;
          }

          if ( isset($payment_status) ) {
            $result .= 'Payment Status: ' . tep_output_string_protected($payment_status) . "\n";
          }

          $result .= 'Pending Reason: ' . tep_output_string_protected($pending_reason) . "\n";

          switch ( $response['AVSADDR'] ) {
            case 'Y':
              $result .= 'AVS Address: Match' . "\n";
              break;

            case 'N':
              $result .= 'AVS Address: No Match' . "\n";
              break;
          }

          switch ( $response['AVSZIP'] ) {
            case 'Y':
              $result .= 'AVS ZIP: Match' . "\n";
              break;

            case 'N':
              $result .= 'AVS ZIP: No Match' . "\n";
              break;
          }

          switch ( $response['IAVS'] ) {
            case 'Y':
              $result .= 'IAVS: International' . "\n";
              break;

            case 'N':
              $result .= 'IAVS: USA' . "\n";
              break;
          }

          switch ( $response['CVV2MATCH'] ) {
            case 'Y':
              $result .= 'CVV2: Match' . "\n";
              break;

            case 'N':
              $result .= 'CVV2: No Match' . "\n";
              break;
          }
        }
      }

      if ( !empty($result) ) {
        $sql_data_array = array('orders_id' => (int)$order['orders_id'],
                                'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                                'date_added' => 'now()',
                                'customer_notified' => '0',
                                'comments' => $result);

        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
      }
    }

    function doCapture($comments, $order) {
      global $HTTP_POST_VARS;

      $pass = false;

      $capture_total = $capture_value = $this->_app->formatCurrencyRaw($order['total'], $order['currency'], $order['currency_value']);
      $capture_final = true;

      if ( $this->_app->formatCurrencyRaw($HTTP_POST_VARS['ppCaptureAmount'], $order['currency'], 1) < $capture_value ) {
        $capture_value = $this->_app->formatCurrencyRaw($HTTP_POST_VARS['ppCaptureAmount'], $order['currency'], 1);
        $capture_final = (isset($HTTP_POST_VARS['ppCatureComplete']) && ($HTTP_POST_VARS['ppCatureComplete'] == 'true')) ? true : false;
      }

      if ( !isset($comments['Gateway']) ) {
        $params = array('AUTHORIZATIONID' => $comments['Transaction ID'],
                        'AMT' => $capture_value,
                        'CURRENCYCODE' => $order['currency'],
                        'COMPLETETYPE' => ($capture_final === true) ? 'Complete' : 'NotComplete');

        $response = $this->_app->getApiResult('APP', 'DoCapture', $params, (strpos($order['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

        if ( in_array($response['ACK'], array('Success', 'SuccessWithWarning')) ) {
          $transaction_id = $response['TRANSACTIONID'];

          $pass = true;
        }
      } elseif ( $comments['Gateway'] == 'Payflow' ) {
        $params = array('ORIGID' => $comments['Transaction ID'],
                        'AMT' => $capture_value,
                        'CAPTURECOMPLETE' => ($capture_final === true) ? 'Y' : 'N');

        $response = $this->_app->getApiResult('APP', 'PayflowCapture', $params, (strpos($order['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

        if ( isset($response['RESULT']) && ($response['RESULT'] == '0') ) {
          $transaction_id = $response['PNREF'];

          $pass = true;
        }
      }

      if ( $pass === true ) {
        $result = 'PayPal App: Capture (' . $capture_value . ')' . "\n";

        if ( ($capture_value < $capture_total) && ($capture_final === true) ) {
          $result .= 'PayPal App: Void (' . $this->_app->formatCurrencyRaw($capture_total - $capture_value, $order['currency'], 1) . ')' . "\n";
        }

        $result .= 'Transaction ID: ' . tep_output_string_protected($transaction_id);

        $sql_data_array = array('orders_id' => (int)$order['orders_id'],
                                'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                                'date_added' => 'now()',
                                'customer_notified' => '0',
                                'comments' => $result);

        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
      }
    }

    function doVoid($comments, $order) {
      $pass = false;

      if ( !isset($comments['Gateway']) ) {
        $response = $this->_app->getApiResult('APP', 'DoVoid', array('AUTHORIZATIONID' => $comments['Transaction ID']), (strpos($order['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

        if ( in_array($response['ACK'], array('Success', 'SuccessWithWarning')) ) {
          $pass = true;
        }
      } elseif ( $comments['Gateway'] == 'Payflow' ) {
        $response = $this->_app->getApiResult('APP', 'PayflowVoid', array('ORIGID' => $comments['Transaction ID']), (strpos($order['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

        if ( isset($response['RESULT']) && ($response['RESULT'] == '0') ) {
          $pass = true;
        }
      }

      if ( $pass === true ) {
        $capture_total = $this->_app->formatCurrencyRaw($order['total'], $order['currency'], $order['currency_value']);

        $c_query = tep_db_query("select comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$order['orders_id'] . "' and orders_status_id = '" . (int)OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID . "' and comments like 'PayPal App: Capture (%'");
        while ( $c = tep_db_fetch_array($c_query) ) {
          if ( preg_match('/^PayPal App\: Capture \(([0-9\.]+)\)\n/', $c['comments'], $c_matches) ) {
            $capture_total -= $this->_app->formatCurrencyRaw($c_matches[1], $order['currency'], 1);
          }
        }

        $result = 'PayPal App: Void (' . $capture_total . ')';

        $sql_data_array = array('orders_id' => (int)$order['orders_id'],
                                'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                                'date_added' => 'now()',
                                'customer_notified' => '0',
                                'comments' => $result);

        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
      }
    }

    function refundTransaction($comments, $order) {
      global $HTTP_POST_VARS;

      if ( isset($HTTP_POST_VARS['ppRefund']) ) {
        $tids = array();

        $ppr_query = tep_db_query("select comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$order['orders_id'] . "' and orders_status_id = '" . (int)OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID . "' and comments like 'PayPal App: %' order by date_added desc");
        if ( tep_db_num_rows($ppr_query) ) {
          while ( $ppr = tep_db_fetch_array($ppr_query) ) {
            if ( strpos($ppr['comments'], 'PayPal App: Refund') !== false ) {
              preg_match('/Parent ID\: ([A-Za-z0-9]+)$/', $ppr['comments'], $ppr_matches);

              $tids[$ppr_matches[1]]['Refund'] = true;
            } elseif ( strpos($ppr['comments'], 'PayPal App: Capture') !== false ) {
              preg_match('/^PayPal App\: Capture \(([0-9\.]+)\).*Transaction ID\: ([A-Za-z0-9]+)/s', $ppr['comments'], $ppr_matches);

              $tids[$ppr_matches[2]]['Amount'] = $ppr_matches[1];
            }
          }
        } elseif ( $comments['Payment Status'] == 'Completed' ) {
          $tids[$comments['Transaction ID']]['Amount'] = $this->_app->formatCurrencyRaw($order['total'], $order['currency'], $order['currency_value']);
        }

        $rids = array();

        foreach ( $HTTP_POST_VARS['ppRefund'] as $id ) {
          if ( isset($tids[$id]) && !isset($tids[$id]['Refund']) ) {
            $rids[] = $id;
          }
        }

        foreach ( $rids as $id ) {
          $pass = false;

          if ( !isset($comments['Gateway']) ) {
            $response = $this->_app->getApiResult('APP', 'RefundTransaction', array('TRANSACTIONID' => $id), (strpos($order['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

            if ( in_array($response['ACK'], array('Success', 'SuccessWithWarning')) ) {
              $transaction_id = $response['REFUNDTRANSACTIONID'];

              $pass = true;
            }
          } elseif ( $comments['Gateway'] == 'Payflow' ) {
            $response = $this->_app->getApiResult('APP', 'PayflowRefund', array('ORIGID' => $id), (strpos($order['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

            if ( isset($response['RESULT']) && ($response['RESULT'] == '0') ) {
              $transaction_id = $response['PNREF'];

              $pass = true;
            }
          }

          if ( $pass === true ) {
            $result = 'PayPal App: Refund (' . $tids[$id]['Amount'] . ')' . "\n" .
                      'Transaction ID: ' . tep_output_string_protected($transaction_id) . "\n" .
                      'Parent ID: ' . tep_output_string_protected($id);

            $sql_data_array = array('orders_id' => (int)$order['orders_id'],
                                    'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                                    'date_added' => 'now()',
                                    'customer_notified' => '0',
                                    'comments' => $result);

            tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
          }
        }
      }
    }
  }
?>
