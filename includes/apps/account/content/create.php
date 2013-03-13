<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  require(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'form_check.js.php');
?>

<h1><?php echo HEADING_TITLE_CREATE; ?></h1>

<?php
  if ( $OSCOM_MessageStack->exists('create_account') ) {
    echo $OSCOM_MessageStack->get('create_account');
  }
?>

<p><?php echo sprintf(TEXT_ORIGIN_LOGIN, osc_href_link('account', 'login', 'SSL')); ?></p>

<?php echo osc_draw_form('create_account', osc_href_link('account', 'create&process', 'SSL'), 'post', 'onsubmit="return check_form(create_account);" class="form-horizontal"', true); ?>

<fieldset>
  <legend>
    <small class="inputRequirement" style="float: right;"><?php echo FORM_REQUIRED_INFORMATION; ?></small>
    <?php echo CATEGORY_PERSONAL; ?>
  </legend>

<?php
  if (ACCOUNT_GENDER == 'true') {
?>

  <div class="control-group">
    <span class="control-label"><?php echo ENTRY_GENDER; ?></span>

    <div class="controls">
      <label class="radio inline">
        <?php echo osc_draw_radio_field('gender', 'm'); ?>
        <?php echo MALE; ?>
      </label>

      <label class="radio inline">
        <?php echo osc_draw_radio_field('gender', 'f'); ?>
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
      <?php echo osc_draw_input_field('firstname') . (osc_not_null(ENTRY_FIRST_NAME_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_FIRST_NAME_TEXT . '</small>': ''); ?>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="lastname"><?php echo ENTRY_LAST_NAME; ?></label>
    <div class="controls">
      <?php echo osc_draw_input_field('lastname') . (osc_not_null(ENTRY_LAST_NAME_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_LAST_NAME_TEXT . '</small>': ''); ?>
    </div>
  </div>

<?php
  if (ACCOUNT_DOB == 'true') {
?>

  <div class="control-group">
    <label class="control-label" for="dob"><?php echo ENTRY_DATE_OF_BIRTH; ?></label>
    <div class="controls">
      <?php echo osc_draw_input_field('dob', '', 'id="dob"') . (osc_not_null(ENTRY_DATE_OF_BIRTH_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_DATE_OF_BIRTH_TEXT . '</small>': ''); ?>
    </div>
  </div>

<?php
  }
?>

  <div class="control-group">
    <label class="control-label" for="email_address"><?php echo ENTRY_EMAIL_ADDRESS; ?></label>
    <div class="controls">
      <?php echo osc_draw_input_field('email_address') . (osc_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_EMAIL_ADDRESS_TEXT . '</small>': ''); ?>
    </div>
  </div>

  <div class="control-group">
    <div class="controls">
      <label class="checkbox inline">
        <?php echo osc_draw_checkbox_field('newsletter', '1'); ?>
        <?php echo ENTRY_NEWSLETTER; ?>
      </label>

      <?php echo (osc_not_null(ENTRY_NEWSLETTER_TEXT) ? '<small class="inline inputRequirement">' . ENTRY_NEWSLETTER_TEXT . '</small>': ''); ?>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="password"><?php echo ENTRY_PASSWORD; ?></label>
    <div class="controls">
      <?php echo osc_draw_password_field('password') . (osc_not_null(ENTRY_PASSWORD_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_PASSWORD_TEXT . '</small>': ''); ?>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="confirmation"><?php echo ENTRY_PASSWORD_CONFIRMATION; ?></label>
    <div class="controls">
      <?php echo osc_draw_password_field('confirmation') . (osc_not_null(ENTRY_PASSWORD_CONFIRMATION_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_PASSWORD_CONFIRMATION_TEXT . '</small>': ''); ?>
    </div>
  </div>
</fieldset>

<div class="control-group">
  <div class="controls">
    <?php echo osc_draw_button(IMAGE_BUTTON_CONTINUE, 'ok-sign', null, 'success'); ?>
  </div>
</div>

</form>
