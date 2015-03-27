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

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/account_history.php');

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link('account.php', '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link('account_history.php', '', 'SSL'));

  require('includes/template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<div class="contentContainer">

<?php
  $orders_total = tep_count_customer_orders();

  if ($orders_total > 0) {
    $history_query_raw = "select o.orders_id, o.date_purchased, o.delivery_name, o.billing_name, ot.text as order_total, s.orders_status_name from orders o, orders_total ot, orders_status s where o.customers_id = '" . (int)$customer_id . "' and o.orders_id = ot.orders_id and ot.class = 'ot_total' and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$_SESSION['languages_id'] . "' and s.public_flag = '1' order by orders_id DESC";
    $history_split = new splitPageResults($history_query_raw, MAX_DISPLAY_ORDER_HISTORY);
    $history_query = tep_db_query($history_split->sql_query);

    while ($history = tep_db_fetch_array($history_query)) {
      $products_query = tep_db_query("select count(*) as count from orders_products where orders_id = '" . (int)$history['orders_id'] . "'");
      $products = tep_db_fetch_array($products_query);

      if (tep_not_null($history['delivery_name'])) {
        $order_type = TEXT_ORDER_SHIPPED_TO;
        $order_name = $history['delivery_name'];
      } else {
        $order_type = TEXT_ORDER_BILLED_TO;
        $order_name = $history['billing_name'];
      }
?>

  <div class="contentText">
    <div class="panel panel-info">
      <div class="panel-heading"><strong><?php echo TEXT_ORDER_NUMBER . ' ' . $history['orders_id'] . ' <span class="contentText">(' . $history['orders_status_name'] . ')</span>'; ?></strong><?php echo tep_draw_button(SMALL_IMAGE_BUTTON_VIEW, 'glyphicon glyphicon-file', tep_href_link('account_history_info.php', (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'order_id=' . $history['orders_id'], 'SSL'), 'primary', NULL, 'btn-primary btn-xs pull-right'); ?></div>
      <div class="panel-body">
        <div class="row">
          <div class="col-sm-6"><?php echo '<strong>' . TEXT_ORDER_DATE . '</strong> ' . tep_date_long($history['date_purchased']) . '<br /><strong>' . $order_type . '</strong> ' . tep_output_string_protected($order_name); ?></div>
          <br class="visible-xs" />
          <div class="col-sm-6"><?php echo '<strong>' . TEXT_ORDER_PRODUCTS . '</strong> ' . $products['count'] . '<br /><strong>' . TEXT_ORDER_COST . '</strong> ' . strip_tags($history['order_total']); ?></div>
        </div>
      </div>
    </div>
  </div>

<?php
    }
?>

  <div class="row">
    <div class="col-sm-6 pagenumber hidden-xs">
      <?php echo $history_split->display_count(TEXT_DISPLAY_NUMBER_OF_ORDERS); ?>
    </div>
    <div class="col-sm-6">
      <div class="pull-right pagenav"><?php echo $history_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></div>
      <span class="pull-right"><?php echo TEXT_RESULT_PAGE; ?></span>
    </div>
  </div>

<?php
  } else {
?>

  <div class="alert alert-info">
    <p><?php echo TEXT_NO_PURCHASES; ?></p>
  </div>

<?php
  }
?>

  <div>
    <?php echo tep_draw_button(IMAGE_BUTTON_BACK, 'glyphicon glyphicon-chevron-left', tep_href_link('account.php', '', 'SSL')); ?>
  </div>
</div>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
