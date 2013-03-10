<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo HEADING_TITLE_ADDRESS_BOOK; ?></h1>

<?php
  if ($messageStack->size('addressbook') > 0) {
    echo $messageStack->output('addressbook');
  }
?>

<div class="contentContainer">
  <h2><?php echo PRIMARY_ADDRESS_TITLE; ?></h2>

  <div class="contentText">
    <div class="ui-widget infoBoxContainer" style="float: right;">
      <div class="ui-widget-header infoBoxHeading"><?php echo PRIMARY_ADDRESS_TITLE; ?></div>

      <div class="ui-widget-content infoBoxContents">
        <?php echo osc_address_label($OSCOM_Customer->getID(), $OSCOM_Customer->getDefaultAddressID(), true, ' ', '<br />'); ?>
      </div>
    </div>

    <?php echo PRIMARY_ADDRESS_DESCRIPTION; ?>
  </div>

  <div style="clear: both;"></div>

  <h2><?php echo ADDRESS_BOOK_TITLE; ?></h2>

  <div class="contentText">

<?php
  $Qab = $OSCOM_PDO->prepare('select address_book_id, entry_firstname as firstname, entry_lastname as lastname, entry_company as company, entry_street_address as street_address, entry_suburb as suburb, entry_city as city, entry_postcode as postcode, entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id from :table_address_book where customers_id = :customers_id order by firstname, lastname');
  $Qab->bindInt(':customers_id', $OSCOM_Customer->getID());
  $Qab->execute();

  while ( $Qab->fetch() ) {
    $format_id = osc_get_address_format_id($Qab->valueInt('country_id'));
?>

    <div>
      <span style="float: right;"><?php echo osc_draw_button(SMALL_IMAGE_BUTTON_EDIT, 'document', osc_href_link('account', 'address_book&edit&id=' . $Qab->valueInt('address_book_id'), 'SSL')) . ' ' . osc_draw_button(SMALL_IMAGE_BUTTON_DELETE, 'trash', osc_href_link('account', 'address_book&delete&id=' . $Qab->valueInt('address_book_id'), 'SSL')); ?></span>
      <p><strong><?php echo $Qab->valueProtected('firstname') . ' ' . $Qab->valueProtected('lastname'); ?></strong><?php if ($Qab->valueInt('address_book_id') == $OSCOM_Customer->getDefaultAddressID()) echo '&nbsp;<small><i>' . PRIMARY_ADDRESS . '</i></small>'; ?></p>
      <p style="padding-left: 20px;"><?php echo osc_address_format($format_id, $Qab->toArray(), true, ' ', '<br />'); ?></p>
    </div>

<?php
  }
?>

  </div>

  <div class="buttonSet">

<?php
  if (osc_count_customer_address_book_entries() < MAX_ADDRESS_BOOK_ENTRIES) {
?>

    <span class="buttonAction"><?php echo osc_draw_button(IMAGE_BUTTON_ADD_ADDRESS, 'home', osc_href_link('account', 'address_book&new', 'SSL'), 'primary'); ?></span>

<?php
  }
?>

    <?php echo osc_draw_button(IMAGE_BUTTON_BACK, 'triangle-1-w', osc_href_link('account', '', 'SSL')); ?>
  </div>

  <p><?php echo sprintf(TEXT_MAXIMUM_ENTRIES, MAX_ADDRESS_BOOK_ENTRIES); ?></p>
</div>
