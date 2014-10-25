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

            $OSCOM_PayPal = new OSCOM_PayPal();

            switch ( $HTTP_GET_VARS['tabaction'] ) {
              case 'getTransactionDetails':
                $pp_result = null;

                if ( !isset($pp['Gateway']) ) {
                  $result = $OSCOM_PayPal->getApiResult('APP', 'GetTransactionDetails', array('TRANSACTIONID' => $pp['Transaction ID']), (strpos($o['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

                  if ( in_array($result['ACK'], array('Success', 'SuccessWithWarning')) ) {
                    $pp_result = 'Transaction ID: ' . tep_output_string_protected($result['TRANSACTIONID']) . "\n" .
                                 'Payer Status: ' . tep_output_string_protected($result['PAYERSTATUS']) . "\n" .
                                 'Address Status: ' . tep_output_string_protected($result['ADDRESSSTATUS']) . "\n" .
                                 'Payment Status: ' . tep_output_string_protected($result['PAYMENTSTATUS']) . "\n" .
                                 'Payment Type: ' . tep_output_string_protected($result['PAYMENTTYPE']) . "\n" .
                                 'Pending Reason: ' . tep_output_string_protected($result['PENDINGREASON']);
                  }
                } elseif ( $pp['Gateway'] == 'Payflow' ) {
                  $result = $OSCOM_PayPal->getApiResult('APP', 'PayflowInquiry', array('ORIGID' => $pp['Transaction ID']), (strpos($o['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

                  if ( isset($result['RESULT']) && ($result['RESULT'] == '0') ) {
                    $pp_result = 'Transaction ID: ' . tep_output_string_protected($result['ORIGPNREF']) . "\n" .
                                 'Gateway: Payflow' . "\n";

                    $pending_reason = $result['TRANSSTATE'];
                    $payment_status = null;

                    switch ( $result['TRANSSTATE'] ) {
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
                      $pp_result .= 'Payment Status: ' . tep_output_string_protected($payment_status) . "\n";
                    }

                    $pp_result .= 'Pending Reason: ' . tep_output_string_protected($pending_reason) . "\n";

                    switch ( $result['AVSADDR'] ) {
                      case 'Y':
                        $pp_result .= 'AVS Address: Match' . "\n";
                        break;

                      case 'N':
                        $pp_result .= 'AVS Address: No Match' . "\n";
                        break;
                    }

                    switch ( $result['AVSZIP'] ) {
                      case 'Y':
                        $pp_result .= 'AVS ZIP: Match' . "\n";
                        break;

                      case 'N':
                        $pp_result .= 'AVS ZIP: No Match' . "\n";
                        break;
                    }

                    switch ( $result['IAVS'] ) {
                      case 'Y':
                        $pp_result .= 'IAVS: International' . "\n";
                        break;

                      case 'N':
                        $pp_result .= 'IAVS: USA' . "\n";
                        break;
                    }

                    switch ( $result['CVV2MATCH'] ) {
                      case 'Y':
                        $pp_result .= 'CVV2: Match' . "\n";
                        break;

                      case 'N':
                        $pp_result .= 'CVV2: No Match' . "\n";
                        break;
                    }
                  }
                }

                if ( tep_not_null($pp_result) ) {
                  $sql_data_array = array('orders_id' => (int)$HTTP_GET_VARS['oID'],
                                          'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                                          'date_added' => 'now()',
                                          'customer_notified' => '0',
                                          'comments' => $pp_result);

                  tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
                }

                break;

              case 'doCapture':
                $pp_pass = false;

                $capture_total = $capture_value = $OSCOM_PayPal->formatCurrencyRaw($o['total'], $o['currency'], $o['currency_value']);
                $capture_final = true;

                if ( $OSCOM_PayPal->formatCurrencyRaw($HTTP_POST_VARS['ppCaptureAmount'], $o['currency'], 1) < $capture_value ) {
                  $capture_value = $OSCOM_PayPal->formatCurrencyRaw($HTTP_POST_VARS['ppCaptureAmount'], $o['currency'], 1);
                  $capture_final = (isset($HTTP_POST_VARS['ppCatureComplete']) && ($HTTP_POST_VARS['ppCatureComplete'] == 'true')) ? true : false;
                }

                if ( !isset($pp['Gateway']) ) {
                  $params = array('AUTHORIZATIONID' => $pp['Transaction ID'],
                                  'AMT' => $capture_value,
                                  'CURRENCYCODE' => $o['currency'],
                                  'COMPLETETYPE' => ($capture_final === true) ? 'Complete' : 'NotComplete');

                  $result = $OSCOM_PayPal->getApiResult('APP', 'DoCapture', $params, (strpos($o['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

                  if ( in_array($result['ACK'], array('Success', 'SuccessWithWarning')) ) {
                    $transaction_id = $result['TRANSACTIONID'];

                    $pp_pass = true;
                  }
                } elseif ( $pp['Gateway'] == 'Payflow' ) {
                  $params = array('ORIGID' => $pp['Transaction ID'],
                                  'AMT' => $capture_value,
                                  'CAPTURECOMPLETE' => ($capture_final === true) ? 'Y' : 'N');

                  $result = $OSCOM_PayPal->getApiResult('APP', 'PayflowCapture', $params, (strpos($o['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

                  if ( isset($result['RESULT']) && ($result['RESULT'] == '0') ) {
                    $transaction_id = $result['PNREF'];

                    $pp_pass = true;
                  }
                }

                if ( $pp_pass === true ) {
                  $pp_result = 'PayPal App: Capture (' . $capture_value . ')' . "\n";

                  if ( ($capture_value < $capture_total) && ($capture_final === true) ) {
                    $pp_result .= 'PayPal App: Void (' . $OSCOM_PayPal->formatCurrencyRaw($capture_total - $capture_value, $o['currency'], 1) . ')' . "\n";
                  }

                  $pp_result .= 'Transaction ID: ' . tep_output_string_protected($transaction_id);

                  $sql_data_array = array('orders_id' => (int)$HTTP_GET_VARS['oID'],
                                          'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                                          'date_added' => 'now()',
                                          'customer_notified' => '0',
                                          'comments' => $pp_result);

                  tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
                }

                break;

              case 'doVoid':
                $pp_pass = false;

                if ( !isset($pp['Gateway']) ) {
                  $result = $OSCOM_PayPal->getApiResult('APP', 'DoVoid', array('AUTHORIZATIONID' => $pp['Transaction ID']), (strpos($o['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

                  if ( in_array($result['ACK'], array('Success', 'SuccessWithWarning')) ) {
                    $pp_pass = true;
                  }
                } elseif ( $pp['Gateway'] == 'Payflow' ) {
                  $result = $OSCOM_PayPal->getApiResult('APP', 'PayflowVoid', array('ORIGID' => $pp['Transaction ID']), (strpos($o['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

                  if ( isset($result['RESULT']) && ($result['RESULT'] == '0') ) {
                    $pp_pass = true;
                  }
                }

                if ( $pp_pass === true ) {
                  $capture_total = $OSCOM_PayPal->formatCurrencyRaw($o['total'], $o['currency'], $o['currency_value']);

                  $c_query = tep_db_query("select comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$o['orders_id'] . "' and orders_status_id = '" . (int)OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID . "' and comments like 'PayPal App: Capture (%'");
                  while ( $c = tep_db_fetch_array($c_query) ) {
                    if ( preg_match('/^PayPal App\: Capture \(([0-9\.]+)\)\n/', $c['comments'], $c_matches) ) {
                      $capture_total -= $OSCOM_PayPal->formatCurrencyRaw($c_matches[1], $order['currency'], 1);
                    }
                  }

                  $pp_result = 'PayPal App: Void (' . $capture_total . ')';

                  $sql_data_array = array('orders_id' => (int)$HTTP_GET_VARS['oID'],
                                          'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                                          'date_added' => 'now()',
                                          'customer_notified' => '0',
                                          'comments' => $pp_result);

                  tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
                }

                break;

              case 'refundTransaction':
                if ( isset($HTTP_POST_VARS['ppRefund']) ) {
                  $tids = array();

                  $ppr_query = tep_db_query("select comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$HTTP_GET_VARS['oID'] . "' and orders_status_id = '" . (int)OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID . "' and comments like 'PayPal App: %' order by date_added desc");
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
                  } elseif ( $pp['Payment Status'] == 'Completed' ) {
                    $tids[$pp['Transaction ID']]['Amount'] = $OSCOM_PayPal->formatCurrencyRaw($o['total'], $o['currency'], $o['currency_value']);
                  }

                  $rids = array();

                  foreach ( $HTTP_POST_VARS['ppRefund'] as $id ) {
                    if ( isset($tids[$id]) && !isset($tids[$id]['Refund']) ) {
                      $rids[] = $id;
                    }
                  }

                  foreach ( $rids as $id ) {
                    $pp_pass = false;

                    if ( !isset($pp['Gateway']) ) {
                      $result = $OSCOM_PayPal->getApiResult('APP', 'RefundTransaction', array('TRANSACTIONID' => $id), (strpos($o['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

                      if ( in_array($result['ACK'], array('Success', 'SuccessWithWarning')) ) {
                        $transaction_id = $result['REFUNDTRANSACTIONID'];

                        $pp_pass = true;
                      }
                    } elseif ( $pp['Gateway'] == 'Payflow' ) {
                      $result = $OSCOM_PayPal->getApiResult('APP', 'PayflowRefund', array('ORIGID' => $id), (strpos($o['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

                      if ( isset($result['RESULT']) && ($result['RESULT'] == '0') ) {
                        $transaction_id = $result['PNREF'];

                        $pp_pass = true;
                      }
                    }

                    if ( $pp_pass === true ) {
                      $pp_result = 'PayPal App: Refund (' . $tids[$id]['Amount'] . ')' . "\n" .
                                   'Transaction ID: ' . tep_output_string_protected($transaction_id) . "\n" .
                                   'Parent ID: ' . tep_output_string_protected($id);

                      $sql_data_array = array('orders_id' => (int)$HTTP_GET_VARS['oID'],
                                              'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                                              'date_added' => 'now()',
                                              'customer_notified' => '0',
                                              'comments' => $pp_result);

                      tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
                    }
                  }
                }

                break;
            }
          }
        }
      }
    }
  }
?>
