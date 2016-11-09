<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  if ( !class_exists('OSCOM_Braintree') ) {
    include(DIR_FS_CATALOG . 'includes/apps/braintree/OSCOM_Braintree.php');
  }

  class braintree_hook_admin_orders_tab {
    function braintree_hook_admin_orders_tab() {
      global $OSCOM_Braintree;

      if ( !isset($OSCOM_Braintree) || !is_object($OSCOM_Braintree) || (get_class($OSCOM_Braintree) != 'OSCOM_Braintree') ) {
        $OSCOM_Braintree = new OSCOM_Braintree();
      }

      $this->_app = $OSCOM_Braintree;

      $this->_app->loadLanguageFile('hooks/admin/orders/tab.php');
    }

    function execute() {
      global $HTTP_GET_VARS, $oID, $base_url;

      if (!defined('OSCOM_APP_PAYPAL_BRAINTREE_TRANSACTIONS_ORDER_STATUS_ID')) {
        return false;
      }

      $output = '';

      $status = array();

      $btstatus_query = tep_db_query("select comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$oID . "' and orders_status_id = '" . (int)OSCOM_APP_PAYPAL_BRAINTREE_TRANSACTIONS_ORDER_STATUS_ID . "' and comments like 'Transaction ID:%' order by date_added desc limit 1");
      if ( tep_db_num_rows($btstatus_query) ) {
        $btstatus = tep_db_fetch_array($btstatus_query);

        foreach ( explode("\n", $btstatus['comments']) as $s ) {
          if ( !empty($s) && (strpos($s, ':') !== false) ) {
            $entry = explode(':', $s, 2);

            $status[trim($entry[0])] = trim($entry[1]);
          }
        }

        if ( isset($status['Transaction ID']) ) {
          $order_query = tep_db_query("select o.orders_id, o.payment_method, o.currency, o.currency_value, ot.value as total from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . " ot where o.orders_id = '" . (int)$oID . "' and o.orders_id = ot.orders_id and ot.class = 'ot_total'");
          $order = tep_db_fetch_array($order_query);

          $bt_server = (strpos(strtolower($order['payment_method']), 'sandbox') !== false) ? 'sandbox' : 'live';

          $info_button = $this->_app->drawButton($this->_app->getDef('button_details'), tep_href_link(FILENAME_ORDERS, 'page=' . $HTTP_GET_VARS['page'] . '&oID=' . $oID . '&action=edit&tabaction=getTransactionDetails'), 'primary', null, true);
          $capture_button = $this->getCaptureButton($status, $order);
          $void_button = $this->getVoidButton($status, $order);
          $refund_button = $this->getRefundButton($status, $order);
          $braintree_button = $this->_app->drawButton($this->_app->getDef('button_view_at_braintree'), 'https://www.' . ($bt_server == 'sandbox' ? 'sandbox.' : '') . 'braintreegateway.com/merchants/' . ($bt_server == 'sandbox' ? OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_MERCHANT_ID : OSCOM_APP_PAYPAL_BRAINTREE_MERCHANT_ID) . '/transactions/' . $status['Transaction ID'], 'info', 'target="_blank"', true);

          $tab_title = addslashes($this->_app->getDef('tab_title'));
          $tab_link = substr(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params()), strlen($base_url)) . '#section_braintree_content';

          $output = <<<EOD
<script>
$(function() {
  $('#orderTabs ul').append('<li><a href="{$tab_link}">{$tab_title}</a></li>');
});
</script>

<div id="section_braintree_content" style="padding: 10px;">
  {$info_button} {$capture_button} {$void_button} {$refund_button} {$braintree_button}
</div>
EOD;

        }
      }

      return $output;
    }

    function getCaptureButton($status, $order) {
      global $HTTP_GET_VARS;

      $output = '';

      if ($status['Payment Status'] == 'authorized') {
        $v_query = tep_db_query("select comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$order['orders_id'] . "' and orders_status_id = '" . (int)OSCOM_APP_PAYPAL_BRAINTREE_TRANSACTIONS_ORDER_STATUS_ID . "' and comments like 'Braintree App: Void (%' limit 1");

        if ( !tep_db_num_rows($v_query) ) {
          $c_query = tep_db_query("select comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$order['orders_id'] . "' and orders_status_id = '" . (int)OSCOM_APP_PAYPAL_BRAINTREE_TRANSACTIONS_ORDER_STATUS_ID . "' and comments like 'Braintree App: Capture (%' limit 1");

          if ( !tep_db_num_rows($c_query) ) {
            $output .= $this->_app->drawButton($this->_app->getDef('button_dialog_capture'), '#', 'success', 'data-button="braintreeButtonDoCapture"', true);

            $dialog_title = tep_output_string_protected($this->_app->getDef('dialog_capture_title'));
            $dialog_body = $this->_app->getDef('dialog_capture_body');
            $field_amount_title = $this->_app->getDef('dialog_capture_amount_field_title');
            $capture_link = tep_href_link(FILENAME_ORDERS, 'page=' . $HTTP_GET_VARS['page'] . '&oID=' . $order['orders_id'] . '&action=edit&tabaction=doCapture');
            $capture_currency = $order['currency'];
            $capture_total = $this->_app->formatCurrencyRaw($order['total'], $order['currency'], $order['currency_value']);
            $dialog_button_capture = addslashes($this->_app->getDef('dialog_capture_button_capture'));
            $dialog_button_cancel = addslashes($this->_app->getDef('dialog_capture_button_cancel'));

            $output .= <<<EOD
<div id="braintree-dialog-capture" title="{$dialog_title}">
  <form id="btCaptureForm" action="{$capture_link}" method="post">
    <p>{$dialog_body}</p>

    <p>
      <label for="btCaptureAmount"><strong>{$field_amount_title}</strong></label>
      <input type="text" name="btCaptureAmount" value="{$capture_total}" id="btCaptureAmount" style="text-align: right;" />
      {$capture_currency}
    </p>
  </form>
</div>

<script>
$(function() {
  $('#braintree-dialog-capture').dialog({
    autoOpen: false,
    resizable: false,
    modal: true,
    buttons: {
      "{$dialog_button_capture}": function() {
        $('#btCaptureForm').submit();
      },
      "{$dialog_button_cancel}": function() {
        $(this).dialog('close');
      }
    }
  });

  $('a[data-button="braintreeButtonDoCapture"]').click(function(e) {
    e.preventDefault();

    $('#braintree-dialog-capture').dialog('open');
  });
});
</script>
EOD;
          }
        }
      }

      return $output;
    }

    function getVoidButton($status, $order) {
      global $HTTP_GET_VARS;

      $output = '';

      $s_query = tep_db_query("select comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$order['orders_id'] . "' and orders_status_id = '" . (int)OSCOM_APP_PAYPAL_BRAINTREE_TRANSACTIONS_ORDER_STATUS_ID . "' and comments like '%Payment Status:%' order by date_added desc limit 1");

      if (tep_db_num_rows($s_query)) {
        $s = tep_db_fetch_array($s_query);

        $last_status = array();

        foreach (explode("\n", $s['comments']) as $status) {
          if (!empty($status) && (strpos($status, ':') !== false) && (substr($status, 0, 1) !== '[')) {
            $entry = explode(':', $status, 2);

            $key = trim($entry[0]);
            $value = trim($entry[1]);

            if ((strlen($key) > 0) && (strlen($value) > 0)) {
              $last_status[$key] = $value;
            }
          }
        }

        if (($last_status['Payment Status'] == 'authorized') || ($last_status['Payment Status'] == 'submitted_for_settlement')) {
          $v_query = tep_db_query("select comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$order['orders_id'] . "' and orders_status_id = '" . (int)OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID . "' and (comments like 'Braintree App: Void (%' or comments like 'Braintree App: Refund (%') limit 1");

          if ( !tep_db_num_rows($v_query) ) {
            $output .= $this->_app->drawButton($this->_app->getDef('button_dialog_void'), '#', 'warning', 'data-button="braintreeButtonDoVoid"', true);

            $dialog_title = tep_output_string_protected($this->_app->getDef('dialog_void_title'));
            $dialog_body = $this->_app->getDef('dialog_void_body');
            $void_link = tep_href_link(FILENAME_ORDERS, 'page=' . $HTTP_GET_VARS['page'] . '&oID=' . $order['orders_id'] . '&action=edit&tabaction=doVoid');
            $dialog_button_void = addslashes($this->_app->getDef('dialog_void_button_void'));
            $dialog_button_cancel = addslashes($this->_app->getDef('dialog_void_button_cancel'));

            $output .= <<<EOD
<div id="braintree-dialog-void" title="{$dialog_title}">
  <p>{$dialog_body}</p>
</div>

<script>
$(function() {
  $('#braintree-dialog-void').dialog({
    autoOpen: false,
    resizable: false,
    modal: true,
    buttons: {
      "{$dialog_button_void}": function() {
        window.location = '{$void_link}';
      },
      "{$dialog_button_cancel}": function() {
        $(this).dialog('close');
      }
    }
  });

  $('a[data-button="braintreeButtonDoVoid"]').click(function(e) {
    e.preventDefault();

    $('#braintree-dialog-void').dialog('open');
  });
});
</script>
EOD;
          }
        }
      }

      return $output;
    }

    function getRefundButton($status, $order) {
      global $HTTP_GET_VARS;

      $output = '';

      $s_query = tep_db_query("select comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$order['orders_id'] . "' and orders_status_id = '" . (int)OSCOM_APP_PAYPAL_BRAINTREE_TRANSACTIONS_ORDER_STATUS_ID . "' and comments not like 'Braintree App: Refund (%' and comments like '%Payment Status:%' order by date_added desc limit 1");

      if ( tep_db_num_rows($s_query) ) {
        $s = tep_db_fetch_array($s_query);

        $last_status = array();

        foreach (explode("\n", $s['comments']) as $status) {
          if (!empty($status) && (strpos($status, ':') !== false) && (substr($status, 0, 1) !== '[')) {
            $entry = explode(':', $status, 2);

            $key = trim($entry[0]);
            $value = trim($entry[1]);

            if ((strlen($key) > 0) && (strlen($value) > 0)) {
              $last_status[$key] = $value;
            }
          }
        }

        if (($last_status['Payment Status'] == 'settled') || ($last_status['Payment Status'] == 'settling')) {
          $refund_total = $this->_app->formatCurrencyRaw($order['total'], $order['currency'], $order['currency_value']);

          $r_query = tep_db_query("select comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$order['orders_id'] . "' and orders_status_id = '" . (int)OSCOM_APP_PAYPAL_BRAINTREE_TRANSACTIONS_ORDER_STATUS_ID . "' and comments like 'Braintree App: Refund (%'");

          while ($r = tep_db_fetch_array($r_query)) {
            if (preg_match('/^Braintree App\: Refund \(([0-9\.]+)\)\n/', $r['comments'], $r_matches)) {
              $refund_total = $this->_app->formatCurrencyRaw($refund_total - $r_matches[1], $order['currency'], 1);
            }
          }

          if ($refund_total > 0) {
            $output .= $this->_app->drawButton($this->_app->getDef('button_dialog_refund'), '#', 'error', 'data-button="braintreeButtonRefundTransaction"', true);

            $dialog_title = tep_output_string_protected($this->_app->getDef('dialog_refund_title'));
            $dialog_body = $this->_app->getDef('dialog_refund_body');
            $field_amount_title = $this->_app->getDef('dialog_refund_amount_field_title');
            $refund_link = tep_href_link(FILENAME_ORDERS, 'page=' . $HTTP_GET_VARS['page'] . '&oID=' . $order['orders_id'] . '&action=edit&tabaction=refundTransaction');
            $refund_currency = $order['currency'];
            $dialog_button_refund = addslashes($this->_app->getDef('dialog_refund_button_refund'));
            $dialog_button_cancel = addslashes($this->_app->getDef('dialog_refund_button_cancel'));

            $output .= <<<EOD
<div id="braintree-dialog-refund" title="{$dialog_title}">
  <form id="btRefundForm" action="{$refund_link}" method="post">
    <p>{$dialog_body}</p>

    <p>
      <label for="btRefundAmount"><strong>{$field_amount_title}</strong></label>
      <input type="text" name="btRefundAmount" value="{$refund_total}" id="btRefundAmount" style="text-align: right;" />
      {$refund_currency}
    </p>
  </form>
</div>

<script>
$(function() {
  $('#braintree-dialog-refund').dialog({
    autoOpen: false,
    resizable: false,
    modal: true,
    buttons: {
      "{$dialog_button_refund}": function() {
        $('#btRefundForm').submit();
      },
      "{$dialog_button_cancel}": function() {
        $(this).dialog('close');
      }
    }
  });

  $('a[data-button="braintreeButtonRefundTransaction"]').click(function(e) {
    e.preventDefault();

    $('#braintree-dialog-refund').dialog('open');
  });
});
</script>
EOD;
          }
        }
      }

      return $output;
    }
  }
?>
