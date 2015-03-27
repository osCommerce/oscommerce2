<div class="col-sm-6 col-md-4">
  <div class="thumbnail equal-height">
    <a href="<?php echo tep_href_link('product_info.php', 'products_id=' . (int)$new_products['products_id']); ?>"><?php echo tep_image(DIR_WS_IMAGES . $new_products['products_image'], $new_products['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?></a>
    <div class="caption">
      <p class="text-center"><a href="<?php echo tep_href_link('product_info.php', 'products_id=' . (int)$new_products['products_id']); ?>"><?php echo $new_products['products_name']; ?></a></p>
      <hr>
      <p class="text-center"><?php echo $currencies->display_price($new_products['products_price'], tep_get_tax_rate($new_products['products_tax_class_id'])); ?></p>
      <div class="text-center">
        <div class="btn-group">
          <a href="<?php echo tep_href_link('product_info.php', tep_get_all_get_params(array('action')) . 'products_id=' . (int)$new_products['products_id']); ?>" class="btn btn-default" role="button"><?php echo SMALL_IMAGE_BUTTON_VIEW; ?></a>
          <a href="<?php echo tep_href_link($PHP_SELF, tep_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . (int)$new_products['products_id']); ?>" class="btn btn-success" role="button"><?php echo SMALL_IMAGE_BUTTON_BUY; ?></a>
        </div>
      </div>
    </div>
  </div>
</div>

