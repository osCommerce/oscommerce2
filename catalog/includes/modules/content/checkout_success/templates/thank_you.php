<?php
use OSC\OM\OSCOM;
?>
<div class="panel panel-success">
  <div class="panel-heading">
    <?php echo OSCOM::getDef('module_content_checkout_success_text_thanks_for_shopping'); ?>
  </div>

  <div class="panel-body">
    <p><?php echo OSCOM::getDef('module_content_checkout_success_text_success'); ?></p>
    <p><?php echo sprintf(OSCOM::getDef('module_content_checkout_success_text_see_orders'), OSCOM::link('account_history.php')); ?></p>
    <p><?php echo sprintf(OSCOM::getDef('module_content_checkout_success_text_contact_store_owner'), OSCOM::link('contact_us.php')); ?></p>
  </div>
</div>
