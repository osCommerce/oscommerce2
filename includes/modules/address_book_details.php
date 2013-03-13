<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  if (!isset($process)) $process = false;
?>

<fieldset>
  <legend>
    <small class="inputRequirement" style="float: right;"><?php echo FORM_REQUIRED_INFORMATION; ?></small>
    <?php echo ADDRESS_BOOK_ENTRY; ?>
  </legend>

<?php
  if (ACCOUNT_GENDER == 'true') {
    $male = ($OSCOM_Customer->getGender() == 'm') ? true : false;

    if (isset($gender)) {
      $male = ($gender == 'm') ? true : false;
    } elseif (isset($entry['entry_gender'])) {
      $male = ($entry['entry_gender'] == 'm') ? true : false;
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
      <?php echo osc_draw_input_field('firstname', (isset($entry['entry_firstname']) ? $entry['entry_firstname'] : $OSCOM_Customer->getFirstName())) . (osc_not_null(ENTRY_FIRST_NAME_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_FIRST_NAME_TEXT . '</small>': ''); ?>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="lastname"><?php echo ENTRY_LAST_NAME; ?></label>
    <div class="controls">
      <?php echo osc_draw_input_field('lastname', (isset($entry['entry_lastname']) ? $entry['entry_lastname'] : $OSCOM_Customer->getLastName())) . (osc_not_null(ENTRY_LAST_NAME_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_LAST_NAME_TEXT . '</small>': ''); ?>
    </div>
  </div>

<?php
  if (ACCOUNT_COMPANY == 'true') {
?>

  <div class="control-group">
    <label class="control-label" for="company"><?php echo ENTRY_COMPANY; ?></label>
    <div class="controls">
      <?php echo osc_draw_input_field('company', (isset($entry['entry_company']) ? $entry['entry_company'] : '')) . (osc_not_null(ENTRY_COMPANY_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_COMPANY_TEXT . '</small>': ''); ?>
    </div>
  </div>

<?php
  }
?>

  <div class="control-group">
    <label class="control-label" for="street_address"><?php echo ENTRY_STREET_ADDRESS; ?></label>
    <div class="controls">
      <?php echo osc_draw_input_field('street_address', (isset($entry['entry_street_address']) ? $entry['entry_street_address'] : '')) . (osc_not_null(ENTRY_STREET_ADDRESS_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_STREET_ADDRESS_TEXT . '</small>': ''); ?>
    </div>
  </div>

<?php
  if (ACCOUNT_SUBURB == 'true') {
?>

  <div class="control-group">
    <label class="control-label" for="suburb"><?php echo ENTRY_SUBURB; ?></label>
    <div class="controls">
      <?php echo osc_draw_input_field('suburb', (isset($entry['entry_suburb']) ? $entry['entry_suburb'] : '')) . (osc_not_null(ENTRY_SUBURB_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_SUBURB_TEXT . '</small>': ''); ?>
    </div>
  </div>

<?php
  }
?>

  <div class="control-group">
    <label class="control-label" for="postcode"><?php echo ENTRY_POST_CODE; ?></label>
    <div class="controls">
      <?php echo osc_draw_input_field('postcode', (isset($entry['entry_postcode']) ? $entry['entry_postcode'] : '')) . (osc_not_null(ENTRY_POST_CODE_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_POST_CODE_TEXT . '</small>': ''); ?>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="city"><?php echo ENTRY_CITY; ?></label>
    <div class="controls">
      <?php echo osc_draw_input_field('city', (isset($entry['entry_city']) ? $entry['entry_city'] : '')) . (osc_not_null(ENTRY_CITY_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_CITY_TEXT . '</small>': ''); ?>
    </div>
  </div>

<?php
  if (ACCOUNT_STATE == 'true') {
?>

  <div class="control-group">
    <label class="control-label" for="state"><?php echo ENTRY_STATE; ?></label>
    <div class="controls">

<?php
    if ($process == true) {
      if ($entry_state_has_zones == true) {
        $zones_array = array();
        $zones_query = osc_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "' order by zone_name");
        while ($zones_values = osc_db_fetch_array($zones_query)) {
          $zones_array[] = array('id' => $zones_values['zone_name'], 'text' => $zones_values['zone_name']);
        }
        echo osc_draw_pull_down_menu('state', $zones_array);
      } else {
        echo osc_draw_input_field('state');
      }
    } else {
      echo osc_draw_input_field('state', (isset($entry['entry_country_id']) ? osc_get_zone_name($entry['entry_country_id'], $entry['entry_zone_id'], $entry['entry_state']) : ''));
    }

    if (osc_not_null(ENTRY_STATE_TEXT)) echo '&nbsp;<small class="inputRequirement">' . ENTRY_STATE_TEXT . '</small>';
?>

    </div>
  </div>

<?php
  }
?>

  <div class="control-group">
    <label class="control-label" for="country"><?php echo ENTRY_COUNTRY; ?></label>
    <div class="controls">
      <?php echo osc_get_country_list('country', (isset($entry['entry_country_id']) ? $entry['entry_country_id'] : (!isset($_POST['country']) ? STORE_COUNTRY : null))) . (osc_not_null(ENTRY_COUNTRY_TEXT) ? '&nbsp;<small class="inputRequirement">' . ENTRY_COUNTRY_TEXT . '</small>': ''); ?>
    </div>
  </div>

<?php
  if ( (isset($_GET['id']) && ($OSCOM_Customer->getDefaultAddressID() != $_GET['id'])) || ($OSCOM_Customer->hasDefaultAddress() && !isset($_GET['id'])) ) {
?>

  <div class="control-group">
    <div class="controls">
      <label class="checkbox inline">
        <?php echo osc_draw_checkbox_field('primary', 'on', false, 'id="primary"'); ?>
        <?php echo SET_AS_PRIMARY; ?>
      </label>
    </div>
  </div>

<?php
  }
?>

</fieldset>
