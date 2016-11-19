<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
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

  $OSCOM_Language->loadDefinitions('checkout_success');

  $breadcrumb->add(OSCOM::getDef('navbar_title_1'));
  $breadcrumb->add(OSCOM::getDef('navbar_title_2'));

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo OSCOM::getDef('heading_title'); ?></h1>
</div>

<?php echo HTML::form('order', OSCOM::link('checkout_success.php', 'action=update'), 'post', 'class="form-horizontal" role="form"'); ?>

<div class="contentContainer">
  <?php echo $page_content; ?>
</div>

<div class="contentContainer">
  <div class="buttonSet">
    <div class="text-right"><?php echo HTML::button(OSCOM::getDef('image_button_continue'), 'fa fa-angle-right', null, null, 'btn-success'); ?></div>
  </div>
</div>

</form>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
