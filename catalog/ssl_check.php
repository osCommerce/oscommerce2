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

  $OSCOM_Language->loadDefinitions('ssl_check');

  $breadcrumb->add(OSCOM::getDef('navbar_title'), OSCOM::link('ssl_check.php'));

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo OSCOM::getDef('heading_title'); ?></h1>
</div>

<div class="contentContainer">
  <div class="contentText">

    <div class="panel panel-danger">
      <div class="panel-heading"><?php echo OSCOM::getDef('box_information_heading'); ?></div>
      <div class="panel-body">
        <?php echo OSCOM::getDef('box_information'); ?>
      </div>
    </div>

    <div class="panel panel-danger">
      <div class="panel-body">
        <?php echo OSCOM::getDef('text_information'); ?>
      </div>
    </div>
  </div>

  <div class="buttonSet">
    <div class="text-right"><?php echo HTML::button(OSCOM::getDef('image_button_continue'), 'fa fa-angle-right', OSCOM::link('login.php')); ?></div>
  </div>
</div>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
