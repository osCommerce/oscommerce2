<?php
use OSC\OM\HTML;
use OSC\OM\OSCOM;
?>
<div class="panel panel-default">
  <div class="panel-heading"><a href="<?php echo OSCOM::link('products_new.php'); ?>"><?php echo OSCOM::getDef('module_boxes_whats_new_box_title'); ?></a></div>
  <div class="panel-body text-center">
    <?php echo '<a href="' . OSCOM::link('product_info.php', 'products_id=' . (int)$random_product['products_id']) . '">' . HTML::image(OSCOM::linkImage($random_product['products_image']), $random_product['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br /><a href="' . OSCOM::link('product_info.php', 'products_id=' . (int)$random_product['products_id']) . '">' . $random_product['products_name'] . '</a><br />' . $whats_new_price . '</div>'; ?>
</div>
