<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo HEADING_TITLE; ?></h1>

<?php
  if ( $OSCOM_MessageStack->exists('account') ) {
    echo $OSCOM_MessageStack->get('account');
  }
?>

<div class="contentContainer">
  <h2><?php echo MY_ACCOUNT_TITLE; ?></h2>

  <div class="contentText">
    <ul class="accountLinkList">
      <li><span class="ui-icon ui-icon-person accountLinkListEntry"></span><?php echo '<a href="' . osc_href_link('account', 'edit', 'SSL') . '">' . MY_ACCOUNT_INFORMATION . '</a>'; ?></li>
      <li><span class="ui-icon ui-icon-home accountLinkListEntry"></span><?php echo '<a href="' . osc_href_link('account', 'address_book', 'SSL') . '">' . MY_ACCOUNT_ADDRESS_BOOK . '</a>'; ?></li>
      <li><span class="ui-icon ui-icon-key accountLinkListEntry"></span><?php echo '<a href="' . osc_href_link('account', 'password&change', 'SSL') . '">' . MY_ACCOUNT_PASSWORD . '</a>'; ?></li>
    </ul>
  </div>

  <h2><?php echo MY_ORDERS_TITLE; ?></h2>

  <div class="contentText">
    <ul class="accountLinkList">
      <li><span class="ui-icon ui-icon-cart accountLinkListEntry"></span><?php echo '<a href="' . osc_href_link('account', 'orders', 'SSL') . '">' . MY_ORDERS_VIEW . '</a>'; ?></li>
    </ul>
  </div>

  <h2><?php echo EMAIL_NOTIFICATIONS_TITLE; ?></h2>

  <div class="contentText">
    <ul class="accountLinkList">
      <li><span class="ui-icon ui-icon-mail-closed accountLinkListEntry"></span><?php echo '<a href="' . osc_href_link('account', 'newsletters', 'SSL') . '">' . EMAIL_NOTIFICATIONS_NEWSLETTERS . '</a>'; ?></li>
      <li><span class="ui-icon ui-icon-heart accountLinkListEntry"></span><?php echo '<a href="' . osc_href_link('account', 'notifications', 'SSL') . '">' . EMAIL_NOTIFICATIONS_PRODUCTS . '</a>'; ?></li>
    </ul>
  </div>
</div>
