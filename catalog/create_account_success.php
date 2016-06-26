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

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/create_account_success.php');

  $breadcrumb->add(NAVBAR_TITLE_1);
  $breadcrumb->add(NAVBAR_TITLE_2);

  if (sizeof($_SESSION['navigation']->snapshot) > 0) {
    $origin_href = OSCOM::link($_SESSION['navigation']->snapshot['page'], tep_array_to_string($_SESSION['navigation']->snapshot['get'], array(session_name())), $_SESSION['navigation']->snapshot['mode']);
    $_SESSION['navigation']->clear_snapshot();
  } else {
    $origin_href = OSCOM::link('index.php');
  }

  require('includes/template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<div class="contentContainer">
  <div class="contentText">
    <div class="alert alert-success">
      <?php echo TEXT_ACCOUNT_CREATED; ?>
    </div>
  </div>

  <div class="buttonSet">
    <div class="text-right"><?php echo HTML::button(IMAGE_BUTTON_CONTINUE, 'fa fa-angle-right', $origin_href, null, null, 'btn-success'); ?></div>
  </div>
</div>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
