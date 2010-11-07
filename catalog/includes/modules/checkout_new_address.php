<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  if (!isset($process)) $process = false;
?>

  <div class="contentText">
    <table border="0" width="100%" cellspacing="0" cellpadding="2">

<?php
  if (ACCOUNT_GENDER == 'true') {
    if (isset($gender)) {
      $male = ($gender == 'm') ? true : false;
      $female = ($gender == 'f') ? true : false;
    } else {
      $male = false;
      $female = false;
    }
?>

    <tr>
      <td class="fieldKey"><?php echo ENTRY_GENDER; ?></td>
      <td class="fieldValue"><?php echo tep_draw_radio_field('gender', 'm', $male) . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . tep_draw_radio_field('gender', 'f', $female) . '&nbsp;&nbsp;' . FEMALE . '&nbsp;' . (tep_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">' . ENTRY_GENDER_TEXT . '</span>': ''); ?></td>
    </tr>

<?php
  }
?>

    <tr>
      <td class="fieldKey"><?php echo ENTRY_FIRST_NAME; ?></td>
      <td class="fieldValue"><?php echo tep_draw_input_field('firstname') . '&nbsp;' . (tep_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_FIRST_NAME_TEXT . '</span>': ''); ?></td>
    </tr>
    <tr>
      <td class="fieldKey"><?php echo ENTRY_LAST_NAME; ?></td>
      <td class="fieldValue"><?php echo tep_draw_input_field('lastname') . '&nbsp;' . (tep_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_LAST_NAME_TEXT . '</span>': ''); ?></td>
    </tr>

<?php
  if (ACCOUNT_COMPANY == 'true') {
?>

    <tr>
      <td class="fieldKey"><?php echo ENTRY_COMPANY; ?></td>
      <td class="fieldValue"><?php echo tep_draw_input_field('company') . '&nbsp;' . (tep_not_null(ENTRY_COMPANY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COMPANY_TEXT . '</span>': ''); ?></td>
    </tr>

<?php
  }
?>

    <tr>
      <td class="fieldKey"><?php echo ENTRY_STREET_ADDRESS; ?></td>
      <td class="fieldValue"><?php echo tep_draw_input_field('street_address') . '&nbsp;' . (tep_not_null(ENTRY_STREET_ADDRESS_TEXT) ? '<span class="inputRequirement">' . ENTRY_STREET_ADDRESS_TEXT . '</span>': ''); ?></td>
    </tr>

<?php
  if (ACCOUNT_SUBURB == 'true') {
?>

    <tr>
      <td class="fieldKey"><?php echo ENTRY_SUBURB; ?></td>
      <td class="fieldValue"><?php echo tep_draw_input_field('suburb') . '&nbsp;' . (tep_not_null(ENTRY_SUBURB_TEXT) ? '<span class="inputRequirement">' . ENTRY_SUBURB_TEXT . '</span>': ''); ?></td>
    </tr>

<?php
  }
?>

    <tr>
      <td class="fieldKey"><?php echo ENTRY_POST_CODE; ?></td>
      <td class="fieldValue"><?php echo tep_draw_input_field('postcode') . '&nbsp;' . (tep_not_null(ENTRY_POST_CODE_TEXT) ? '<span class="inputRequirement">' . ENTRY_POST_CODE_TEXT . '</span>': ''); ?></td>
    </tr>
    <tr>
      <td class="fieldKey"><?php echo ENTRY_CITY; ?></td>
      <td class="fieldValue"><?php echo tep_draw_input_field('city') . '&nbsp;' . (tep_not_null(ENTRY_CITY_TEXT) ? '<span class="inputRequirement">' . ENTRY_CITY_TEXT . '</span>': ''); ?></td>
    </tr>

<?php
  if (ACCOUNT_STATE == 'true') {
?>

    <tr>
      <td class="fieldKey"><?php echo ENTRY_STATE; ?></td>
      <td class="fieldValue">

<?php
    if ($process == true) {
      if ($entry_state_has_zones == true) {
        $zones_array = array();
        $zones_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "' order by zone_name");
        while ($zones_values = tep_db_fetch_array($zones_query)) {
          $zones_array[] = array('id' => $zones_values['zone_name'], 'text' => $zones_values['zone_name']);
        }
        echo tep_draw_pull_down_menu('state', $zones_array);
      } else {
        echo tep_draw_input_field('state');
      }
    } else {
      echo tep_draw_input_field('state');
    }

    if (tep_not_null(ENTRY_STATE_TEXT)) echo '&nbsp;<span class="inputRequirement">' . ENTRY_STATE_TEXT . '</span>';
?>

      </td>
    </tr>

<?php
  }
?>

    <tr>
      <td class="fieldKey"><?php echo ENTRY_COUNTRY; ?></td>
      <td class="fieldValue"><?php echo tep_get_country_list('country', STORE_COUNTRY) . '&nbsp;' . (tep_not_null(ENTRY_COUNTRY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COUNTRY_TEXT . '</span>': ''); ?></td>
    </tr>
  </table>
</div>
