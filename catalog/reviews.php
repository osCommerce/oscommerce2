<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/reviews.php');

  $breadcrumb->add(NAVBAR_TITLE, tep_href_link('reviews.php'));

  require('includes/template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<div class="contentContainer">

<?php
  $Qreviews = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS r.reviews_id, left(rd.reviews_text, 100) as reviews_text, r.reviews_rating, r.date_added, p.products_id, pd.products_name, p.products_image, r.customers_name from :table_reviews r, :table_reviews_description rd, :table_products p, :table_products_description pd where p.products_id = r.products_id and p.products_status = 1 and r.reviews_status = 1 and r.reviews_id = rd.reviews_id and p.products_id = pd.products_id and pd.language_id = :language_id and pd.language_id = rd.languages_id order by r.reviews_id desc limit :page_set_offset, :page_set_max_results');
  $Qreviews->bindInt(':language_id', $_SESSION['languages_id']);
  $Qreviews->setPageSet(MAX_DISPLAY_NEW_REVIEWS);
  $Qreviews->execute();

  if ($Qreviews->getPageSetTotalRows() > 0) {
    if ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3')) {
?>

  <div class="row">
    <div class="col-sm-6 pagenumber hidden-xs">
      <?php echo $Qreviews->getPageSetLabel(TEXT_DISPLAY_NUMBER_OF_REVIEWS); ?>
    </div>
    <div class="col-sm-6">
      <div class="pull-right pagenav"><?php echo $Qreviews->getPageSetLinks(tep_get_all_get_params(array('page', 'info'))); ?></div>
      <span class="pull-right"><?php echo TEXT_RESULT_PAGE; ?></span>
    </div>
  </div>

  <div class="clearfix"></div>

<?php
    }
    ?>
<div class="row">
<?php
    while ($Qreviews->fetch()) {
?>

  <div class="col-sm-6 review">
    <h4><?php echo '<a href="' . tep_href_link('product_reviews.php', 'products_id=' . $Qreviews->valueInt('products_id') . '&reviews_id=' . $Qreviews->valueInt('reviews_id')) . '">' . $Qreviews->value('products_name') . '</a>'; ?></h4>
    <blockquote>
      <p><span class="pull-left"><?php echo tep_image(DIR_WS_IMAGES . $Qreviews->value('products_image'), $Qreviews->value('products_name'), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?></span><?php echo $Qreviews->valueProtected('reviews_text') . ' ... '; ?></p>
      <div class="clearfix"></div>
      <footer>
        <?php
        $review_name = $Qreviews->valueProtected('customers_name');
        echo sprintf(REVIEWS_TEXT_RATED, tep_draw_stars($Qreviews->value('reviews_rating')), $review_name, $review_name) . '<a href="' . tep_href_link('product_reviews.php', 'products_id=' . $Qreviews->valueInt('products_id')) . '"><span class="pull-right label label-info">' . REVIEWS_TEXT_READ_MORE . '</span></a>'; ?>
      </footer>
    </blockquote>
  </div>

<?php
    }
    ?>
</div>

<?php
    if ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3')) {
?>

  <div class="clearfix"></div>

  <div class="row">
    <div class="col-sm-6 pagenumber hidden-xs">
      <?php echo $Qreviews->getPageSetLabel(TEXT_DISPLAY_NUMBER_OF_REVIEWS); ?>
    </div>
    <div class="col-sm-6">
      <div class="pull-right pagenav"><?php echo $Qreviews->getPageSetLinks(tep_get_all_get_params(array('page', 'info'))); ?></div>
      <span class="pull-right"><?php echo TEXT_RESULT_PAGE; ?></span>
    </div>
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
?>

</div>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
