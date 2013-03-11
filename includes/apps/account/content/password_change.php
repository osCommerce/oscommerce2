<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  require(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'form_check.js.php');
?>

<h1><?php echo HEADING_TITLE_PASSWORD; ?></h1>

<?php
  if ( $OSCOM_MessageStack->exists('account_password') ) {
    echo $OSCOM_MessageStack->get('account_password');
  }
?>

<?php echo osc_draw_form('account_password', osc_href_link('account', 'password&change&process', 'SSL'), 'post', 'onsubmit="return check_form(account_password);"', true); ?>

<div class="contentContainer">
  <div>
    <span class="inputRequirement" style="float: right;"><?php echo FORM_REQUIRED_INFORMATION; ?></span>
    <h2><?php echo MY_PASSWORD_TITLE; ?></h2>
  </div>

  <div class="contentText">
    <table border="0" width="100%" cellspacing="2" cellpadding="2">
      <tr>
        <td class="fieldKey"><?php echo ENTRY_PASSWORD_CURRENT; ?></td>
        <td class="fieldValue"><?php echo osc_draw_password_field('password_current') . '&nbsp;' . (osc_not_null(ENTRY_PASSWORD_CURRENT_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_CURRENT_TEXT . '</span>': ''); ?></td>
      </tr>
      <tr> 
        <td class="fieldKey"><?php echo ENTRY_PASSWORD_NEW; ?></td>
        <td class="fieldValue"><?php echo osc_draw_password_field('password_new') . '&nbsp;' . (osc_not_null(ENTRY_PASSWORD_NEW_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_NEW_TEXT . '</span>': ''); ?></td>
      </tr>
      <tr> 
        <td class="fieldKey"><?php echo ENTRY_PASSWORD_CONFIRMATION; ?></td>
        <td class="fieldValue"><?php echo osc_draw_password_field('password_confirmation') . '&nbsp;' . (osc_not_null(ENTRY_PASSWORD_CONFIRMATION_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_CONFIRMATION_TEXT . '</span>': ''); ?></td>
      </tr>
    </table>
  </div>

  <div class="buttonSet">
    <span class="buttonAction"><?php echo osc_draw_button(IMAGE_BUTTON_CONTINUE, 'ok-sign', null, 'success'); ?></span>

    <?php echo osc_draw_button(IMAGE_BUTTON_BACK, 'arrow-left', osc_href_link('account', '', 'SSL')); ?>
  </div>
</div>

</form>
