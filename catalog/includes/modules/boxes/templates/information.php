<?php
use OSC\OM\OSCOM;
?>
<div class="panel panel-default">
  <div class="panel-heading"><?php echo MODULE_BOXES_INFORMATION_BOX_TITLE; ?></div>
  <ul class="nav nav-pills nav-stacked">
    <li><a href="<?php echo OSCOM::link('shipping.php'); ?>"><?php echo MODULE_BOXES_INFORMATION_BOX_SHIPPING; ?></a></li>
    <li><a href="<?php echo OSCOM::link('privacy.php'); ?>"><?php echo MODULE_BOXES_INFORMATION_BOX_PRIVACY; ?></a></li>
    <li><a href="<?php echo OSCOM::link('conditions.php'); ?>"><?php echo MODULE_BOXES_INFORMATION_BOX_CONDITIONS; ?></a></li>
    <li><a href="<?php echo OSCOM::link('contact_us.php'); ?>"><?php echo MODULE_BOXES_INFORMATION_BOX_CONTACT; ?></a></li>
  </ul>
</div>
