<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo HEADING_TITLE_PASSWORD_FORGOTTEN; ?></h1>

<?php
  if ( $OSCOM_MessageStack->exists('password_forgotten') ) {
    echo $OSCOM_MessageStack->get('password_forgotten');
  }
?>

<div class="contentContainer">
  <div class="contentText">
    <?php echo TEXT_PASSWORD_RESET_INITIATED; ?>
  </div>
</div>
