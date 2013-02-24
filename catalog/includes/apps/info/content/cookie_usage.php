<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo HEADING_TITLE_COOKIE_USAGE; ?></h1>

<div class="contentContainer">
  <div class="contentText">
    <div class="ui-widget infoBoxContainer" style=" width: 40%; float: right; padding: 0 0 10px 10px;">
      <div class="ui-widget-header infoBoxHeading"><?php echo BOX_INFORMATION_HEADING_COOKIE_USAGE; ?></div>

      <div class="ui-widget-content infoBoxContents">
        <?php echo BOX_INFORMATION_COOKIE_USAGE; ?>
      </div>
    </div>

    <?php echo TEXT_INFORMATION_COOKIE_USAGE; ?>
  </div>

  <div class="buttonSet">
    <span class="buttonAction"><?php echo osc_draw_button(IMAGE_BUTTON_CONTINUE, 'triangle-1-e', osc_href_link()); ?></span>
  </div>
</div>
