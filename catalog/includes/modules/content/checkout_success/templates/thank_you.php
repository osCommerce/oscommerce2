<?php
use OSC\OM\OSCOM;
?>
<div class="panel panel-success">
  <div class="panel-heading">
    <?php echo MODULE_CONTENT_CHECKOUT_SUCCESS_TEXT_THANKS_FOR_SHOPPING; ?>
  </div>

  <div class="panel-body">
    <p><?php echo MODULE_CONTENT_CHECKOUT_SUCCESS_TEXT_SUCCESS; ?></p>
    <p><?php echo sprintf(MODULE_CONTENT_CHECKOUT_SUCCESS_TEXT_SEE_ORDERS, OSCOM::link('account_history.php', '', 'SSL')); ?></p>
    <p><?php echo sprintf(MODULE_CONTENT_CHECKOUT_SUCCESS_TEXT_CONTACT_STORE_OWNER, OSCOM::link('contact_us.php')); ?></p>
  </div>
</div>
