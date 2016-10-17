<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot();
    OSCOM::redirect('login.php', '', 'SSL');
  }

  require('includes/languages/' . $_SESSION['language'] . '/account_history.php');

  $breadcrumb->add(NAVBAR_TITLE_1, OSCOM::link('account.php', '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, OSCOM::link('account_history.php', '', 'SSL'));

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<div class="contentContainer">

<?php
  $Qorders = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS o.orders_id, o.date_purchased, o.delivery_name, o.billing_name, ot.text as order_total, s.orders_status_name from :table_orders o, :table_orders_total ot, :table_orders_status s where o.customers_id = :customers_id and o.orders_id = ot.orders_id and ot.class = "ot_total" and o.orders_status = s.orders_status_id and s.language_id = :language_id and s.public_flag = "1" order by o.orders_id desc limit :page_set_offset, :page_set_max_results');
  $Qorders->bindInt(':customers_id', $_SESSION['customer_id']);
  $Qorders->bindInt(':language_id', $_SESSION['languages_id']);
  $Qorders->setPageSet(MAX_DISPLAY_ORDER_HISTORY);
  $Qorders->execute();

  if ($Qorders->getPageSetTotalRows() > 0) {
    foreach ($Qorders->fetchAll() as $order) {
      $Qproducts = $OSCOM_Db->prepare('select count(*) as count from :table_orders_products where orders_id = :orders_id');
      $Qproducts->bindInt(':orders_id', $order['orders_id']);
      $Qproducts->execute();

      if (tep_not_null($order['delivery_name'])) {
        $order_type = TEXT_ORDER_SHIPPED_TO;
        $order_name = $order['delivery_name'];
      } else {
        $order_type = TEXT_ORDER_BILLED_TO;
        $order_name = $order['billing_name'];
      }
?>

  <div class="contentText">
    <div class="panel panel-info">
      <div class="panel-heading"><strong><?php echo TEXT_ORDER_NUMBER . ' ' . (int)$order['orders_id'] . ' <span class="contentText">(' . HTML::outputProtected($order['orders_status_name']) . ')</span>'; ?></strong></div>
      <div class="panel-body">
        <div class="row">
          <div class="col-sm-6"><?php echo '<strong>' . TEXT_ORDER_DATE . '</strong> ' . tep_date_long($order['date_purchased']) . '<br /><strong>' . $order_type . '</strong> ' . HTML::outputProtected($order_name); ?></div>
          <br class="visible-xs" />
          <div class="col-sm-6"><?php echo '<strong>' . TEXT_ORDER_PRODUCTS . '</strong> ' . $Qproducts->valueInt('count') . '<br /><strong>' . TEXT_ORDER_COST . '</strong> ' . strip_tags($order['order_total']); ?></div>
        </div>
      </div>
      <div class="panel-footer"><?php echo HTML::button(SMALL_IMAGE_BUTTON_VIEW, 'fa fa-file', OSCOM::link('account_history_info.php', (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'order_id=' . $order['orders_id'], 'SSL'), 'primary', NULL, 'btn-primary btn-xs'); ?></div>
    </div>
  </div>

<?php
    }
?>
  <div class="row">
    <div class="col-md-6 pagenumber"><?php echo $Qorders->getPageSetLabel(TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></div>
    <div class="col-md-6"><span class="pull-right pagenav"><ul class="pagination"><?php echo $Qorders->getPageSetLinks(tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></ul></span><span class="pull-right"><?php echo TEXT_RESULT_PAGE; ?></span></div>
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

  <div class="buttonSet">
    <?php echo HTML::button(IMAGE_BUTTON_BACK, 'fa fa-angle-left', OSCOM::link('account.php', '', 'SSL')); ?>
  </div>
</div>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
