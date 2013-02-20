<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  require(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'form_check.js.php');
?>

<h1><?php echo HEADING_TITLE; ?></h1>

<?php
  if ($messageStack->size('create_account') > 0) {
    echo $messageStack->output('create_account');
  }
?>

<p><?php echo sprintf(TEXT_ORIGIN_LOGIN, tep_href_link('account', 'login', 'SSL')); ?></p>

<?php echo tep_draw_form('create_account', tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'), 'post', 'onsubmit="return check_form(create_account);"', true) . tep_draw_hidden_field('action', 'process'); ?>

<div class="contentContainer">
  <div>
    <span class="inputRequirement" style="float: right;"><?php echo FORM_REQUIRED_INFORMATION; ?></span>
    <h2><?php echo CATEGORY_PERSONAL; ?></h2>
  </div>

  <div class="contentText">
    <table border="0" cellspacing="2" cellpadding="2" width="100%">

<?php
  if (ACCOUNT_GENDER == 'true') {
?>

      <tr>
        <td class="fieldKey"><?php echo ENTRY_GENDER; ?></td>
        <td class="fieldValue"><?php echo tep_draw_radio_field('gender', 'm') . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . tep_draw_radio_field('gender', 'f') . '&nbsp;&nbsp;' . FEMALE . '&nbsp;' . (tep_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">' . ENTRY_GENDER_TEXT . '</span>': ''); ?></td>
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
  if (ACCOUNT_DOB == 'true') {
?>

      <tr>
        <td class="fieldKey"><?php echo ENTRY_DATE_OF_BIRTH; ?></td>
        <td class="fieldValue"><?php echo tep_draw_input_field('dob', '', 'id="dob"') . '&nbsp;' . (tep_not_null(ENTRY_DATE_OF_BIRTH_TEXT) ? '<span class="inputRequirement">' . ENTRY_DATE_OF_BIRTH_TEXT . '</span>': ''); ?><script type="text/javascript">$('#dob').datepicker({dateFormat: '<?php echo JQUERY_DATEPICKER_FORMAT; ?>', changeMonth: true, changeYear: true, yearRange: '-100:+0'});</script></td>
      </tr>

<?php
  }
?>

      <tr>
        <td class="fieldKey"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
        <td class="fieldValue"><?php echo tep_draw_input_field('email_address') . '&nbsp;' . (tep_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">' . ENTRY_EMAIL_ADDRESS_TEXT . '</span>': ''); ?></td>
      </tr>
    </table>
  </div>

<?php
  if (ACCOUNT_COMPANY == 'true') {
?>

  <h2><?php echo CATEGORY_COMPANY; ?></h2>

  <div class="contentText">
    <table border="0" cellspacing="2" cellpadding="2" width="100%">
      <tr>
        <td class="fieldKey"><?php echo ENTRY_COMPANY; ?></td>
        <td class="fieldValue"><?php echo tep_draw_input_field('company') . '&nbsp;' . (tep_not_null(ENTRY_COMPANY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COMPANY_TEXT . '</span>': ''); ?></td>
      </tr>
    </table>
  </div>

<?php
  }
?>

  <h2><?php echo CATEGORY_ADDRESS; ?></h2>

  <div class="contentText">
    <table border="0" cellspacing="2" cellpadding="2" width="100%">
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

        $Qzones = $OSCOM_PDO->prepare('select zone_name from :table_zones where zone_country_id = :zone_country_id order by zone_name');
        $Qzones->bindInt(':zone_country_id', $country);
        $Qzones->execute();

        while ( $Qzones->fetch() ) {
          $zones_array[] = array('id' => $Qzones->value('zone_name'), 'text' => $Qzones->value('zone_name'));
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
        <td class="fieldValue"><?php echo tep_get_country_list('country') . '&nbsp;' . (tep_not_null(ENTRY_COUNTRY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COUNTRY_TEXT . '</span>': ''); ?></td>
      </tr>
    </table>
  </div>

  <h2><?php echo CATEGORY_CONTACT; ?></h2>

  <div class="contentText">
    <table border="0" cellspacing="2" cellpadding="2" width="100%">
      <tr>
        <td class="fieldKey"><?php echo ENTRY_TELEPHONE_NUMBER; ?></td>
        <td class="fieldValue"><?php echo tep_draw_input_field('telephone') . '&nbsp;' . (tep_not_null(ENTRY_TELEPHONE_NUMBER_TEXT) ? '<span class="inputRequirement">' . ENTRY_TELEPHONE_NUMBER_TEXT . '</span>': ''); ?></td>
      </tr>
      <tr>
        <td class="fieldKey"><?php echo ENTRY_FAX_NUMBER; ?></td>
        <td class="fieldValue"><?php echo tep_draw_input_field('fax') . '&nbsp;' . (tep_not_null(ENTRY_FAX_NUMBER_TEXT) ? '<span class="inputRequirement">' . ENTRY_FAX_NUMBER_TEXT . '</span>': ''); ?></td>
      </tr>
      <tr>
        <td class="fieldKey"><?php echo ENTRY_NEWSLETTER; ?></td>
        <td class="fieldValue"><?php echo tep_draw_checkbox_field('newsletter', '1') . '&nbsp;' . (tep_not_null(ENTRY_NEWSLETTER_TEXT) ? '<span class="inputRequirement">' . ENTRY_NEWSLETTER_TEXT . '</span>': ''); ?></td>
      </tr>
    </table>
  </div>

  <h2><?php echo CATEGORY_PASSWORD; ?></h2>

  <div class="contentText">
    <table border="0" cellspacing="2" cellpadding="2" width="100%">
      <tr>
        <td class="fieldKey"><?php echo ENTRY_PASSWORD; ?></td>
        <td class="fieldValue"><?php echo tep_draw_password_field('password') . '&nbsp;' . (tep_not_null(ENTRY_PASSWORD_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_TEXT . '</span>': ''); ?></td>
      </tr>
      <tr>
        <td class="fieldKey"><?php echo ENTRY_PASSWORD_CONFIRMATION; ?></td>
        <td class="fieldValue"><?php echo tep_draw_password_field('confirmation') . '&nbsp;' . (tep_not_null(ENTRY_PASSWORD_CONFIRMATION_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_CONFIRMATION_TEXT . '</span>': ''); ?></td>
      </tr>
    </table>
  </div>

  <div class="buttonSet">
    <span class="buttonAction"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'person', null, 'primary'); ?></span>
  </div>
</div>

</form>
