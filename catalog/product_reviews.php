<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  if (!isset($_GET['products_id'])) {
    osc_redirect(osc_href_link(FILENAME_REVIEWS));
  }

  $product_info_query = osc_db_query("select p.products_id, p.products_model, p.products_image, p.products_price, p.products_tax_class_id, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = '" . (int)$_GET['products_id'] . "' and p.products_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
  if (!osc_db_num_rows($product_info_query)) {
    osc_redirect(osc_href_link(FILENAME_REVIEWS));
  } else {
    $product_info = osc_db_fetch_array($product_info_query);
  }

  if ($new_price = osc_get_products_special_price($product_info['products_id'])) {
    $products_price = '<del>' . $currencies->display_price($product_info['products_price'], osc_get_tax_rate($product_info['products_tax_class_id'])) . '</del> <span class="productSpecialPrice">' . $currencies->display_price($new_price, osc_get_tax_rate($product_info['products_tax_class_id'])) . '</span>';
  } else {
    $products_price = $currencies->display_price($product_info['products_price'], osc_get_tax_rate($product_info['products_tax_class_id']));
  }

  if (osc_not_null($product_info['products_model'])) {
    $products_name = $product_info['products_name'] . '<br /><small>[' . $product_info['products_model'] . ']</small>';
  } else {
    $products_name = $product_info['products_name'];
  }

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . FILENAME_PRODUCT_REVIEWS);

  $breadcrumb->add(NAVBAR_TITLE, osc_href_link(FILENAME_PRODUCT_REVIEWS, osc_get_all_get_params()));

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<?php
  if ($messageStack->size('product_reviews') > 0) {
    echo $messageStack->output('product_reviews');
  }
?>

<div class="page-header">
  <h1 class="pull-right"><?php echo $products_price; ?></h1>
  <h1><?php echo $products_name; ?></h1>
</div>

<div class="contentContainer">

  <div class="row">
    <div class="col-sm-8 text-center alert alert-success">
      <?php
      $average_query = osc_db_query("select AVG(r.reviews_rating) as average, COUNT(r.reviews_rating) as count from " . TABLE_REVIEWS . " r where r.products_id = '" . (int)$product_info['products_id'] . "' and r.reviews_status = 1");
      $average = osc_db_fetch_array($average_query);

      echo sprintf(REVIEWS_TEXT_AVERAGE, osc_output_string_protected($average['count']), osc_draw_stars(osc_output_string_protected(round($average['average']))));
      ?>
    </div>


<?php
  if (osc_not_null($product_info['products_image'])) {
?>

    <div class="col-sm-4 text-center">
      <?php echo '<a href="' . osc_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $product_info['products_id']) . '">' . osc_image(DIR_WS_IMAGES . $product_info['products_image'], addslashes($product_info['products_name']), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '</a>'; ?>

      <p><?php echo osc_draw_button(IMAGE_BUTTON_IN_CART, 'glyphicon glyphicon-shopping-cart', osc_href_link(basename($PHP_SELF), osc_get_all_get_params(array('action')) . 'action=buy_now'), null, null, 'btn-success btn-block'); ?></p>
    </div>

    <div class="clearfix"></div>

    <hr>

    <div class="clearfix"></div>

<?php
  }
?>
  </div>
<?php

  $reviews_query_raw = "select r.reviews_id, left(rd.reviews_text, 100) as reviews_text, r.reviews_rating, r.date_added, r.customers_name from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd where r.products_id = '" . (int)$product_info['products_id'] . "' and r.reviews_id = rd.reviews_id and rd.languages_id = '" . (int)$_SESSION['languages_id'] . "' and r.reviews_status = 1 order by r.reviews_id desc";
  $reviews_split = new splitPageResults($reviews_query_raw, MAX_DISPLAY_NEW_REVIEWS);

  if ($reviews_split->number_of_rows > 0) {
    if ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3')) {
?>

  <div class="contentText">
    <p style="float: right;"><?php echo TEXT_RESULT_PAGE . ' ' . $reviews_split->display_links(MAX_DISPLAY_PAGE_LINKS, osc_get_all_get_params(array('page', 'info'))); ?></p>

    <p><?php echo $reviews_split->display_count(TEXT_DISPLAY_NUMBER_OF_REVIEWS); ?></p>
  </div>

  <br />

  <div class="clearfix"></div>

<?php
    }
    $reviews_query = osc_db_query($reviews_split->sql_query);
    while ($reviews = osc_db_fetch_array($reviews_query)) {
?>

  <div class="col-sm-6 review">
    <blockquote>
      <p><?php echo osc_output_string_protected($reviews['reviews_text']); ?></p>
      <div class="clearfix"></div>
      <footer>
        <?php
        $review_name = osc_output_string_protected($reviews['customers_name']);
        echo sprintf(REVIEWS_TEXT_RATED, osc_draw_stars($reviews['reviews_rating']), $review_name, $review_name);
        ?>
      </footer>
    </blockquote>
  </div>

<?php
    }
  } else {
?>

  <div class="contentText">
    <div class="alert alert-info">
      <?php echo TEXT_NO_REVIEWS; ?>
    </div>
  </div>

<?php
  }

  if (($reviews_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>
  <div class="clearfix"></div>

  <div class="contentText">
    <p style="float: right;"><?php echo TEXT_RESULT_PAGE . ' ' . $reviews_split->display_links(MAX_DISPLAY_PAGE_LINKS, osc_get_all_get_params(array('page', 'info'))); ?></p>

    <p><?php echo $reviews_split->display_count(TEXT_DISPLAY_NUMBER_OF_REVIEWS); ?></p>
  </div>

<?php
  }
?>

  <div class="clearfix"></div>

  <div class="row">
    <div class="col-sm-6 text-right pull-right"><?php echo osc_draw_button(IMAGE_BUTTON_WRITE_REVIEW, 'glyphicon glyphicon-comment', osc_href_link(FILENAME_PRODUCT_REVIEWS_WRITE, osc_get_all_get_params()), 'primary', null, 'btn-success'); ?></div>
    <div class="col-sm-6"><?php echo osc_draw_button(IMAGE_BUTTON_BACK, 'glyphicon glyphicon-chevron-left', osc_href_link(FILENAME_PRODUCT_INFO, osc_get_all_get_params())); ?></div>
  </div>

</div>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
