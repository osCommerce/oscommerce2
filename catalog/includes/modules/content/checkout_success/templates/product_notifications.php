<?php
use OSC\OM\OSCOM;
?>
<div class="panel panel-success">
  <div class="panel-heading"><?php echo OSCOM::getDef('module_content_checkout_success_product_notifications_text_notify_products'); ?></div>
  <div class="panel-body">
    <p class="productsNotifications">
      <?php echo $products_notifications; ?>
    </p>
  </div>
</div>
