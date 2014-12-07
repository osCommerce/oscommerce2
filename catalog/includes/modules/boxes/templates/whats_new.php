<div class="panel panel-default">
  <div class="panel-heading"><a href="<?php echo tep_href_link('products_new.php'); ?>"><?php echo MODULE_BOXES_WHATS_NEW_BOX_TITLE; ?></a></div>
  <div class="panel-body text-center">
    <?php echo '<a href="' . tep_href_link('product_info.php', 'products_id=' . $random_product['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $random_product['products_image'], $random_product['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br /><a href="' . tep_href_link('product_info.php', 'products_id=' . $random_product['products_id']) . '">' . $random_product['products_name'] . '</a><br />' . $whats_new_price . '</div>'; ?>
</div>
