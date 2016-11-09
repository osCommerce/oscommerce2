<?php
use OSC\OM\OSCOM;

?>
<div class="panel panel-success">
  <div class="panel-heading">
    <?php echo OSCOM::getDef('module_content_checkout_success_text_thanks_for_shopping'); ?>
  </div>

  <div class="panel-body">
    <p><?php echo OSCOM::getDef('module_content_checkout_success_text_success'); ?></p>
    <p><?php echo OSCOM::getDef('module_content_checkout_success_text_see_orders', ['account_history_link' => OSCOM::link('account_history.php')]); ?></p>
    <p><?php echo OSCOM::getDef('module_content_checkout_success_text_contact_store_owner', ['contact_us_link' =>  OSCOM::link('contact_us.php')]); ?></p>
  </div>
</div>
