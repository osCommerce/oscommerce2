<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');

  if (!tep_session_is_registered('customer_id')) {
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  if ( MODULE_CONTENT_ACCOUNT_SET_PASSWORD_ALLOW_PASSWORD != 'True' ) {
    tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  }

  $check_customer_query = tep_db_query("select customers_password from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
  $check_customer = tep_db_fetch_array($check_customer_query);

  if ( !empty($check_customer['customers_password']) ) {
    tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  }

// needs to be included earlier to set the success message in the messageStack
  require(DIR_WS_LANGUAGES . $language . '/modules/content/account/cm_account_set_password.php');

  if (isset($HTTP_POST_VARS['action']) && ($HTTP_POST_VARS['action'] == 'process') && isset($HTTP_POST_VARS['formid']) && ($HTTP_POST_VARS['formid'] == $sessiontoken)) {
    $password_new = tep_db_prepare_input($HTTP_POST_VARS['password_new']);
    $password_confirmation = tep_db_prepare_input($HTTP_POST_VARS['password_confirmation']);

    $error = false;

    if (strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_password', ENTRY_PASSWORD_NEW_ERROR);
    } elseif ($password_new != $password_confirmation) {
      $error = true;

      $messageStack->add('account_password', ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING);
    }

    if ($error == false) {
      tep_db_query("update " . TABLE_CUSTOMERS . " set customers_password = '" . tep_encrypt_password($password_new) . "' where customers_id = '" . (int)$customer_id . "'");

      tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_account_last_modified = now() where customers_info_id = '" . (int)$customer_id . "'");

      $messageStack->add_session('account', MODULE_CONTENT_ACCOUNT_SET_PASSWORD_SUCCESS_PASSWORD_SET, 'success');

      tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
    }
  }

  $breadcrumb->add(MODULE_CONTENT_ACCOUNT_SET_PASSWORD_NAVBAR_TITLE_1, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add(MODULE_CONTENT_ACCOUNT_SET_PASSWORD_NAVBAR_TITLE_2, tep_href_link('ext/modules/content/account/set_password.php', '', 'SSL'));

  require(DIR_WS_INCLUDES . 'template_top.php');
  require('includes/form_check.js.php');
?>

<h1><?php echo MODULE_CONTENT_ACCOUNT_SET_PASSWORD_HEADING_TITLE; ?></h1>

<?php
  if ($messageStack->size('account_password') > 0) {
    echo $messageStack->output('account_password');
  }
?>

<?php echo tep_draw_form('account_password', tep_href_link('ext/modules/content/account/set_password.php', '', 'SSL'), 'post', 'onsubmit="return check_form(account_password);"', true) . tep_draw_hidden_field('action', 'process'); ?>

<div class="contentContainer">
  <div>
    <span class="inputRequirement" style="float: right;"><?php echo FORM_REQUIRED_INFORMATION; ?></span>
    <h2><?php echo MODULE_CONTENT_ACCOUNT_SET_PASSWORD_SET_PASSWORD_TITLE; ?></h2>
  </div>

  <div class="contentText">
    <table border="0" width="100%" cellspacing="2" cellpadding="2">
      <tr> 
        <td class="fieldKey"><?php echo ENTRY_PASSWORD_NEW; ?></td>
        <td class="fieldValue"><?php echo tep_draw_password_field('password_new') . '&nbsp;' . (tep_not_null(ENTRY_PASSWORD_NEW_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_NEW_TEXT . '</span>': ''); ?></td>
      </tr>
      <tr> 
        <td class="fieldKey"><?php echo ENTRY_PASSWORD_CONFIRMATION; ?></td>
        <td class="fieldValue"><?php echo tep_draw_password_field('password_confirmation') . '&nbsp;' . (tep_not_null(ENTRY_PASSWORD_CONFIRMATION_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_CONFIRMATION_TEXT . '</span>': ''); ?></td>
      </tr>
    </table>
  </div>

  <div class="buttonSet">
    <span class="buttonAction"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'triangle-1-e', null, 'primary'); ?></span>

    <?php echo tep_draw_button(IMAGE_BUTTON_BACK, 'triangle-1-w', tep_href_link(FILENAME_ACCOUNT, '', 'SSL')); ?>
  </div>
</div>

</form>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
