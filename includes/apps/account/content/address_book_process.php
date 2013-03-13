<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  require(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'form_check.js.php');
?>

<h1><?php echo (isset($_GET['id']) ? HEADING_TITLE_ADDRESS_BOOK_EDIT : HEADING_TITLE_ADDRESS_BOOK_NEW); ?></h1>

<?php
  if ( $OSCOM_MessageStack->exists('addressbook') ) {
    echo $OSCOM_MessageStack->get('addressbook');
  }
?>

<?php echo osc_draw_form('addressbook', osc_href_link('account', 'address_book&process' . (isset($_GET['id']) ? '&id=' . $_GET['id'] : ''), 'SSL'), 'post', 'onsubmit="return check_form(addressbook);" class="form-horizontal"', true); ?>

<?php
  require(DIR_FS_CATALOG . DIR_WS_MODULES . 'address_book_details.php');
?>

<div class="control-group">
  <div class="controls">
    <?php echo osc_draw_button(IMAGE_BUTTON_CONTINUE, 'ok-sign', null, 'success'); ?>
  </div>
</div>

</form>
