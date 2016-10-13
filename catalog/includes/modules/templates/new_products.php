<?php
use OSC\OM\HTML;
use OSC\OM\OSCOM;
?>
<div class="col-sm-6 col-md-4" itemprop="itemListElement" itemscope="" itemtype="http://schema.org/Product">
  <div class="thumbnail equal-height">
    <a href="<?php echo OSCOM::link('product_info.php', 'products_id=' . $Qnew->valueInt('products_id')); ?>"><?php echo HTML::image(OSCOM::linkImage($Qnew->value('products_image')), $Qnew->value('products_name'), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'itemprop="image"'); ?></a>
    <div class="caption">
      <p class="text-center"><a itemprop="url" href="<?php echo OSCOM::link('product_info.php', 'products_id=' . $Qnew->valueInt('products_id')); ?>"><span itemprop="name"><?php echo $Qnew->value('products_name'); ?></span></a></p>
      <hr>
      <p class="text-center" itemprop="offers" itemscope itemtype="http://schema.org/Offer"><span itemprop="price" content="<?php echo $currencies->display_raw($Qnew->value('products_price'), tep_get_tax_rate($Qnew->value('products_tax_class_id')));; ?>"><?php echo $currencies->display_price($Qnew->value('products_price'), tep_get_tax_rate($Qnew->value('products_tax_class_id'))); ?></span></p>
      <div class="text-center">
        <div class="btn-group">
          <a href="<?php echo OSCOM::link('product_info.php', tep_get_all_get_params(array('action')) . 'products_id=' . $Qnew->valueInt('products_id')); ?>" class="btn btn-default" role="button"><?php echo SMALL_IMAGE_BUTTON_VIEW; ?></a>
          <a href="<?php echo OSCOM::link($PHP_SELF, tep_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $Qnew->valueInt('products_id')); ?>" class="btn btn-success" role="button"><?php echo SMALL_IMAGE_BUTTON_BUY; ?></a>
        </div>
      </div>
    </div>
  </div>
</div>
