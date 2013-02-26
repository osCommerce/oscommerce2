<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo HEADING_TITLE_SUCCESS; ?></h1>

<div class="contentContainer">
  <div class="contentText">
    <?php echo TEXT_SUCCESS; ?>
  </div>

  <div class="contentText">
    <?php echo TEXT_SEE_ORDERS . '<br /><br />' . TEXT_CONTACT_STORE_OWNER; ?>
  </div>

  <div class="contentText">
    <h3><?php echo TEXT_THANKS_FOR_SHOPPING; ?></h3>
  </div>

<?php
  if (DOWNLOAD_ENABLED == 'true') {
    include(DIR_WS_MODULES . 'downloads.php');
  }
?>

  <div class="buttonSet">
    <span class="buttonAction"><?php echo osc_draw_button(IMAGE_BUTTON_CONTINUE, 'triangle-1-e', osc_href_link()); ?></span>
  </div>
</div>
