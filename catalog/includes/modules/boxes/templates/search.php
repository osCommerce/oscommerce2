<?php
use OSC\OM\OSCOM;
?>
<div class="panel panel-default">
  <div class="panel-heading"><?php echo OSCOM::getDef('module_boxes_search_box_title'); ?></div>
  <div class="panel-body text-center"><?php echo $form_output; ?></div>
  <div class="panel-footer"><?php echo OSCOM::getDef('module_boxes_search_box_text') . '<br /><a href="' . OSCOM::link('advanced_search.php') . '"><strong>' . OSCOM::getDef('module_boxes_search_box_advanced_search') . '</strong></a>'; ?></div>
</div>
