<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<div>
  <h1 style="float: right;"><?php echo $products_price; ?></h1>
  <h1><?php echo $products_name; ?></h1>
</div>

<div class="contentContainer">

<?php
  if (tep_not_null($Qreview->value('products_image'))) {
?>

  <div style="float: right; width: <?php echo SMALL_IMAGE_WIDTH+20; ?>px; text-align: center;">
    <?php echo '<a href="' . tep_href_link('products', 'id=' . $Qreview->valueInt('products_id')) . '">' . tep_image(DIR_WS_IMAGES . $Qreview->value('products_image'), $Qreview->value('products_name'), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '</a>'; ?>

    <p><?php echo tep_draw_button(IMAGE_BUTTON_IN_CART, 'cart', tep_href_link('cart', 'add&id=' . $_GET['id'] . '&formid=' . md5($_SESSION['sessiontoken']))); ?></p>
  </div>

<?php
  }
?>

  <div>
    <span style="float: right;"><?php echo sprintf(TEXT_REVIEW_DATE_ADDED, tep_date_long($Qreview->value('date_added'))); ?></span>
    <h2><?php echo sprintf(TEXT_REVIEW_BY, tep_output_string_protected($Qreview->value('customers_name'))); ?></h2>
  </div>

  <div class="contentText">
    <?php echo tep_break_string(nl2br(tep_output_string_protected($Qreview->value('reviews_text'))), 60, '-<br />') . '<br /><br /><i>' . sprintf(TEXT_REVIEW_RATING, tep_image(DIR_WS_IMAGES . 'stars_' . $Qreview->valueInt('reviews_rating') . '.gif', sprintf(TEXT_OF_5_STARS, $Qreview->valueInt('reviews_rating'))), sprintf(TEXT_OF_5_STARS, $Qreview->valueInt('reviews_rating'))) . '</i>'; ?>
  </div>

  <br />

  <div class="buttonSet">
    <span class="buttonAction"><?php echo tep_draw_button(IMAGE_BUTTON_WRITE_REVIEW, 'comment', tep_href_link('products', 'reviews&new&id=' . $_GET['id']), 'primary'); ?></span>

    <?php echo tep_draw_button(IMAGE_BUTTON_BACK, 'triangle-1-w', tep_href_link('products', 'reviews&id=' . $_GET['id'])); ?>
  </div>
</div>
