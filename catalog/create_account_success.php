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

  $OSCOM_Language->loadDefinitions('create_account_success');

  $breadcrumb->add(OSCOM::getDef('navbar_title_1'));
  $breadcrumb->add(OSCOM::getDef('navbar_title_2'));

  if (sizeof($_SESSION['navigation']->snapshot) > 0) {
    $origin_href = OSCOM::link($_SESSION['navigation']->snapshot['page'], tep_array_to_string($_SESSION['navigation']->snapshot['get'], array(session_name())));
    $_SESSION['navigation']->clear_snapshot();
  } else {
    $origin_href = OSCOM::link('index.php');
  }

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo OSCOM::getDef('heading_title'); ?></h1>
</div>

<div class="contentContainer">
  <div class="contentText">
    <div class="alert alert-success">
      <?php echo OSCOM::getDef('text_account_created', ['contact_us_link' => OSCOM::link('contact_us.php')]); ?>
    </div>
  </div>

  <div class="buttonSet">
    <div class="text-right"><?php echo HTML::button(OSCOM::getDef('image_button_continue'), 'fa fa-angle-right', $origin_href, null, 'btn-success'); ?></div>
  </div>
</div>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
