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

// if the customer is not logged on, redirect them to the shopping cart page
  if (!isset($_SESSION['customer_id'])) {
    OSCOM::redirect('shopping_cart.php');
  }

  $Qorders = $OSCOM_Db->prepare('select orders_id from :table_orders where customers_id = :customers_id order by date_purchased desc limit 1');
  $Qorders->bindInt(':customers_id', $_SESSION['customer_id']);
  $Qorders->execute();

// redirect to shopping cart page if no orders exist
  if ($Qorders->fetch() === false) {
    OSCOM::redirect('shopping_cart.php');
  }

  $orders = $Qorders->toArray(); // TODO replace $orders used in template content modules with $Qorders

  $order_id = $orders['orders_id'];

  $page_content = $oscTemplate->getContent('checkout_success');

  if ( isset($_GET['action']) && ($_GET['action'] == 'update') ) {
    OSCOM::redirect('index.php');
  }

  require('includes/languages/' . $_SESSION['language'] . '/checkout_success.php');

  $breadcrumb->add(NAVBAR_TITLE_1);
  $breadcrumb->add(NAVBAR_TITLE_2);

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php echo HTML::form('order', OSCOM::link('checkout_success.php', 'action=update', 'SSL'), 'post', 'class="form-horizontal" role="form"'); ?>

<div class="contentContainer">
  <?php echo $page_content; ?>
</div>

<div class="contentContainer">
  <div class="buttonSet">
    <div class="text-right"><?php echo HTML::button(IMAGE_BUTTON_CONTINUE, 'fa fa-angle-right', null, null, 'btn-success'); ?></div>
  </div>
</div>

</form>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
