<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $log_query_raw = "select l.id, l.customers_id, l.module, l.action, l.result, l.ip_address, unix_timestamp(l.date_added) as date_added, c.customers_firstname, c.customers_lastname from oscom_app_paypal_log l left join " . TABLE_CUSTOMERS . " c on (l.customers_id = c.customers_id) order by l.date_added desc";
  $log_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $log_query_raw, $log_query_numrows);
  $log_query = tep_db_query($log_query_raw);
?>

<table width="100%" style="margin-bottom: 5px;">
  <tr>
    <td><?php echo $OSCOM_PayPal->drawButton('Delete &hellip;', '#', 'warning', 'data-button="delLogs"'); ?></td>
    <td style="text-align: right;"><?php echo $log_split->display_links($log_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], 'action=log'); ?></td>
  </tr>
</table>

<table id="ppTableLog" class="pp-table pp-table-hover" width="100%">
  <thead>
    <tr>
      <th colspan="2">Action</th>
      <th>IP</th>
      <th>Customer</th>
      <th colspan="2">Date</th>
    </tr>
  </thead>
  <tbody>

<?php
  if ( tep_db_num_rows($log_query) > 0 ) {
    while ($log = tep_db_fetch_array($log_query)) {
      $customers_name = null;

      if ( $log['customers_id'] > 0 ) {
        $customers_name = trim($log['customers_firstname'] . ' ' . $log['customers_lastname']);

        if ( empty($customers_name) ) {
          $customers_name = '- ? -';
        }
      }
?>

    <tr>
      <td style="text-align: center; width: 30px;"><span class="<?php echo ($log['result'] == '1') ? 'logSuccess' : 'logError'; ?>"><?php echo $log['module']; ?></span></td>
      <td><?php echo $log['action']; ?></td>
      <td><?php echo long2ip($log['ip_address']); ?></td>
      <td><?php echo (!empty($customers_name)) ? tep_output_string_protected($customers_name) : '<i>Guest</i>'; ?></td>
      <td><?php echo date(PHP_DATE_TIME_FORMAT, $log['date_added']); ?></td>
      <td class="ppTableAction" style="text-align: right;"><small><?php echo $OSCOM_PayPal->drawButton('View', tep_href_link('paypal.php', 'action=log&page=' . $HTTP_GET_VARS['page'] . '&lID=' . $log['id'] . '&subaction=view'), 'info'); ?></small></td>
    </tr>

<?php
    }
  } else {
?>

    <tr>
      <td colspan="6" style="padding: 10px;">No log entries found.</td>
    </tr>

<?php
  }
?>

  </tbody>
</table>

<table width="100%">
  <tr>
    <td valign="top"><?php echo $log_split->display_count($log_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_PAYPAL_LOGS); ?></td>
    <td style="text-align: right;"><?php echo $log_split->display_links($log_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], 'action=log'); ?></td>
  </tr>
</table>

<div id="delLogs-dialog-confirm" title="Delete all log entries?">
  <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to delete all log entries?</p>
</div>

<script>
$(function() {
  $('#delLogs-dialog-confirm').dialog({
    autoOpen: false,
    resizable: false,
    height: 140,
    modal: true,
    buttons: {
      "Delete All": function() {
        window.location = '<?php echo tep_href_link('paypal.php', 'action=log&subaction=deleteAll'); ?>';
      },
      Cancel: function() {
        $( this ).dialog( "close" );
      }
    }
  });

  $('a[data-button="delLogs"]').click(function(e) {
    e.preventDefault();

    $('#delLogs-dialog-confirm').dialog('open');
  });

  $('#ppTableLog tbody tr td.ppTableAction').css({
    visibility: 'hidden',
    display: 'block'
  });

  $('#ppTableLog tbody tr').hover(
    function() {
      $('td.ppTableAction', this).css('visibility', 'visible');
    },
    function() {
      $('td.ppTableAction', this).css('visibility', 'hidden');
    }
  );
});
</script>
