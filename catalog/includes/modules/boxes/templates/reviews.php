<?php
use OSC\OM\OSCOM;
?>
<div class="panel panel-default">
  <div class="panel-heading"><a href="<?php echo OSCOM::link('reviews.php'); ?>"><?php echo OSCOM::getDef('module_boxes_reviews_box_title'); ?></a></div>
  <div class="panel-body"><?php echo $reviews_box_contents; ?></div>
</div>

