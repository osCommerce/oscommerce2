<?php
use OSC\OM\OSCOM;
?>
<div class="panel panel-default">
  <div class="panel-heading"><?php echo MODULE_BOXES_MANUFACTURER_INFO_BOX_TITLE; ?></div>
  <div class="panel-body"><?php echo $manufacturer_info_string; ?></div>
  <div class="panel-footer clearfix"><a href="<?php echo OSCOM::link('index.php', 'manufacturers_id=' . $Qmanufacturer->valueInt('manufacturers_id')); ?>"><?php echo MODULE_BOXES_MANUFACTURER_INFO_BOX_OTHER_PRODUCTS; ?></a></div>
</div>
