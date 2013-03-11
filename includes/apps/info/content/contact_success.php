<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo HEADING_TITLE_CONTACT; ?></h1>

<?php
  if ( $OSCOM_MessageStack->exists('contact') ) {
    echo $OSCOM_MessageStack->get('contact');
  }
?>

<div class="contentContainer">
  <div class="contentText">
    <?php echo INFO_CONTACT_SUCCESS; ?>
  </div>

  <div style="float: right;">
    <?php echo osc_draw_button(IMAGE_BUTTON_CONTINUE, 'ok-sign', osc_href_link()); ?>
  </div>
</div>
