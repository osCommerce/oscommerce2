<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\DateTime;
  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot();
    OSCOM::redirect('login.php');
  }

  $OSCOM_Language->loadDefinitions('account_history');

  $breadcrumb->add(OSCOM::getDef('navbar_title_1'), OSCOM::link('account.php'));
  $breadcrumb->add(OSCOM::getDef('navbar_title_2'), OSCOM::link('account_history.php'));

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo OSCOM::getDef('heading_title'); ?></h1>
</div>

<div class="contentContainer">

<?php
  $Qorders = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS o.orders_id, o.date_purchased, o.delivery_name, o.billing_name, ot.text as order_total, s.orders_status_name from :table_orders o, :table_orders_total ot, :table_orders_status s where o.customers_id = :customers_id and o.orders_id = ot.orders_id and ot.class = "ot_total" and o.orders_status = s.orders_status_id and s.language_id = :language_id and s.public_flag = "1" order by o.orders_id desc limit :page_set_offset, :page_set_max_results');
  $Qorders->bindInt(':customers_id', $_SESSION['customer_id']);
  $Qorders->bindInt(':language_id', $OSCOM_Language->getId());
  $Qorders->setPageSet(MAX_DISPLAY_ORDER_HISTORY);
  $Qorders->execute();

  if ($Qorders->getPageSetTotalRows() > 0) {
    foreach ($Qorders->fetchAll() as $order) {
      $Qproducts = $OSCOM_Db->prepare('select count(*) as count from :table_orders_products where orders_id = :orders_id');
      $Qproducts->bindInt(':orders_id', $order['orders_id']);
      $Qproducts->execute();

      if (tep_not_null($order['delivery_name'])) {
        $order_type = OSCOM::getDef('text_order_shipped_to');
        $order_name = $order['delivery_name'];
      } else {
        $order_type = OSCOM::getDef('text_order_billed_to');
        $order_name = $order['billing_name'];
      }
?>

  <div class="contentText">
    <div class="panel panel-info">
      <div class="panel-heading"><strong><?php echo OSCOM::getDef('text_order_number') . ' ' . (int)$order['orders_id'] . ' <span class="contentText">(' . HTML::outputProtected($order['orders_status_name']) . ')</span>'; ?></strong></div>
      <div class="panel-body">
        <div class="row">
          <div class="col-sm-6"><?php echo '<strong>' . OSCOM::getDef('text_order_date') . '</strong> ' . DateTime::toLong($order['date_purchased']) . '<br /><strong>' . $order_type . '</strong> ' . HTML::outputProtected($order_name); ?></div>
          <br class="visible-xs" />
          <div class="col-sm-6"><?php echo '<strong>' . OSCOM::getDef('text_order_products') . '</strong> ' . $Qproducts->valueInt('count') . '<br /><strong>' . OSCOM::getDef('text_order_cost') . '</strong> ' . strip_tags($order['order_total']); ?></div>
        </div>
      </div>
      <div class="panel-footer"><?php echo HTML::button(OSCOM::getDef('small_image_button_view'), 'fa fa-file', OSCOM::link('account_history_info.php', (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'order_id=' . $order['orders_id']), null, 'btn-primary btn-xs'); ?></div>
    </div>
  </div>

<?php
    }
?>
  <div class="row">
    <div class="col-md-6 pagenumber"><?php echo $Qorders->getPageSetLabel(OSCOM::getDef('text_display_number_of_orders')); ?></div>
    <div class="col-md-6"><span class="pull-right pagenav"><?php echo $Qorders->getPageSetLinks(tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></span><span class="pull-right"><?php echo OSCOM::getDef('text_result_page'); ?></span></div>
  </div>

<?php
  } else {
?>

  <div class="alert alert-info">
    <p><?php echo OSCOM::getDef('text_no_purchases'); ?></p>
  </div>

<?php
  }
?>

  <div class="buttonSet">
    <?php echo HTML::button(OSCOM::getDef('image_button_back'), 'fa fa-angle-left', OSCOM::link('account.php')); ?>
  </div>
</div>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
