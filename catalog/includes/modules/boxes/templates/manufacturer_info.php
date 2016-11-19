<?php
use OSC\OM\OSCOM;
?>
<div class="panel panel-default">
  <div class="panel-heading"><?php echo OSCOM::getDef('module_boxes_manufacturer_info_box_title'); ?></div>
  <div class="panel-body"><?php echo $manufacturer_info_string; ?></div>
  <div class="panel-footer clearfix"><a href="<?php echo OSCOM::link('index.php', 'manufacturers_id=' . $Qmanufacturer->valueInt('manufacturers_id')); ?>"><?php echo OSCOM::getDef('module_boxes_manufacturer_info_box_other_products'); ?></a></div>
</div>
