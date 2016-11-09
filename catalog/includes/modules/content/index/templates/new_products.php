<?php
use OSC\OM\HTML;
use OSC\OM\OSCOM;
?>
<div class="col-sm-<?php echo $content_width; ?> new-products">

  <h3><?php echo OSCOM::getDef('module_content_new_products_heading', ['current_month' => strftime('%B')]); ?></h3>

  <div class="row" itemtype="http://schema.org/ItemList">
    <meta itemprop="numberOfItems" content="<?php echo (int)$num_new_products; ?>" />
    <?php
    foreach ($new_products as $new_product) {
      ?>
    <div class="col-sm-<?php echo $product_width; ?>" itemprop="itemListElement" itemscope="" itemtype="http://schema.org/Product">
      <div class="thumbnail equal-height">
        <a href="<?php echo OSCOM::link('product_info.php', 'products_id=' . (int)$new_product['products_id']); ?>"><?php echo HTML::image(OSCOM::linkImage($new_product['products_image']), $new_product['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'itemprop="image"'); ?></a>
        <div class="caption">
          <p class="text-center"><a itemprop="url" href="<?php echo OSCOM::link('product_info.php', 'products_id=' . (int)$new_product['products_id']); ?>"><span itemprop="name"><?php echo $new_product['products_name']; ?></span></a></p>
          <hr>
          <p class="text-center" itemprop="offers" itemscope itemtype="http://schema.org/Offer"><meta itemprop="priceCurrency" content="<?php echo HTML::output($_SESSION['currency']); ?>" /><span itemprop="price" content="<?php echo $currencies->display_raw($new_product['products_price'], tep_get_tax_rate($new_product['products_tax_class_id'])); ?>"><?php echo $currencies->display_price($new_product['products_price'], tep_get_tax_rate($new_product['products_tax_class_id'])); ?></span></p>
          <div class="text-center">
            <div class="btn-group">
              <a href="<?php echo OSCOM::link('product_info.php', tep_get_all_get_params(array('action')) . 'products_id=' . (int)$new_product['products_id']); ?>" class="btn btn-default" role="button"><?php echo OSCOM::getDef('module_content_new_products_button_view'); ?></a>
              <a href="<?php echo OSCOM::link($PHP_SELF, tep_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . (int)$new_product['products_id']); ?>" class="btn btn-success" role="button"><?php echo OSCOM::getDef('module_content_new_products_button_buy'); ?></a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php
  }
  ?>
  </div>

</div>
