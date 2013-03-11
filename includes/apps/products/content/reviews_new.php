<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<script type="text/javascript"><!--
function checkForm() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";

  var review = document.product_reviews_write.review.value;

  if (review.length < <?php echo REVIEW_TEXT_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_REVIEW_TEXT; ?>";
    error = 1;
  }

  if ((document.product_reviews_write.rating[0].checked) || (document.product_reviews_write.rating[1].checked) || (document.product_reviews_write.rating[2].checked) || (document.product_reviews_write.rating[3].checked) || (document.product_reviews_write.rating[4].checked)) {
  } else {
    error_message = error_message + "<?php echo JS_REVIEW_RATING; ?>";
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

<div>
  <h1 style="float: right;"><?php echo $products_price; ?></h1>
  <h1><?php echo $products_name; ?></h1>
</div>

<?php
  if ($messageStack->size('review') > 0) {
    echo $messageStack->output('review');
  }
?>

<?php echo osc_draw_form('product_reviews_write', osc_href_link('products', 'reviews&new&process&id=' . $_GET['id']), 'post', 'onsubmit="return checkForm();"', true); ?>

<div class="contentContainer">

<?php
  if (osc_not_null($Qp->value('products_image'))) {
?>

  <div style="float: right; width: <?php echo SMALL_IMAGE_WIDTH+20; ?>px; text-align: center;">
    <?php echo '<a href="' . osc_href_link('products', 'id=' . $Qp->valueInt('products_id')) . '">' . osc_image(DIR_WS_IMAGES . $Qp->value('products_image'), $Qp->value('products_name'), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '</a>'; ?>

    <p><?php echo osc_draw_button(IMAGE_BUTTON_IN_CART, 'shopping-cart', osc_href_link('cart', 'add&id=' . $_GET['id'] . '&formid=' . md5($_SESSION['sessiontoken'])), 'success'); ?></p>
  </div>

<?php
  }
?>

  <div class="contentText">
    <table border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td class="fieldKey"><?php echo SUB_TITLE_FROM; ?></td>
        <td class="fieldValue"><?php echo $Qcustomer->valueProtected('customers_firstname') . ' ' . $Qcustomer->valueProtected('customers_lastname'); ?></td>
      </tr>
      <tr>
        <td class="fieldKey" valign="top"><?php echo SUB_TITLE_REVIEW; ?></td>
        <td class="fieldValue"><?php echo osc_draw_textarea_field('review', 'soft', 60, 15) . '<br /><span style="float: right;">' . TEXT_NO_HTML . '</span>'; ?></td>
      </tr>
      <tr>
        <td class="fieldKey"><?php echo SUB_TITLE_RATING; ?></td>
        <td class="fieldValue"><?php echo TEXT_BAD . ' ' . osc_draw_radio_field('rating', '1') . ' ' . osc_draw_radio_field('rating', '2') . ' ' . osc_draw_radio_field('rating', '3') . ' ' . osc_draw_radio_field('rating', '4') . ' ' . osc_draw_radio_field('rating', '5') . ' ' . TEXT_GOOD; ?></td>
      </tr>
    </table>
  </div>

  <div class="buttonSet">
    <span class="buttonAction"><?php echo osc_draw_button(IMAGE_BUTTON_CONTINUE, 'ok-sign', null, 'success'); ?></span>

    <?php echo osc_draw_button(IMAGE_BUTTON_BACK, 'arrow-left', osc_href_link('products', 'reviews&id=' . $_GET['id'])); ?>
  </div>
</div>

</form>
