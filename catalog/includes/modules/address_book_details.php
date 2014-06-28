<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  if (!isset($process)) $process = false;

    $address_format_query = tep_db_query("select address_format as format from " . TABLE_ADDRESS_FORMAT . " af left join " . TABLE_COUNTRIES . " c on af.address_format_id = c.address_format_id where countries_id = '" . STORE_COUNTRY . "'");
    $address_format = tep_db_fetch_array($address_format_query);
    $fmt = $address_format['format'];
	$replace = array(",", "(", ")", "\$cr", "comma", "-", " ");
	$fmt = str_replace($replace, "", $fmt);
	$pieces = explode("$",$fmt);
?>

  <div>
    <span class="inputRequirement" style="float: right;"><?php echo FORM_REQUIRED_INFORMATION; ?></span>
    <h2><?php echo NEW_ADDRESS_TITLE; ?></h2>
  </div>

  <div class="contentText">
    <table border="0" width="100%" cellspacing="2" cellpadding="2">

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

      <tr>
        <td class="fieldKey"><?php echo ENTRY_GENDER; ?></td>
        <td class="fieldValue"><?php echo tep_draw_radio_field('gender', 'm', $male) . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . tep_draw_radio_field('gender', 'f', $female) . '&nbsp;&nbsp;' . FEMALE . '&nbsp;' . (tep_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">' . ENTRY_GENDER_TEXT . '</span>': ''); ?></td>
      </tr>

<?php
  }

$fn =   '<tr> <td class="fieldKey">' . ENTRY_FIRST_NAME . '</td><td class="fieldValue">'. tep_draw_input_field('entry_firstname') . '&nbsp;' . (tep_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_FIRST_NAME_TEXT . '</span>': '') . '</td></tr>' ;
$sn =   '<tr> <td class="fieldKey">' . ENTRY_LAST_NAME . '</td><td class="fieldValue">'. tep_draw_input_field('entry_lastname') . '&nbsp;' . (tep_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_LAST_NAME_TEXT . '</span>': '') . '</td></tr>' ;

if ($pieces[1] == "firstname") {
		echo $fn;
		echo $sn;
} else {
		echo $sn;
		echo $ln;
}

  if (ACCOUNT_COMPANY == 'true') {
?>

      <tr>
        <td class="fieldKey"><?php echo ENTRY_COMPANY; ?></td>
        <td class="fieldValue"><?php echo tep_draw_input_field('company', (isset($entry['entry_company']) ? $entry['entry_company'] : '')) . '&nbsp;' . (tep_not_null(ENTRY_COMPANY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COMPANY_TEXT . '</span>': ''); ?></td>
      </tr>

<?php
  }
?>

      <tr>
        <td class="fieldKey"><?php echo ENTRY_STREET_ADDRESS; ?></td>
        <td class="fieldValue"><?php echo tep_draw_input_field('street_address', (isset($entry['entry_street_address']) ? $entry['entry_street_address'] : '')) . '&nbsp;' . (tep_not_null(ENTRY_STREET_ADDRESS_TEXT) ? '<span class="inputRequirement">' . ENTRY_STREET_ADDRESS_TEXT . '</span>': ''); ?></td>
      </tr>

<?php
  if (ACCOUNT_SUBURB == 'true') {
?>

      <tr>
        <td class="fieldKey"><?php echo ENTRY_SUBURB; ?></td>
        <td class="fieldValue"><?php echo tep_draw_input_field('suburb', (isset($entry['entry_suburb']) ? $entry['entry_suburb'] : '')) . '&nbsp;' . (tep_not_null(ENTRY_SUBURB_TEXT) ? '<span class="inputRequirement">' . ENTRY_SUBURB_TEXT . '</span>': ''); ?></td>
      </tr>

<?php
  }

$city =   '<tr> <td class="fieldKey">' . ENTRY_CITY . '</td><td class="fieldValue">'. tep_draw_input_field('city') . '&nbsp;' . (tep_not_null(ENTRY_CITY_TEXT) ? '<span class="inputRequirement">' . ENTRY_CITY_TEXT . '</span>': '') . '</td></tr>' ;
$pc =   '<tr> <td class="fieldKey">' . ENTRY_POST_CODE . '</td><td class="fieldValue">'. tep_draw_input_field('postcode') . '&nbsp;' . (tep_not_null(ENTRY_POST_CODE_TEXT) ? '<span class="inputRequirement">' . ENTRY_POST_CODE_TEXT . '</span>': '') . '</td></tr>' ;

  if (ACCOUNT_STATE == 'true') {
	  $state =   '<tr> <td class="fieldKey">' . ENTRY_STATE . '</td><td class="fieldValue">';

    if ($process == true) {
      if ($entry_state_has_zones == true) {
        $zones_array = array();
        $zones_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "' order by zone_name");
        while ($zones_values = tep_db_fetch_array($zones_query)) {
          $zones_array[] = array('id' => $zones_values['zone_name'], 'text' => $zones_values['zone_name']);
        }
        $state .= tep_draw_pull_down_menu('state', $zones_array);
      } else {
        $state .= tep_draw_input_field('state');
      }
    } else {
      $state .= tep_draw_input_field('state');
    }

    if (tep_not_null(ENTRY_STATE_TEXT)) $state .=  '&nbsp;<span class="inputRequirement">' . ENTRY_STATE_TEXT . '</span>';

$state .= '</td></tr>'
	
?>
<?php
  } else {
	$state = "";
}

if ($pieces[4] == 'city') {
	echo $city;

	if ($pieces[5] == 'postcode') { 
		echo $pc;
		echo $state;
	} else { 
		echo $state;
		echo $pc;
	}

} elseif ($pieces[4] == 'postcode') {
		echo $pc;

	  if ($pieces[5] == 'state') { 
		echo $state;
		echo $city;
	  } else { 
		echo $city;
		echo $state;
	  }

} else { 
		echo $state;

	  if ($pieces[5] == 'postcode') { 
		echo $pc;
		echo $city;
	  } else { 
		echo $city;
		echo $pc;
	  }

}

?>

      <tr>
        <td class="fieldKey"><?php echo ENTRY_COUNTRY; ?></td>
        <td class="fieldValue"><?php echo tep_get_country_list('country', (isset($entry['entry_country_id']) ? $entry['entry_country_id'] : STORE_COUNTRY)) . '&nbsp;' . (tep_not_null(ENTRY_COUNTRY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COUNTRY_TEXT . '</span>': ''); ?></td>
      </tr>

<?php
  if ((isset($HTTP_GET_VARS['edit']) && ($customer_default_address_id != $HTTP_GET_VARS['edit'])) || (isset($HTTP_GET_VARS['edit']) == false) ) {
?>

      <tr>
        <td class="fieldValue" colspan="2"><?php echo tep_draw_checkbox_field('primary', 'on', false, 'id="primary"') . ' ' . SET_AS_PRIMARY; ?></td>
      </tr>

<?php
  }
?>
    </table>
  </div>
