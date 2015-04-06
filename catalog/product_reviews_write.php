<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;

  require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/product_reviews_write.php');

  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot();
    tep_redirect(tep_href_link('login.php', '', 'SSL'));
  }

  if (!isset($_GET['products_id'])) {
    tep_redirect(tep_href_link('product_reviews.php', tep_get_all_get_params(array('action'))));
  }

  $Qcheck = $OSCOM_Db->prepare('select p.products_id, p.products_model, p.products_image, p.products_price, p.products_tax_class_id, pd.products_name from :table_products p, :table_products_description pd where p.products_id = :products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id');
  $Qcheck->bindInt(':products_id', $_GET['products_id']);
  $Qcheck->bindInt(':language_id', $_SESSION['languages_id']);
  $Qcheck->execute();

  if ( $Qcheck->fetch() === false ) {
    tep_redirect(tep_href_link('product_reviews.php', tep_get_all_get_params(array('action'))));
  }

  $Qc = $OSCOM_Db->prepare('select customers_firstname, customers_lastname from :table_customers where customers_id = :customers_id');
  $Qc->bindInt('customers_id', $_SESSION['customer_id']);
  $Qc->execute();

  if (isset($_GET['action']) && ($_GET['action'] == 'process') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
    $rating = HTML::sanitize($_POST['rating']);
    $review = HTML::sanitize($_POST['review']);

    $error = false;
    if (strlen($review) < REVIEW_TEXT_MIN_LENGTH) {
      $error = true;

      $messageStack->add('review', JS_REVIEW_TEXT);
    }

    if (($rating < 1) || ($rating > 5)) {
      $error = true;

      $messageStack->add('review', JS_REVIEW_RATING);
    }

    if ($error == false) {
      $OSCOM_Db->save(':table_reviews', array('products_id' => (int)$_GET['products_id'], 'customers_id' => (int)$_SESSION['customer_id'], 'customers_name' => tep_db_input($Qc->value('customers_firstname')) . ' ' . tep_db_input($Qc->value('customers_lastname')), 'reviews_rating' => tep_db_input($rating), 'date_added' => 'now()'));
      $insert_id = $OSCOM_Db->lastInsertId();

      $OSCOM_Db->save(':table_reviews_description', array('reviews_id' => (int)$insert_id, 'languages_id' => (int)$_SESSION['languages_id'], 'reviews_text' => tep_db_input($review)));

      $messageStack->add_session('product_reviews', TEXT_REVIEW_RECEIVED, 'success');
      tep_redirect(tep_href_link('product_reviews.php', tep_get_all_get_params(array('action'))));
    }
  }

  if ($new_price = tep_get_products_special_price($Qcheck->value('products_id'))) {
    $products_price = '<del>' . $currencies->display_price($Qcheck->value('products_price'), tep_get_tax_rate($Qcheck->value('products_tax_class_id'))) . '</del> <span class="productSpecialPrice">' . $currencies->display_price($new_price, tep_get_tax_rate($Qcheck->value('products_tax_class_id'))) . '</span>';
  } else {
    $products_price = $currencies->display_price($Qcheck->value('products_price'), tep_get_tax_rate($Qcheck->value('products_tax_class_id')));
  }

  if ( $Qcheck->value('products_model') ) {
    $products_name = $Qcheck->value('products_name') . ' <small>[' . $Qcheck->value('products_model') . ']</small>';
  } else {
    $products_name = $Qcheck->value('products_name');
  }

  $breadcrumb->add(NAVBAR_TITLE, tep_href_link('product_reviews.php', tep_get_all_get_params()));

  require('includes/template_top.php');
?>

<div class="page-header">
  <div class="row">
    <h1 class="col-sm-8"><?php echo $products_name; ?></h1>
    <h1 class="col-sm-4 text-right-not-xs"><?php echo $products_price; ?></h1>
  </div>
</div>

<?php
  if ($messageStack->size('review') > 0) {
    echo $messageStack->output('review');
  }
?>

<?php echo tep_draw_form('product_reviews_write', tep_href_link('product_reviews_write.php', 'action=process&products_id=' . (int)$_GET['products_id']), 'post', 'class="form-horizontal" role="form"', true); ?>

<div class="contentContainer">

<?php
  if ( $Qcheck->value('products_image') ) {
?>

    <div class="col-sm-4 text-center pull-right">
      <?php echo '<a href="' . tep_href_link('product_info.php', 'products_id=' . $Qcheck->valueInt('products_id')) . '">' . tep_image(DIR_WS_IMAGES . $Qcheck->value('products_image'), addslashes($Qcheck->value('products_name')), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '</a>'; ?>

      <p><?php echo tep_draw_button(IMAGE_BUTTON_IN_CART, 'glyphicon glyphicon-shopping-cart', tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action')) . 'action=buy_now'), null, null, 'btn-success btn-block'); ?></p>
    </div>

    <div class="clearfix"></div>

    <hr>

    <div class="clearfix"></div>

<?php
  }
?>

  <div class="contentText">
    <div class="row">
      <p class="col-sm-3 text-right-not-xs"><strong><?php echo SUB_TITLE_FROM; ?></strong></p>
      <p class="col-sm-9"><?php echo tep_output_string_protected($Qc->value('customers_firstname') . ' ' . $Qc->value('customers_lastname')); ?></p>
    </div>
    <div class="form-group has-feedback">
      <label for="inputReview" class="control-label col-sm-3"><?php echo SUB_TITLE_REVIEW; ?></label>
      <div class="col-sm-9">
        <?php
        echo tep_draw_textarea_field('review', 'soft', 60, 15, NULL, 'minlength="' . REVIEW_TEXT_MIN_LENGTH . '" required aria-required="true" id="inputReview" placeholder="' . ENTRY_REVIEW_TEXT . '"');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
    <div class="form-group">
      <label class="control-label col-sm-3"><?php echo SUB_TITLE_RATING; ?></label>
      <div class="col-sm-9">
        <div class="radio">
          <label>
            <?php echo tep_draw_radio_field('rating', '5') . tep_draw_stars(5, false) . ' ' . TEXT_GOOD; ?>
          </label>
        </div>
        <div class="radio">
          <label>
            <?php echo tep_draw_radio_field('rating', '4') . tep_draw_stars(4, false); ?>
          </label>
        </div>
        <div class="radio">
          <label>
            <?php echo tep_draw_radio_field('rating', '3') . tep_draw_stars(3, false); ?>
          </label>
        </div>
        <div class="radio">
          <label>
            <?php echo tep_draw_radio_field('rating', '2') . tep_draw_stars(2, false); ?>
          </label>
        </div>
        <div class="radio">
          <label>
            <?php echo tep_draw_radio_field('rating', '1', null, 'required aria-required="true"') . tep_draw_stars(1, false) . ' ' . TEXT_BAD; ?>
          </label>
        </div>
      </div>
    </div>


  </div>

  <div class="clearfix"></div>

  <div class="row">
    <div class="col-xs-6 text-right pull-right"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', null, 'primary', null, 'btn-success'); ?></div>
    <div class="col-xs-6"><?php echo tep_draw_button(IMAGE_BUTTON_BACK, 'glyphicon glyphicon-chevron-left', tep_href_link('product_reviews.php', tep_get_all_get_params(array('reviews_id', 'action')))); ?></div>
  </div>

</div>

</form>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
