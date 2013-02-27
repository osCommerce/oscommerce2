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
  if ($messageStack->size('addressbook') > 0) {
    echo $messageStack->output('addressbook');
  }
?>

<?php echo osc_draw_form('addressbook', osc_href_link('account', 'address_book&process' . (isset($_GET['id']) ? '&id=' . $_GET['id'] : ''), 'SSL'), 'post', 'onsubmit="return check_form(addressbook);"', true); ?>

<div class="contentContainer">

<?php
  require(DIR_FS_CATALOG . DIR_WS_MODULES . 'address_book_details.php');
?>

<?php
  if ( isset($_GET['id']) ) {
?>

  <div>
    <span style="float: right;"><?php echo osc_draw_button(IMAGE_BUTTON_UPDATE, 'refresh', null, 'primary'); ?></span>

    <?php echo osc_draw_button(IMAGE_BUTTON_BACK, 'triangle-1-w', osc_href_link('account', 'address_book', 'SSL')); ?>
  </div>

<?php
  } else {
    if ( $OSCOM_NavigationHistory->hasSnapshot() ) {
      $back_link = $OSCOM_NavigationHistory->getSnapshotURL();
    } else {
      $back_link = osc_href_link('account', 'address_book', 'SSL');
    }
?>

  <div class="buttonSet">
    <span class="buttonAction"><?php echo osc_draw_button(IMAGE_BUTTON_CONTINUE, 'triangle-1-e', null, 'primary'); ?></span>

    <?php echo osc_draw_button(IMAGE_BUTTON_BACK, 'triangle-1-w', $back_link); ?>
  </div>

<?php
  }
?>

</div>

</form>
