<?php
use OSC\OM\OSCOM;
?>
<div class="panel panel-default">
  <div class="panel-heading"><?php echo OSCOM::getDef('module_boxes_information_box_title'); ?></div>
  <div class="panel-body">
    <ul class="list-unstyled">
      <li><a href="<?php echo OSCOM::link('shipping.php'); ?>"><?php echo OSCOM::getDef('module_boxes_information_box_shipping'); ?></a></li>
      <li><a href="<?php echo OSCOM::link('privacy.php'); ?>"><?php echo OSCOM::getDef('module_boxes_information_box_privacy'); ?></a></li>
      <li><a href="<?php echo OSCOM::link('conditions.php'); ?>"><?php echo OSCOM::getDef('module_boxes_information_box_conditions'); ?></a></li>
      <li><a href="<?php echo OSCOM::link('contact_us.php'); ?>"><?php echo OSCOM::getDef('module_boxes_information_box_contact'); ?></a></li>
    </ul>
  </div>
</div>
