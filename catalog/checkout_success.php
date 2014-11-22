<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

// if the customer is not logged on, redirect them to the shopping cart page
  if (!isset($_SESSION['customer_id'])) {
    tep_redirect(tep_href_link('shopping_cart.php'));
  }

  $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where customers_id = '" . (int)$customer_id . "' order by date_purchased desc limit 1");

// redirect to shopping cart page if no orders exist
  if ( !tep_db_num_rows($orders_query) ) {
    tep_redirect(tep_href_link('shopping_cart.php'));
  }

  $orders = tep_db_fetch_array($orders_query);

  $order_id = $orders['orders_id'];

  $page_content = $oscTemplate->getContent('checkout_success');

  if ( isset($_GET['action']) && ($_GET['action'] == 'update') ) {
    tep_redirect(tep_href_link('index.php'));
  }

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/checkout_success.php');

  $breadcrumb->add(NAVBAR_TITLE_1);
  $breadcrumb->add(NAVBAR_TITLE_2);

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php echo tep_draw_form('order', tep_href_link('checkout_success.php', 'action=update', 'SSL'), 'post', 'class="form-horizontal" role="form"'); ?>

<div class="contentContainer">
  <?php echo $page_content; ?>
</div>

<div><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', null, 'primary', null, 'btn-success btn-block'); ?></div>

</form>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
