<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . FILENAME_CREATE_ACCOUNT_SUCCESS);

  $breadcrumb->add(NAVBAR_TITLE_1);
  $breadcrumb->add(NAVBAR_TITLE_2);

  if (sizeof($_SESSION['navigation']->snapshot) > 0) {
    $origin_href = tep_href_link($_SESSION['navigation']->snapshot['page'], tep_array_to_string($_SESSION['navigation']->snapshot['get'], array(session_name())), $_SESSION['navigation']->snapshot['mode']);
    $_SESSION['navigation']->clear_snapshot();
  } else {
    $origin_href = tep_href_link(FILENAME_DEFAULT);
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
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

  <div><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', $origin_href, null, null, 'btn-success btn-block'); ?></div>
</div>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
