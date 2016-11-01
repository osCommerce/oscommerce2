<?php
use OSC\OM\OSCOM;
?>
<div class="panel panel-default" itemscope itemtype="http://schema.org/ItemList">
  <div class="panel-heading" itemprop="name"><?php echo OSCOM::getDef('module_boxes_best_sellers_box_title'); ?></div>
  <div class="panel-body">
    <meta itemprop="itemListOrder" content="http://schema.org/ItemListOrderDescending" />
    <meta itemprop="numberOfItems" content="<?php echo (int)$num_best_sellers; ?>" />
    <ol style="margin: 0; padding-left: 25px;">
      <?php echo $bestsellers_list; ?>
    </ol>
  </div>
</div>
