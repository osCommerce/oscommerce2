<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo HEADING_TITLE_ADDRESS_BOOK_DELETE; ?></h1>

<?php
  if ($messageStack->size('addressbook') > 0) {
    echo $messageStack->output('addressbook');
  }
?>

<div class="contentContainer">
  <h2><?php echo DELETE_ADDRESS_TITLE; ?></h2>

  <div class="contentText">
    <p><?php echo DELETE_ADDRESS_DESCRIPTION; ?></p>

    <p><?php echo tep_address_label($_SESSION['customer_id'], $_GET['id'], true, ' ', '<br />'); ?></p>
  </div>

  <div>
    <span style="float: right;"><?php echo tep_draw_button(IMAGE_BUTTON_DELETE, 'trash', tep_href_link('account', 'address_book&delete&process&id=' . $_GET['id'] . '&formid=' . md5($_SESSION['sessiontoken']), 'SSL'), 'primary'); ?></span>

    <?php echo tep_draw_button(IMAGE_BUTTON_BACK, 'triangle-1-w', tep_href_link('account', 'address_book', 'SSL')); ?>
  </div>
</div>
