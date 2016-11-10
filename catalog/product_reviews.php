<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  if (!isset($_GET['products_id'])) {
    OSCOM::redirect('reviews.php');
  }

  $Qcheck = $OSCOM_Db->prepare('select p.products_id, p.products_model, p.products_image, p.products_price, p.products_tax_class_id, pd.products_name from :table_products p, :table_products_description pd where p.products_id = :products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id');
  $Qcheck->bindInt(':products_id', $_GET['products_id']);
  $Qcheck->bindInt(':language_id', $OSCOM_Language->getId());
  $Qcheck->execute();

  if ( $Qcheck->fetch() === false ) {
    OSCOM::redirect('reviews.php');
  }

  if ( $new_price = tep_get_products_special_price($Qcheck->valueInt('products_id')) ) {
    $products_price = '<del>' . $currencies->display_price($Qcheck->valueDecimal('products_price'), tep_get_tax_rate($Qcheck->valueInt('products_tax_class_id'))) . '</del> <span class="productSpecialPrice">' . $currencies->display_price($new_price, tep_get_tax_rate($Qcheck->valueInt('products_tax_class_id'))) . '</span>';
  } else {
    $products_price = $currencies->display_price($Qcheck->valueDecimal('products_price'), tep_get_tax_rate($Qcheck->valueInt('products_tax_class_id')));
  }

  $products_name = $Qcheck->value('products_name');

  if ( !empty($Qcheck->value('products_model')) ) {
    $products_name .= '<br /><small>[' . $Qcheck->value('products_model') . ']</small>';
  }

  $OSCOM_Language->loadDefinitions('product_reviews');

  $breadcrumb->add(OSCOM::getDef('navbar_title'), OSCOM::link('product_reviews.php', tep_get_all_get_params()));

  require($oscTemplate->getFile('template_top.php'));
?>

<?php
  if ($messageStack->size('product_reviews') > 0) {
    echo $messageStack->output('product_reviews');
  }
?>

<div itemscope itemtype="http://schema.org/Product">

<div class="page-header">
  <div class="row">
    <h1 class="col-sm-8" itemprop="name"><?php echo $products_name; ?></h1>
    <h2 class="col-sm-4 text-right-not-xs"><?php echo $products_price; ?></h2>
  </div>
</div>

<div class="contentContainer">

<?php
  $Qa = $OSCOM_Db->prepare('select AVG(r.reviews_rating) as average, COUNT(r.reviews_rating) as count from :table_reviews r left join :table_reviews_description rd on r.reviews_id = rd.reviews_id where r.products_id = :products_id and r.reviews_status = 1 and rd.languages_id = :languages_id');
  $Qa->bindInt(':products_id', $Qcheck->valueInt('products_id'));
  $Qa->bindInt(':languages_id', $OSCOM_Language->getId());
//  $Qa->setCache('product_reviews_avg-' . $OSCOM_Language->get('code') . '-p' . $Qcheck->valueInt('products_id'));
  $Qa->execute();

  echo '<div class="col-sm-8 text-center alert alert-success" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">';
  echo '  <meta itemprop="ratingValue" content="' . max(1, (int)round($Qa->value('average'))) . '" />';
  echo '  <meta itemprop="bestRating" content="5" />';
  echo    OSCOM::getDef('reviews_text_average', ['count' =>  $Qa->valueInt('count'), 'average' =>  HTML::stars(round($Qa->value('average')))]);
  echo '</div>';
?>

<?php
  if (tep_not_null($Qcheck->value('products_image'))) {
?>

  <div class="col-sm-4 text-center">
    <?php echo '<a href="' . OSCOM::link('product_info.php', 'products_id=' . $Qcheck->valueInt('products_id')) . '">' . HTML::image(OSCOM::linkImage($Qcheck->value('products_image')), $Qcheck->value('products_name'), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '</a>'; ?>

    <p><?php echo HTML::button(OSCOM::getDef('image_button_in_cart'), 'fa fa-shopping-cart', OSCOM::link(basename($PHP_SELF), tep_get_all_get_params(array('action')) . 'action=buy_now')); ?></p>
  </div>

  <div class="clearfix"></div>

  <hr>

  <div class="clearfix"></div>

<?php
  }

  $Qreviews = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS r.reviews_id, reviews_text, r.reviews_rating, r.date_added, r.customers_name from :table_reviews r, :table_reviews_description rd where r.products_id = :products_id and r.reviews_id = rd.reviews_id and rd.languages_id = :languages_id and r.reviews_status = 1 order by r.reviews_rating desc limit :page_set_offset, :page_set_max_results');
  $Qreviews->bindInt(':products_id', $Qcheck->valueInt('products_id'));
  $Qreviews->bindInt(':languages_id', $OSCOM_Language->getId());
  $Qreviews->setPageSet(MAX_DISPLAY_NEW_REVIEWS);
  $Qreviews->execute();

  if ($Qreviews->getPageSetTotalRows() > 0) {
    if ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3')) {
?>
<div class="row">
  <div class="col-sm-6 pagenumber hidden-xs">
    <?php echo $Qreviews->getPageSetLabel(OSCOM::getDef('text_display_number_of_reviews')); ?>
  </div>
  <div class="col-sm-6">
    <spanclass="pull-right pagenav"><?php echo $Qreviews->getPageSetLinks(tep_get_all_get_params(array('page', 'info'))); ?></span>
    <span class="pull-right"><?php echo OSCOM::getDef('text_result_page'); ?></span>
  </div>
</div>
<?php
    }
?>

    <div class="reviews">
<?php
    while ( $Qreviews->fetch() ) {
?>
      <blockquote class="col-sm-6" itemprop="review" itemscope itemtype="http://schema.org/Review">
        <p itemprop="reviewBody"><?php echo nl2br($Qreviews->valueProtected('reviews_text')); ?></p>
        <meta itemprop="datePublished" content="<?php echo $Qreviews->value('date_added'); ?>">
        <span itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
          <meta itemprop="ratingValue" content="<?php echo $Qreviews->value('reviews_rating'); ?>">
        </span>
        <footer>
        <?php
        $review_name = $Qreviews->valueProtected('customers_name');
        echo OSCOM::getDef('reviews_text_rated', [
		'reviews_rating' => HTML::stars($Qreviews->value('reviews_rating')),
		'review_name' => $review_name
		]);
        ?>
        </footer>
      </blockquote>
<?php
    }
?>
    </div>
    <div class="clearfix"></div>
<?php
  } else {
?>

  <div class="alert alert-info">
    <?php echo OSCOM::getDef('text_no_reviews'); ?>
  </div>

<?php
  }

    if (($Qreviews->getPageSetTotalRows() > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>
<div class="row">
  <div class="col-sm-6 pagenumber hidden-xs">
    <?php echo $Qreviews->getPageSetLabel(OSCOM::getDef('text_display_number_of_reviews')); ?>
  </div>
  <div class="col-sm-6">
    <span class="pull-right pagenav"><?php echo $Qreviews->getPageSetLinks(tep_get_all_get_params(array('page', 'info'))); ?></span>
    <span class="pull-right"><?php echo OSCOM::getDef('text_result_page'); ?></span>
  </div>
</div>
<?php
  }
?>

  <br />

  <div class="buttonSet row">
    <div class="col-xs-6">
      <?php
      $back = sizeof($_SESSION['navigation']->path)-2;
      if (isset($_SESSION['navigation']->path[$back])) {
        echo HTML::button(OSCOM::getDef('image_button_back'), 'fa fa-angle-left', OSCOM::link($_SESSION['navigation']->path[$back]['page'], tep_array_to_string($_SESSION['navigation']->path[$back]['get'], array('action'))));
      }
      ?>&nbsp;
    </div>
    <div class="col-xs-6 text-right"><?php echo HTML::button(OSCOM::getDef('image_button_write_review'), 'fa fa-commenting', OSCOM::link('product_reviews_write.php', tep_get_all_get_params()), null, 'btn-success'); ?></div>
  </div>
</div>

</div>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
