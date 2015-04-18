<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  require('includes/application_top.php');

// if the customer is not logged on, redirect them to the shopping cart page
  if (!isset($_SESSION['customer_id'])) {
    tep_redirect(OSCOM::link('shopping_cart.php'));
  }

  $Qorders = $OSCOM_Db->prepare('select orders_id from :table_orders where customers_id = :customers_id order by date_purchased desc limit 1');
  $Qorders->bindInt(':customers_id', $_SESSION['customer_id']);
  $Qorders->execute();

// redirect to shopping cart page if no orders exist
  if ($Qorders->fetch() === false) {
    tep_redirect(OSCOM::link('shopping_cart.php'));
  }

  $orders = $Qorders->toArray(); // TODO replace $orders used in template content modules with $Qorders

  $order_id = $orders['orders_id'];

  $page_content = $oscTemplate->getContent('checkout_success');

  if ( isset($_GET['action']) && ($_GET['action'] == 'update') ) {
    tep_redirect(OSCOM::link('index.php'));
  }

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/checkout_success.php');

  $breadcrumb->add(NAVBAR_TITLE_1);
  $breadcrumb->add(NAVBAR_TITLE_2);

  require('includes/template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php echo tep_draw_form('order', OSCOM::link('checkout_success.php', 'action=update', 'SSL'), 'post', 'class="form-horizontal" role="form"'); ?>

<div class="contentContainer">
  <?php echo $page_content; ?>
</div>

<div><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', null, 'primary', null, 'btn-success btn-block'); ?></div>

</form>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
