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

  $OSCOM_Language->loadDefinitions('product_reviews_write');

  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot();
    OSCOM::redirect('login.php');
  }

  if (!isset($_GET['products_id'])) {
    OSCOM::redirect('product_reviews.php', tep_get_all_get_params(array('action')));
  }

  $Qcheck = $OSCOM_Db->prepare('select p.products_id, p.products_model, p.products_image, p.products_price, p.products_tax_class_id, pd.products_name from :table_products p, :table_products_description pd where p.products_id = :products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id');
  $Qcheck->bindInt(':products_id', $_GET['products_id']);
  $Qcheck->bindInt(':language_id', $OSCOM_Language->getId());
  $Qcheck->execute();

  if ( $Qcheck->fetch() === false ) {
    OSCOM::redirect('product_reviews.php', tep_get_all_get_params(array('action')));
  }

  $Qcustomer = $OSCOM_Db->get('customers', ['customers_firstname', 'customers_lastname'], ['customers_id' => $_SESSION['customer_id']]);

  if (isset($_GET['action']) && ($_GET['action'] == 'process') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
    $rating = HTML::sanitize($_POST['rating']);
    $review = HTML::sanitize($_POST['review']);

    $error = false;
    if (strlen($review) < REVIEW_TEXT_MIN_LENGTH) {
      $error = true;

      $messageStack->add('review', OSCOM::getDef('js_review_text', ['min_length' => REVIEW_TEXT_MIN_LENGTH]));
    }

    if (($rating < 1) || ($rating > 5)) {
      $error = true;

      $messageStack->add('review', OSCOM::getDef('js_review_rating'));
    }

    if ($error == false) {
      $OSCOM_Db->save('reviews', ['products_id' => $Qcheck->valueInt('products_id'), 'customers_id' => $_SESSION['customer_id'], 'customers_name' => $Qcustomer->value('customers_firstname') . ' ' . $Qcustomer->value('customers_lastname'), 'reviews_rating' => $rating, 'date_added' => 'now()']);
      $insert_id = $OSCOM_Db->lastInsertId();

      $OSCOM_Db->save('reviews_description', ['reviews_id' => $insert_id, 'languages_id' => $OSCOM_Language->getId(), 'reviews_text' => $review]);

      $messageStack->add_session('product_reviews', OSCOM::getDef('text_review_received'), 'success');
      OSCOM::redirect('product_reviews.php', tep_get_all_get_params(array('action')));
    }
  }

  if ($new_price = tep_get_products_special_price($Qcheck->valueInt('products_id'))) {
    $products_price = '<del>' . $currencies->display_price($Qcheck->valueDecimal('products_price'), tep_get_tax_rate($Qcheck->valueInt('products_tax_class_id'))) . '</del> <span class="productSpecialPrice">' . $currencies->display_price($new_price, tep_get_tax_rate($Qcheck->valueInt('products_tax_class_id'))) . '</span>';
  } else {
    $products_price = $currencies->display_price($Qcheck->valueDecimal('products_price'), tep_get_tax_rate($Qcheck->valueInt('products_tax_class_id')));
  }

  $products_name = $Qcheck->value('products_name');

  if ( !empty($Qcheck->value('products_model')) ) {
    $products_name .= '<br /><small>[' . $Qcheck->value('products_model') . ']</small>';
  }

  $breadcrumb->add(OSCOM::getDef('navbar_title'), OSCOM::link('product_reviews.php', tep_get_all_get_params()));

  require($oscTemplate->getFile('template_top.php'));
?>

<script><!--
function checkForm() {
  var error = 0;
  var error_message = <?= json_encode(OSCOM::getDef('js_error') . "\n\n"); ?>;

  var review = document.product_reviews_write.review.value;

  if (review.length < <?php echo REVIEW_TEXT_MIN_LENGTH; ?>) {
    error_message = error_message + <?= json_encode(OSCOM::getDef('js_review_text', ['min_length' => REVIEW_TEXT_MIN_LENGTH]) . "\n"); ?>;
    error = 1;
  }

  if ((document.product_reviews_write.rating[0].checked) || (document.product_reviews_write.rating[1].checked) || (document.product_reviews_write.rating[2].checked) || (document.product_reviews_write.rating[3].checked) || (document.product_reviews_write.rating[4].checked)) {
  } else {
    error_message = error_message + <?= json_encode(OSCOM::getDef('js_review_rating') . "\n"); ?>;
    error = 1;
  }

  if (error == 1) {
    alert(error_message);
    return false;
  } else {
    return true;
  }
}
//--></script>

<div class="page-header">
  <div class="row">
    <h1 class="col-sm-4"><?php echo $products_name; ?></h1>
    <h2 class="col-sm-8 text-right-not-xs"><?php echo $products_price; ?></h2>
  </div>
</div>

<?php
  if ($messageStack->size('review') > 0) {
    echo $messageStack->output('review');
  }
?>

<?php echo HTML::form('product_reviews_write', OSCOM::link('product_reviews_write.php', 'action=process&products_id=' . $Qcheck->valueInt('products_id')), 'post', 'class="form-horizontal" onsubmit="return checkForm();"', ['tokenize' => true]); ?>

<div class="contentContainer">

<?php
  if ( !empty($Qcheck->value('products_image')) ) {
?>

    <div class="pull-right text-center">
      <?php echo '<a href="' . OSCOM::link('product_info.php', 'products_id=' . $Qcheck->valueInt('products_id')) . '">' . HTML::image(OSCOM::linkImage($Qcheck->value('products_image')), $Qcheck->value('products_name'), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '</a>'; ?>

      <p><?php echo HTML::button(OSCOM::getDef('image_button_in_cart'), 'fa fa-shopping-cart', OSCOM::link(basename($PHP_SELF), tep_get_all_get_params(array('action')) . 'action=buy_now')); ?></p>
    </div>

    <div class="clearfix"></div>

<?php
  }
?>

  <div class="contentText">
    <div class="row">
      <p class="col-sm-3 text-right-not-xs"><strong><?php echo OSCOM::getDef('sub_title_from'); ?></strong></p>
      <p class="col-sm-9"><?php echo HTML::sanitize($Qcustomer->value('customers_firstname') . ' ' . $Qcustomer->value('customers_lastname')); ?></p>
    </div>
    <div class="form-group has-feedback">
      <label for="inputReview" class="control-label col-sm-3"><?php echo OSCOM::getDef('sub_title_review'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::textareaField('review', 60, 15, NULL, 'required aria-required="true" id="inputReview" placeholder="' . OSCOM::getDef('sub_title_review_text') . '"');
        echo OSCOM::getDef('form_required_input');
        ?>
      </div>
    </div>
    <div class="form-group">
      <label class="control-label col-sm-3"><?php echo OSCOM::getDef('sub_title_rating'); ?></label>
      <div class="col-sm-9">
        <label class="radio-inline">
          <?php echo HTML::radioField('rating', '1'); ?>
        </label>
        <label class="radio-inline">
          <?php echo HTML::radioField('rating', '2'); ?>
        </label>
        <label class="radio-inline">
          <?php echo HTML::radioField('rating', '3'); ?>
        </label>
        <label class="radio-inline">
          <?php echo HTML::radioField('rating', '4'); ?>
        </label>
        <label class="radio-inline">
	  <?php echo HTML::radioField('rating', '5', 1); ?>
        </label>
        <?php echo '<div class="help-block justify" style="width: 150px;">' . OSCOM::getDef('text_bad') . '<p class="pull-right">' . OSCOM::getDef('text_good') . '</p></div>'; ?>
      </div>
    </div>

  </div>

  <div class="buttonSet row">
    <div class="col-xs-6"><?php echo HTML::button(OSCOM::getDef('image_button_back'), 'fa fa-angle-left', OSCOM::link('product_reviews.php', tep_get_all_get_params(array('reviews_id', 'action')))); ?></div>
    <div class="col-xs-6 text-right"><?php echo HTML::button(OSCOM::getDef('image_button_continue'), 'fa fa-angle-right', null, null, 'btn-success'); ?></div>
  </div>
</div>

</form>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
