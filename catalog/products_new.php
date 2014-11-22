<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/products_new.php');

  $breadcrumb->add(NAVBAR_TITLE, tep_href_link('products_new.php'));

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<div class="contentContainer">
  <div class="contentText">

<?php
  $products_new_array = array();

  $products_new_query_raw = "select p.products_id, pd.products_name, p.products_image, p.products_price, p.products_tax_class_id, p.products_date_added, m.manufacturers_name from " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m on (p.manufacturers_id = m.manufacturers_id), " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by p.products_date_added DESC, pd.products_name";
  $products_new_split = new splitPageResults($products_new_query_raw, MAX_DISPLAY_PRODUCTS_NEW);

  if (($products_new_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>

    <div>
      <span style="float: right;"><?php echo TEXT_RESULT_PAGE . ' ' . $products_new_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></span>

      <span><?php echo $products_new_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS_NEW); ?></span>
    </div>

    <br />

<?php
  }
?>

<?php
  if ($products_new_split->number_of_rows > 0) {
?>


    <div class="row">

<?php
    $products_new_query = tep_db_query($products_new_split->sql_query);
    while ($products_new = tep_db_fetch_array($products_new_query)) {
      if ($new_price = tep_get_products_special_price($products_new['products_id'])) {
        $products_price = '<del>' . $currencies->display_price($products_new['products_price'], tep_get_tax_rate($products_new['products_tax_class_id'])) . '</del> <span class="productSpecialPrice">' . $currencies->display_price($new_price, tep_get_tax_rate($products_new['products_tax_class_id'])) . '</span>';
      } else {
        $products_price = $currencies->display_price($products_new['products_price'], tep_get_tax_rate($products_new['products_tax_class_id']));
      }
?>
      <div class="col-sm-6">
        <div class="well well-sm">
           <div class="row">
              <div class="col-xs-3 col-md-3 text-center"><?php echo '<a href="' . tep_href_link('product_info.php', 'products_id=' . $products_new['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $products_new['products_image'], $products_new['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a>'; ?></div>
              <div class="col-xs-9 col-md-9 info-box">
                <h4><?php echo '<a href="' . tep_href_link('product_info.php', 'products_id=' . $products_new['products_id']) . '">' . $products_new['products_name'] . '</a>'; ?></h4>
                <p><?php echo TEXT_DATE_ADDED . ' ' . tep_date_long($products_new['products_date_added']) . '<br />' . TEXT_MANUFACTURER . ' ' . $products_new['manufacturers_name']; ?></p>
                <hr />
                <div class="row">
                  <div class="col-sm-6">
                    <?php echo TEXT_PRICE . ' ' . $products_price; ?>
                  </div>
                  <div class="col-sm-6 text-right">
                    <?php echo tep_draw_button(IMAGE_BUTTON_IN_CART, 'glyphicon glyphicon-shopping-cart', tep_href_link('products_new.php', tep_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $products_new['products_id']), null, null, 'btn-success'); ?>
                  </div>
                </div>
             </div>
          </div>
        </div>
      </div>
<?php
    }
?>

    </div>

<?php
  } else {
?>

    <div class="alert alert-warning">
      <?php echo TEXT_NO_NEW_PRODUCTS; ?>
    </div>

<?php
  }

  if (($products_new_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>

    <br />

    <div>
      <span style="float: right;"><?php echo TEXT_RESULT_PAGE . ' ' . $products_new_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></span>

      <span><?php echo $products_new_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS_NEW); ?></span>
    </div>

<?php
  }
?>

  </div>
</div>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
