<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo HEADING_TITLE; ?></h1>

<div class="contentContainer">
  <div class="contentText">
    <?php echo tep_customer_greeting(); ?>
  </div>

<?php
  if (tep_not_null(TEXT_MAIN)) {
?>

  <div class="contentText">
    <?php echo TEXT_MAIN; ?>
  </div>

<?php
  }

  include(DIR_WS_MODULES . FILENAME_NEW_PRODUCTS);
  include(DIR_WS_MODULES . FILENAME_UPCOMING_PRODUCTS);
?>

</div>
