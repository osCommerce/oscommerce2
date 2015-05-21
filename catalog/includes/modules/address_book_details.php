<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;

  if (!isset($process)) $process = false;
?>

  <p class="inputRequirement text-right"><?php echo FORM_REQUIRED_INFORMATION; ?></p>

  <?php
  if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    ?>
    <div class="page-header">
      <h4><?php echo EDIT_ADDRESS_TITLE; ?></h4>
    </div>
    <?php
  }
  else {
    ?>
    <div class="page-header">
      <h4><?php echo NEW_ADDRESS_TITLE; ?></h4>
    </div>
    <?php
  }
  ?>

  <div class="contentText">

<?php
  if (ACCOUNT_GENDER == 'true') {
    $male = $female = false;
    if (isset($gender)) {
      $male = ($gender == 'm') ? true : false;
      $female = !$male;
    } elseif (isset($entry['entry_gender'])) {
      $male = ($entry['entry_gender'] == 'm') ? true : false;
      $female = !$male;
    }
?>

      <div class="form-group has-feedback">
        <label class="control-label col-sm-3"><?php echo ENTRY_GENDER; ?></label>
        <div class="col-sm-9">
          <label class="radio-inline">
            <?php echo tep_draw_radio_field('gender', 'm', $male, 'required aria-required="true"') . ' ' . MALE; ?>
          </label>
          <label class="radio-inline">
            <?php echo tep_draw_radio_field('gender', 'f', $female) . ' ' . FEMALE; ?>
          </label>
          <?php echo FORM_REQUIRED_INPUT; ?>
          <?php if (tep_not_null(ENTRY_GENDER_TEXT)) echo '<span class="help-block">' . ENTRY_GENDER_TEXT . '</span>'; ?>
        </div>
      </div>

<?php
  }
?>

      <div class="form-group has-feedback">
        <label for="inputFirstName" class="control-label col-sm-3"><?php echo ENTRY_FIRST_NAME; ?></label>
        <div class="col-sm-9">
          <?php echo HTML::inputField('firstname', (isset($entry['entry_firstname']) ? $entry['entry_firstname'] : ''), 'minlength="' . ENTRY_FIRST_NAME_MIN_LENGTH . '" required aria-required="true" id="inputFirstName" placeholder="' . ENTRY_FIRST_NAME_TEXT . '"'); ?>
          <?php echo FORM_REQUIRED_INPUT; ?>
        </div>
      </div>
      <div class="form-group has-feedback">
        <label for="inputLastName" class="control-label col-sm-3"><?php echo ENTRY_LAST_NAME; ?></label>
        <div class="col-sm-9">
          <?php echo HTML::inputField('lastname', (isset($entry['entry_lastname']) ? $entry['entry_lastname'] : ''), 'minlength="' . ENTRY_LAST_NAME_MIN_LENGTH . '" required aria-required="true" id="inputLastName" placeholder="' . ENTRY_LAST_NAME_TEXT . '"'); ?>
          <?php echo FORM_REQUIRED_INPUT; ?>
        </div>
      </div>

<?php
  if (ACCOUNT_COMPANY == 'true') {
?>

      <div class="form-group">
        <label for="inputCompany" class="control-label col-sm-3"><?php echo ENTRY_COMPANY; ?></label>
        <div class="col-sm-9">
          <?php
          echo HTML::inputField('company', (isset($entry['entry_company']) ? $entry['entry_company'] : ''), 'id="inputCompany" placeholder="' . ENTRY_COMPANY_TEXT . '"');
          ?>
        </div>
      </div>

<?php
  }
?>

      <div class="form-group has-feedback">
        <label for="inputStreet" class="control-label col-sm-3"><?php echo ENTRY_STREET_ADDRESS; ?></label>
        <div class="col-sm-9">
          <?php
          echo HTML::inputField('street_address', (isset($entry['entry_street_address']) ? $entry['entry_street_address'] : ''), 'required aria-required="true" id="inputStreet" placeholder="' . ENTRY_STREET_ADDRESS_TEXT . '"');
          echo FORM_REQUIRED_INPUT;
          ?>
        </div>
      </div>

<?php
  if (ACCOUNT_SUBURB == 'true') {
?>

      <div class="form-group">
        <label for="inputSuburb" class="control-label col-sm-3"><?php echo ENTRY_SUBURB; ?></label>
        <div class="col-sm-9">
          <?php
          echo HTML::inputField('suburb', (isset($entry['entry_suburb']) ? $entry['entry_suburb'] : ''), 'id="inputSuburb" placeholder="' . ENTRY_SUBURB_TEXT . '"');
          ?>
        </div>
      </div>

<?php
  }
?>

      <div class="form-group has-feedback">
        <label for="inputCity" class="control-label col-sm-3"><?php echo ENTRY_CITY; ?></label>
        <div class="col-sm-9">
          <?php
          echo HTML::inputField('city', (isset($entry['entry_city']) ? $entry['entry_city'] : ''), 'minlength="' . ENTRY_CITY_MIN_LENGTH . '" required aria-required="true" id="inputCity" placeholder="' . ENTRY_CITY_TEXT . '"');
          echo FORM_REQUIRED_INPUT;
          ?>
        </div>
      </div>
      <div class="form-group has-feedback">
        <label for="inputZip" class="control-label col-sm-3"><?php echo ENTRY_POST_CODE; ?></label>
        <div class="col-sm-9">
          <?php
          echo HTML::inputField('postcode', (isset($entry['entry_postcode']) ? $entry['entry_postcode'] : ''), 'minlength="' . ENTRY_POSTCODE_MIN_LENGTH . '" required aria-required="true" id="inputZip" placeholder="' . ENTRY_POST_CODE_TEXT . '"');
          echo FORM_REQUIRED_INPUT;
          ?>
        </div>
      </div>

<?php
  if (ACCOUNT_STATE == 'true') {
?>

      <div class="form-group">
        <label for="inputState" class="control-label col-sm-3"><?php echo ENTRY_STATE; ?></label>
        <div class="col-sm-9">
          <?php

          if ($process == true) {
            if ($entry_state_has_zones == true) {
              $zones_array = array();

              $Qzones = $OSCOM_Db->prepare('select zone_name from :table_zones where zone_country_id = :zone_country_id order by zone_name');
              $Qzones->bindInt(':zone_country_id', $country);
              $Qzones->execute();

              while ($Qzones->fetch()) {
                $zones_array[] = array('id' => $Qzones->value('zone_name'), 'text' => $Qzones->value('zone_name'));
              }
              echo tep_draw_pull_down_menu('state', $zones_array);
            } else {
              echo HTML::inputField('state');
            }
          } else {
            echo HTML::inputField('state', (isset($entry['entry_country_id']) ? tep_get_zone_name($entry['entry_country_id'], $entry['entry_zone_id'], $entry['entry_state']) : ''));
          }

          if (tep_not_null(ENTRY_STATE_TEXT)) echo '<span class="help-block">' . ENTRY_STATE_TEXT . '</span>';
          ?>
        </div>
      </div>

<?php
  }
?>

      <div class="form-group has-feedback">
        <label for="inputCountry" class="control-label col-sm-3"><?php echo ENTRY_COUNTRY; ?></label>
        <div class="col-sm-9">
          <?php
          echo tep_get_country_list('country', (isset($entry['entry_country_id']) ? $entry['entry_country_id'] : STORE_COUNTRY), 'required aria-required="true" id="inputCountry"');
          echo FORM_REQUIRED_INPUT;
          if (tep_not_null(ENTRY_COUNTRY_TEXT)) echo '<span class="help-block">' . ENTRY_COUNTRY_TEXT . '</span>';
          ?>
        </div>
      </div>

<?php
  if ((isset($_GET['edit']) && ($_SESSION['customer_default_address_id'] != $_GET['edit'])) || (isset($_GET['edit']) == false) ) {
?>

      <div class="form-group">
        <label class="control-label col-sm-3"><?php echo SET_AS_PRIMARY; ?></label>
        <div class="col-sm-9">
          <div class="checkbox">
            <label>
              <?php echo tep_draw_checkbox_field('primary', 'on', false, 'id="primary"') . '&nbsp;'; ?>
            </label>
          </div>
        </div>
      </div>

<?php
  }
?>
  </div>
