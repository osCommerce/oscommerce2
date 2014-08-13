<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $listing_split = new splitPageResults($listing_sql, MAX_DISPLAY_SEARCH_RESULTS, 'p.products_id');
?>

  <div class="contentText">

<?php
  if ( ($listing_split->number_of_rows > 0) && ( (PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3') ) ) {
?>

    <div>
      <span style="float: right;"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></span>

      <span><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></span>
    </div>

    <br />

<?php
  }
  $prod_list_contents = '';
?>
  <div class="ui-widget infoBoxContainer">
    <div class="ui-widget-header ui-corner-top infoBoxHeading">
      <table border="0" width="100%" cellspacing="0" cellpadding="2" class="productListingHeader">
        <tr>
<?php
  foreach (array_keys($column_list) as $col) {
    $lc_align = '';

    switch ($column_list[$col]) {
      case 'PRODUCT_LIST_MODEL':
        $lc_text = TABLE_HEADING_MODEL;
        $lc_align = '';
        break;
      case 'PRODUCT_LIST_NAME':
        $lc_text = TABLE_HEADING_PRODUCTS;
        $lc_align = '';
        break;
      case 'PRODUCT_LIST_MANUFACTURER':
        $lc_text = TABLE_HEADING_MANUFACTURER;
        $lc_align = '';
        break;
      case 'PRODUCT_LIST_PRICE':
        $lc_text = TABLE_HEADING_PRICE;
        $lc_align = 'right';
        break;
      case 'PRODUCT_LIST_QUANTITY':
        $lc_text = TABLE_HEADING_QUANTITY;
        $lc_align = 'right';
        break;
      case 'PRODUCT_LIST_WEIGHT':
        $lc_text = TABLE_HEADING_WEIGHT;
        $lc_align = 'right';
        break;
      case 'PRODUCT_LIST_IMAGE':
        $lc_text = TABLE_HEADING_IMAGE;
        $lc_align = 'center';
        break;
      case 'PRODUCT_LIST_BUY_NOW':
        $lc_text = TABLE_HEADING_BUY_NOW;
        $lc_align = 'center';
        break;
    }

    if ( ($column_list[$col] != 'PRODUCT_LIST_BUY_NOW') && ($column_list[$col] != 'PRODUCT_LIST_IMAGE') ) {
      $lc_text = tep_create_sort_heading($_GET['sort'], $col+1, $lc_text);
    }
?>
          <td<?php echo (tep_not_null($lc_align) ? ' align="' . $lc_align . '"' : '') . '>' . $lc_text; ?></td>
<?php } ?>
        </tr>
      </table>
    </div>
<?php
  if ($listing_split->number_of_rows > 0) {
    $rows = 0;
    $listing_query = tep_db_query($listing_split->sql_query);
?>
      <div class="ui-widget-content ui-corner-bottom productListTable">
        <table border="0" width="100%" cellspacing="0" cellpadding="2" class="productListingData">
<?php
    while ($listing = tep_db_fetch_array($listing_query)) {
      $rows++;
?>
            <tr>
<?php
      foreach (array_keys($column_list) as $col) {
        switch ($column_list[$col]) {
          case 'PRODUCT_LIST_MODEL': ?>
            <td><?php echo $listing['products_model']; ?></td>
          <?php  break;  
          case 'PRODUCT_LIST_NAME':
            if (isset($_GET['manufacturers_id']) && tep_not_null($_GET['manufacturers_id'])) { ?>
                      <td><a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'manufacturers_id=' . $_GET['manufacturers_id'] . '&products_id=' . $listing['products_id']) . '">' . $listing['products_name']; ?></a></td>
            <?php } else { ?>
                      <td><a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, ($cPath ? 'cPath=' . $cPath . '&' : '') . 'products_id=' . $listing['products_id']) . '">' . $listing['products_name']; ?></a></td>
            <?php }
            break;
          case 'PRODUCT_LIST_MANUFACTURER': ?>
                    <td><a href="<?php echo tep_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $listing['manufacturers_id']) . '">' . $listing['manufacturers_name']; ?></a></td>
            <?php break;
          case 'PRODUCT_LIST_PRICE': 
            if (tep_not_null($listing['specials_new_products_price'])) { ?>
                      <td align="right"><del><?php echo  $currencies->display_price($listing['products_price'], tep_get_tax_rate($listing['products_tax_class_id'])) . '</del>&nbsp;&nbsp;<span class="productSpecialPrice">' . $currencies->display_price($listing['specials_new_products_price'], tep_get_tax_rate($listing['products_tax_class_id'])); ?></span></td>
            <?php } else { ?>
                      <td align="right"><?php echo $currencies->display_price($listing['products_price'], tep_get_tax_rate($listing['products_tax_class_id'])); ?></td>
            <?php } 
            break;
          case 'PRODUCT_LIST_QUANTITY': ?>
                    <td align="right"><?php echo $listing['products_quantity']; ?></td>
          <?php  break;
          case 'PRODUCT_LIST_WEIGHT': ?>
                    <td align="right"><?php echo $listing['products_weight']; ?></td>
          <?php  break; 
          case 'PRODUCT_LIST_IMAGE': 
            if (isset($_GET['manufacturers_id'])  && tep_not_null($_GET['manufacturers_id'])) { ?>
                      <td align="center"><a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'manufacturers_id=' . $_GET['manufacturers_id'] . '&products_id=' . $listing['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $listing['products_image'], $listing['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?></a></td>
            <?php } else { ?>
                      <td align="center"><a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, ($cPath ? 'cPath=' . $cPath . '&' : '') . 'products_id=' . $listing['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $listing['products_image'], $listing['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?></a></td>
            <?php } 
            break;
          case 'PRODUCT_LIST_BUY_NOW': ?>
                    <td align="center"><?php echo tep_draw_button(IMAGE_BUTTON_BUY_NOW, 'glyphicon glyphicon-shopping-cart', tep_href_link($PHP_SELF, tep_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $listing['products_id'])); ?></td>
            <?php break;
        }
      }
?>
           </tr>
 <?php   } ?>

        </table>
      </div>
    </div>

<?php  
  } else {
?>

    <p><?php echo TEXT_NO_PRODUCTS; ?></p>

<?php
  }

  if ( ($listing_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3')) ) {
?>

    <br />

    <div>
      <span style="float: right;"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></span>

      <span><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></span>
    </div>

<?php
  }
?>

  </div>
