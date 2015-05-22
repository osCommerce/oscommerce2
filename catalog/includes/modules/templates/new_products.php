<?php
use OSC\OM\HTML;
use OSC\OM\OSCOM;
?>
<div class="col-sm-6 col-md-4">
  <div class="thumbnail equal-height">
    <a href="<?php echo OSCOM::link('product_info.php', 'products_id=' . $Qnew->valueInt('products_id')); ?>"><?php echo HTML::image(DIR_WS_IMAGES . $Qnew->value('products_image'), $Qnew->value('products_name'), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?></a>
    <div class="caption">
      <p class="text-center"><a href="<?php echo OSCOM::link('product_info.php', 'products_id=' . $Qnew->valueInt('products_id')); ?>"><?php echo $Qnew->value('products_name'); ?></a></p>
      <hr>
      <p class="text-center"><?php echo $currencies->display_price($Qnew->value('products_price'), tep_get_tax_rate($Qnew->value('products_tax_class_id'))); ?></p>
      <div class="text-center">
        <div class="btn-group">
          <a href="<?php echo OSCOM::link('product_info.php', tep_get_all_get_params(array('action')) . 'products_id=' . $Qnew->valueInt('products_id')); ?>" class="btn btn-default" role="button"><?php echo SMALL_IMAGE_BUTTON_VIEW; ?></a>
          <a href="<?php echo OSCOM::link($PHP_SELF, tep_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $Qnew->valueInt('products_id')); ?>" class="btn btn-success" role="button"><?php echo SMALL_IMAGE_BUTTON_BUY; ?></a>
        </div>
      </div>
    </div>
  </div>
</div>
