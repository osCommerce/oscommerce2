<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  require(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'form_check.js.php');
?>

<h1><?php echo HEADING_TITLE_EDIT; ?></h1>

<?php
  if ( $OSCOM_MessageStack->exists('account_edit') ) {
    echo $OSCOM_MessageStack->get('account_edit');
  }
?>

<?php echo osc_draw_form('account_edit', osc_href_link('account', 'edit&process', 'SSL'), 'post', 'onsubmit="return check_form(account_edit);" class="form-horizontal"', true); ?>

<fieldset>
  <legend>
    <small class="inputRequirement" style="float: right;"><?php echo FORM_REQUIRED_INFORMATION; ?></small>
    <?php echo MY_ACCOUNT_TITLE; ?>
  </legend>

<?php
  if (ACCOUNT_GENDER == 'true') {
    if (isset($gender)) {
      $male = ($gender == 'm') ? true : false;
    } else {
      $male = ($account['customers_gender'] == 'm') ? true : false;
    }
    $female = !$male;
?>

  <div class="control-group">
    <span class="control-label"><?php echo ENTRY_GENDER; ?></span>

    <div class="controls">
      <label class="radio inline">
        <?php echo osc_draw_radio_field('gender', 'm', $male); ?>
        <?php echo MALE; ?>
      </label>

      <label class="radio inline">
        <?php echo osc_draw_radio_field('gender', 'f', $female); ?>
        <?php echo FEMALE; ?>
      </label>

      <?php echo (osc_not_null(ENTRY_GENDER_TEXT) ? '<small class="inline inputRequirement">' . ENTRY_GENDER_TEXT . '</small>': ''); ?>
    </div>
  </div>

<?php
  }
?>

  <div class="control-group">
    <label class="control-label" for="firstname"><?php echo ENTRY_FIRST_NAME; ?></label>
    <div class="controls">
      <?php echo osc_draw_input_field('firstname', $account['customers_firstname']) . (osc_not_null(ENTRY_FIRST_NAME_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_FIRST_NAME_TEXT . '</small>': ''); ?>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="lastname"><?php echo ENTRY_LAST_NAME; ?></label>
    <div class="controls">
      <?php echo osc_draw_input_field('lastname', $account['customers_lastname']) . (osc_not_null(ENTRY_LAST_NAME_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_LAST_NAME_TEXT . '</small>': ''); ?>
    </div>
  </div>

<?php
  if (ACCOUNT_DOB == 'true') {
?>

  <div class="control-group">
    <label class="control-label" for="dob"><?php echo ENTRY_DATE_OF_BIRTH; ?></label>
    <div class="controls">
      <?php echo osc_draw_input_field('dob', osc_date_short($account['customers_dob']), 'id="dob"') . (osc_not_null(ENTRY_DATE_OF_BIRTH_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_DATE_OF_BIRTH_TEXT . '</small>': ''); ?>
    </div>
  </div>

<?php
  }
?>

  <div class="control-group">
    <label class="control-label" for="email_address"><?php echo ENTRY_EMAIL_ADDRESS; ?></label>
    <div class="controls">
      <?php echo osc_draw_input_field('email_address', $account['customers_email_address']) . (osc_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_EMAIL_ADDRESS_TEXT . '</small>': ''); ?>
    </div>
  </div>

<?php
  if ( $OSCOM_Customer->hasDefaultAddress() ) {
?>

  <div class="control-group">
    <label class="control-label" for="telephone"><?php echo ENTRY_TELEPHONE_NUMBER; ?></label>
    <div class="controls">
      <?php echo osc_draw_input_field('telephone', $account['customers_telephone']) . (osc_not_null(ENTRY_TELEPHONE_NUMBER_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_TELEPHONE_NUMBER_TEXT . '</small>': ''); ?>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="fax"><?php echo ENTRY_FAX_NUMBER; ?></label>
    <div class="controls">
      <?php echo osc_draw_input_field('fax', $account['customers_fax']) . (osc_not_null(ENTRY_FAX_NUMBER_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_FAX_NUMBER_TEXT . '</small>': ''); ?>
    </div>
  </div>

<?php
  }
?>

</fieldset>

<div class="control-group">
  <div class="controls">
    <?php echo osc_draw_button(IMAGE_BUTTON_CONTINUE, 'ok-sign', null, 'success'); ?>
  </div>
</div>

</form>
