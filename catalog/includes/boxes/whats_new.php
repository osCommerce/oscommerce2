<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  if ($random_product = tep_random_select("select products_id, products_image, products_tax_class_id, products_price from " . TABLE_PRODUCTS . " where products_status = '1' order by products_date_added desc limit " . MAX_RANDOM_SELECT_NEW)) {
    $random_product['products_name'] = tep_get_products_name($random_product['products_id']);
    $random_product['specials_new_products_price'] = tep_get_products_special_price($random_product['products_id']);

    if (tep_not_null($random_product['specials_new_products_price'])) {
      $whats_new_price = '<del>' . $currencies->display_price($random_product['products_price'], tep_get_tax_rate($random_product['products_tax_class_id'])) . '</del><br>';
      $whats_new_price .= '<span class="productSpecialPrice">' . $currencies->display_price($random_product['specials_new_products_price'], tep_get_tax_rate($random_product['products_tax_class_id'])) . '</span>';
    } else {
      $whats_new_price = $currencies->display_price($random_product['products_price'], tep_get_tax_rate($random_product['products_tax_class_id']));
    }
?>

<div class="ui-widget infoBoxContainer">
  <div class="ui-widget-header infoBoxHeading"><?php echo '<a href="' . tep_href_link(FILENAME_PRODUCTS_NEW) . '">' . BOX_HEADING_WHATS_NEW . '</a>'; ?></div>

  <div class="ui-widget-content infoBoxContents" style="text-align: center;">
    <?php echo '<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $random_product['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $random_product['products_image'], $random_product['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br /><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $random_product['products_id']) . '">' . $random_product['products_name'] . '</a><br />' . $whats_new_price; ?>
  </div>
</div>

<?php
  }
?>
