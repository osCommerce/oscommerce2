<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  require('includes/application_top.php');

  $OSCOM_Language->loadDefinitions('logoff');

  $breadcrumb->add(OSCOM::getDef('navbar_title'));

  unset($_SESSION['customer_id']);
  unset($_SESSION['customer_default_address_id']);
  unset($_SESSION['customer_first_name']);
  unset($_SESSION['customer_country_id']);
  unset($_SESSION['customer_zone_id']);

  if ( isset($_SESSION['sendto']) ) {
    unset($_SESSION['sendto']);
  }

  if ( isset($_SESSION['billto']) ) {
    unset($_SESSION['billto']);
  }

  if ( isset($_SESSION['shipping']) ) {
    unset($_SESSION['shipping']);
  }

  if ( isset($_SESSION['payment']) ) {
    unset($_SESSION['payment']);
  }

  if ( isset($_SESSION['comments']) ) {
    unset($_SESSION['comments']);
  }

  $_SESSION['cart']->reset();

  Registry::get('Hooks')->call('Account', 'Logout');

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo OSCOM::getDef('heading_title'); ?></h1>
</div>

<div class="contentContainer">
  <div class="contentText">
    <div class="alert alert-danger">
      <?php echo OSCOM::getDef('text_main'); ?>
    </div>
  </div>

  <div class="buttonSet">
    <div class="text-right"><?php echo HTML::button(OSCOM::getDef('image_button_continue'), 'fa fa-angle-right', OSCOM::link('index.php'), null, 'btn-danger'); ?></div>
  </div>
</div>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
