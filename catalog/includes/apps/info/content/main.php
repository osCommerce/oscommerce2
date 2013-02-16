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
    <ul class="accountLinkList">
      <li><?php echo '<a href="' . tep_href_link('info', 'shipping') . '">' . MODULE_BOXES_INFORMATION_BOX_SHIPPING . '</a>'; ?></li>
      <li><?php echo '<a href="' . tep_href_link('info', 'privacy') . '">' . MODULE_BOXES_INFORMATION_BOX_PRIVACY . '</a>'; ?></li>
      <li><?php echo '<a href="' . tep_href_link('info', 'conditions') . '">' . MODULE_BOXES_INFORMATION_BOX_CONDITIONS . '</a>'; ?></li>
      <li><?php echo '<a href="' . tep_href_link('info', 'contact') . '">' . MODULE_BOXES_INFORMATION_BOX_CONTACT . '</a>'; ?></li>
    </ul>
  </div>
</div>
