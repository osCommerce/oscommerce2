<?php
use OSC\OM\OSCOM;
?>

<div class="col-sm-<?php echo $content_width; ?> gtin">
  <ul class="list-group">
    <li class="list-group-item">
      <span itemprop="gtin<?php echo MODULE_CONTENT_PRODUCT_INFO_GTIN_LENGTH; ?>" class="badge"><?php echo $gtin; ?></span>
      <?php echo OSCOM::getDef('module_content_product_info_gtin_public_title'); ?>
    </li>
  </ul>
</div>
