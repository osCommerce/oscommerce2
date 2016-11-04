<?php
use OSC\OM\OSCOM;
?>
<div class="panel panel-default">
  <div class="panel-heading"><?php echo OSCOM::getDef('module_boxes_product_social_bookmarks_box_title'); ?></div>
  <div class="panel-body text-center"><?php echo implode(' ', $social_bookmarks); ?></div>
</div>
