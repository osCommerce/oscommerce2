<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  if (!isset($_SESSION['customer_id'])) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link('login.php', '', 'SSL'));
  }

  if (!isset($_GET['order_id']) || (isset($_GET['order_id']) && !is_numeric($_GET['order_id']))) {
    tep_redirect(tep_href_link('account_history.php', '', 'SSL'));
  }

  $customer_info_query = tep_db_query("select o.customers_id from orders o, orders_status s where o.orders_id = '". (int)$_GET['order_id'] . "' and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$_SESSION['languages_id'] . "' and s.public_flag = '1'");
  $customer_info = tep_db_fetch_array($customer_info_query);
  if ($customer_info['customers_id'] != $customer_id) {
    tep_redirect(tep_href_link('account_history.php', '', 'SSL'));
  }

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/account_history_info.php');

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link('account.php', '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link('account_history.php', '', 'SSL'));
  $breadcrumb->add(sprintf(NAVBAR_TITLE_3, $_GET['order_id']), tep_href_link('account_history_info.php', 'order_id=' . $_GET['order_id'], 'SSL'));

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order($_GET['order_id']);

  require('includes/template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<div class="contentContainer">

  <div class="contentText">

    <div class="panel panel-success">
      <div class="panel-heading"><strong><?php echo sprintf(HEADING_ORDER_NUMBER, $_GET['order_id']) . ' <span class="badge pull-right">' . $order->info['orders_status'] . '</span>'; ?></strong></div>
      <div class="panel-body">
        <table class="table table-hover table-striped order_confirmation">
<?php
  if (sizeof($order->info['tax_groups']) > 1) {
?>
          <tr>
            <th><?php echo HEADING_PRODUCTS; ?></th>
            <th><?php echo HEADING_TAX; ?></th>
            <th><?php echo HEADING_TOTAL; ?></th>
          </tr>
<?php
  } else {
?>
          <tr>
            <th colspan="2"><?php echo HEADING_PRODUCTS; ?></th>
            <th class="text-right"><?php echo HEADING_TOTAL; ?></th>
          </tr>
<?php
  }

  for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
    echo '          <tr>' . "\n" .
         '            <td class="text-right" valign="top" width="30">' . $order->products[$i]['qty'] . '&nbsp;x&nbsp;</td>' . "\n" .
         '            <td valign="top">' . $order->products[$i]['name'];

    if ( (isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0) ) {
      for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
        echo '<br /><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'] . '</i></small></nobr>';
      }
    }

    echo '</td>' . "\n";

    if (sizeof($order->info['tax_groups']) > 1) {
      echo '            <td valign="top" class="text-right">' . tep_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n";
    }

    echo '            <td class="text-right" valign="top">' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</td>' . "\n" .
         '          </tr>' . "\n";
  }
?>
        </table>
        <hr>
        <table width="100%" class="pull-right">
<?php
  for ($i=0, $n=sizeof($order->totals); $i<$n; $i++) {
    echo '          <tr>' . "\n" .
         '            <td align="right" width="100%">' . $order->totals[$i]['title'] . '&nbsp;</td>' . "\n" .
         '            <td align="right">' . $order->totals[$i]['text'] . '</td>' . "\n" .
         '          </tr>' . "\n";
  }
?>
        </table>
      </div>
      <div class="panel-footer">
        <span class="pull-right hidden-xs"><?php echo HEADING_ORDER_TOTAL . ' ' . $order->info['total']; ?></span><?php echo HEADING_ORDER_DATE . ' ' . tep_date_long($order->info['date_purchased']); ?>
      </div>
    </div>

  </div>

  <div class="clearfix"></div>

  <div class="row">
    <?php
    if ($order->delivery != false) {
      ?>
      <div class="col-sm-3">
        <div class="panel panel-info">
          <div class="panel-heading"><?php echo '<strong>' . HEADING_DELIVERY_ADDRESS . '</strong>'; ?></div>
          <div class="panel-body">
            <?php echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br />'); ?>
          </div>
        </div>
      </div>
      <?php
    }
    ?>
    <div class="col-sm-3">
      <div class="panel panel-warning">
        <div class="panel-heading"><?php echo '<strong>' . HEADING_BILLING_ADDRESS . '</strong>'; ?></div>
        <div class="panel-body">
          <?php echo tep_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br />'); ?>
        </div>
      </div>
    </div>

    <?php
    if ($order->info['shipping_method']) {
      ?>
      <div class="col-sm-3">
        <div class="panel panel-info">
          <div class="panel-heading"><?php echo '<strong>' . HEADING_SHIPPING_METHOD . '</strong>'; ?></div>
          <div class="panel-body">
            <?php echo $order->info['shipping_method']; ?>
          </div>
        </div>
      </div>
      <?php
    }
    ?>
    <div class="col-sm-3">
      <div class="panel panel-warning">
        <div class="panel-heading"><?php echo '<strong>' . HEADING_PAYMENT_METHOD . '</strong>'; ?></div>
        <div class="panel-body">
          <?php echo $order->info['payment_method']; ?>
        </div>
      </div>
    </div>
  </div>
  
  <div class="clearfix"></div>

  <div class="page-header">
    <h4><?php echo HEADING_ORDER_HISTORY; ?></h4>
  </div>

  <div class="contentText">
    <ul class="timeline">
      <?php
      $statuses_query = tep_db_query("select os.orders_status_name, osh.date_added, osh.comments from orders_status os, orders_status_history osh where osh.orders_id = '" . (int)$_GET['order_id'] . "' and osh.orders_status_id = os.orders_status_id and os.language_id = '" . (int)$_SESSION['languages_id'] . "' and os.public_flag = '1' order by osh.date_added");
      while ($statuses = tep_db_fetch_array($statuses_query)) {
        echo '<li>';
        echo '  <div class="timeline-badge"><i class="glyphicon glyphicon-check"></i></div>';
        echo '  <div class="timeline-panel">';
        echo '    <div class="timeline-heading">';
        echo '      <p class="pull-right"><small class="text-muted"><i class="glyphicon glyphicon-time"></i> ' . tep_date_short($statuses['date_added']) . '</small></p><h4 class="timeline-title">' . $statuses['orders_status_name'] . '</h4>';
        echo '    </div>';
        echo '    <div class="timeline-body">';
        echo '      <p><blockquote>' . (empty($statuses['comments']) ? TEXT_NO_COMMENTS : nl2br(tep_output_string_protected($statuses['comments']))) . '</blockquote></p>';
        echo '    </div>';
        echo '  </div>';
        echo '</li>';
      }
      ?>
    </ul>
  </div>

<?php
  if (DOWNLOAD_ENABLED == 'true') include(DIR_WS_MODULES . 'downloads.php');
?>

  <div class="clearfix"></div>
  <div>
    <?php echo tep_draw_button(IMAGE_BUTTON_BACK, 'glyphicon glyphicon-chevron-left', tep_href_link('account_history.php', tep_get_all_get_params(array('order_id')), 'SSL')); ?>
  </div>
</div>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
