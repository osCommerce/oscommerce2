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

  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot();
    OSCOM::redirect('index.php', 'Account&LogIn', 'SSL');
  }

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/account.php');

  $breadcrumb->add(NAVBAR_TITLE, OSCOM::link('account.php', '', 'SSL'));

  require('includes/template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php
  if ($messageStack->size('account') > 0) {
    echo $messageStack->output('account');
  }
?>

<div class="contentContainer">
  <div class="row">

    <?php
    echo $oscTemplate->getContent('account');
    ?>
  
  </div>
</div>


<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
