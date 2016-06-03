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

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/ssl_check.php');

  $breadcrumb->add(NAVBAR_TITLE, OSCOM::link('ssl_check.php'));

  require('includes/template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<div class="contentContainer">
  <div class="contentText">

    <div class="panel panel-danger">
      <div class="panel-heading"><?php echo BOX_INFORMATION_HEADING; ?></div>
      <div class="panel-body">
        <?php echo BOX_INFORMATION; ?>
      </div>
    </div>

    <div class="panel panel-danger">
      <div class="panel-body">
        <?php echo TEXT_INFORMATION; ?>
      </div>
    </div>
  </div>

  <div class="buttonSet">
    <div class="text-right"><?php echo HTML::button(IMAGE_BUTTON_CONTINUE, 'fa fa-angle-right', OSCOM::link('index.php', 'Account&LogIn')); ?></div>
  </div>
</div>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
