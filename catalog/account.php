<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot();
    OSCOM::redirect('login.php');
  }

  $OSCOM_Language->loadDefinitions('account');

  $breadcrumb->add(OSCOM::getDef('navbar_title'), OSCOM::link('account.php'));

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?= OSCOM::getDef('heading_title'); ?></h1>
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
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
