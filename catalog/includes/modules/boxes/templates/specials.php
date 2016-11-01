<?php
use OSC\OM\HTML;
use OSC\OM\OSCOM;
?>
<div class="panel panel-default">
  <div class="panel-heading">
    <?php echo '<a href="' . OSCOM::link('specials.php') . '">' . OSCOM::getDef('module_boxes_specials_box_title') . '</a>'; ?>
  </div>
  <div class="panel-body text-center">
    <?php echo '<a href="' . OSCOM::link('product_info.php', 'products_id=' . (int)$random_product['products_id']) . '">' . HTML::image(OSCOM::linkImage($random_product['products_image']), $random_product['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br /><a href="' . OSCOM::link('product_info.php', 'products_id=' . (int)$random_product['products_id']) . '">' . $random_product['products_name'] . '</a><br /><del>' . $currencies->display_price($random_product['products_price'], tep_get_tax_rate($random_product['products_tax_class_id'])) . '</del><br /><span class="productSpecialPrice">' . $currencies->display_price($random_product['specials_new_products_price'], tep_get_tax_rate($random_product['products_tax_class_id'])) . '</span>'; ?>
  </div>
</div>
