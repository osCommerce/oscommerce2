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

  if (!isset($_SESSION['customer_id'])) {
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
  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/content/account/cm_account_set_password.php');

  if (isset($_POST['action']) && ($_POST['action'] == 'process') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
    $password_new = tep_db_prepare_input($_POST['password_new']);
    $password_confirmation = tep_db_prepare_input($_POST['password_confirmation']);

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
?>

<div class="page-header">
  <h1><?php echo MODULE_CONTENT_ACCOUNT_SET_PASSWORD_HEADING_TITLE; ?></h1>
</div>

<?php
  if ($messageStack->size('account_password') > 0) {
    echo $messageStack->output('account_password');
  }
?>

<?php echo tep_draw_form('account_password', tep_href_link('ext/modules/content/account/set_password.php', '', 'SSL'), 'post', 'class="form-horizontal" role="form"', true) . tep_draw_hidden_field('action', 'process'); ?>

<div class="contentContainer">
  <p class="inputRequirement text-right"><?php echo FORM_REQUIRED_INFORMATION; ?></p>

  <div class="contentText">
    <div class="form-group has-feedback">
      <label for="inputPassword" class="control-label col-xs-3"><?php echo ENTRY_PASSWORD_NEW; ?></label>
      <div class="col-xs-9">
        <?php
        echo tep_draw_password_field('password_new', NULL, 'required aria-required="true" id="inputPassword" placeholder="' . ENTRY_PASSWORD_NEW . '"');
        echo FORM_REQUIRED_INPUT;
        if (tep_not_null(ENTRY_PASSWORD_NEW_TEXT)) echo '<span class="help-block">' . ENTRY_PASSWORD_NEW_TEXT . '</span>';
        ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputConfirmation" class="control-label col-xs-3"><?php echo ENTRY_PASSWORD_CONFIRMATION; ?></label>
      <div class="col-xs-9">
        <?php
        echo tep_draw_password_field('password_confirmation', NULL, 'required aria-required="true" id="inputConfirmation" placeholder="' . ENTRY_PASSWORD_CONFIRMATION . '"');
        echo FORM_REQUIRED_INPUT;
        if (tep_not_null(ENTRY_PASSWORD_CONFIRMATION_TEXT)) echo '<span class="help-block">' . ENTRY_PASSWORD_CONFIRMATION_TEXT . '</span>';
        ?>
      </div>
    </div>

  </div>

  <div class="row">
    <div class="col-sm-6 text-right pull-right"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', null, 'primary', null, 'btn-success'); ?></div>
    <div class="col-sm-6"><?php echo tep_draw_button(IMAGE_BUTTON_BACK, 'glyphicon glyphicon-chevron-left', tep_href_link(FILENAME_ACCOUNT, '', 'SSL')); ?></div>
  </div>

</div>

</form>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
