<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot();
    OSCOM::redirect('login.php', '', 'SSL');
  }

// needs to be included earlier to set the success message in the messageStack
  require('includes/languages/' . $_SESSION['language'] . '/account_password.php');

  if (isset($_POST['action']) && ($_POST['action'] == 'process') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
    $password_current = HTML::sanitize($_POST['password_current']);
    $password_new = HTML::sanitize($_POST['password_new']);
    $password_confirmation = HTML::sanitize($_POST['password_confirmation']);

    $error = false;

    if (strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_password', ENTRY_PASSWORD_NEW_ERROR);
    } elseif ($password_new != $password_confirmation) {
      $error = true;

      $messageStack->add('account_password', ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING);
    }

    if ($error == false) {
      $Qcheck = $OSCOM_Db->prepare('select customers_password from :table_customers where customers_id = :customers_id');
      $Qcheck->bindInt(':customers_id', $_SESSION['customer_id']);
      $Qcheck->execute();

      if (tep_validate_password($password_current, $Qcheck->value('customers_password'))) {
        $OSCOM_Db->save('customers', ['customers_password' => tep_encrypt_password($password_new)], ['customers_id' => (int)$_SESSION['customer_id']]);
        $OSCOM_Db->save('customers_info', ['customers_info_date_account_last_modified' => 'now()'], ['customers_info_id' => (int)$_SESSION['customer_id']]);

        $messageStack->add_session('account', SUCCESS_PASSWORD_UPDATED, 'success');

        OSCOM::redirect('account.php', '', 'SSL');
      } else {
        $error = true;

        $messageStack->add('account_password', ERROR_CURRENT_PASSWORD_NOT_MATCHING);
      }
    }
  }

  $breadcrumb->add(NAVBAR_TITLE_1, OSCOM::link('account.php', '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, OSCOM::link('account_password.php', '', 'SSL'));

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php
  if ($messageStack->size('account_password') > 0) {
    echo $messageStack->output('account_password');
  }
?>

<?php echo HTML::form('account_password', OSCOM::link('account_password.php', '', 'SSL'), 'post', 'class="form-horizontal"', ['tokenize' => true, 'action' => 'process']); ?>

<?php
  $Qcustomer = $OSCOM_Db->get('customers', 'customers_email_address', ['customers_id' => (int)$_SESSION['customer_id']]);
  echo HTML::hiddenField('username', $Qcustomer->value('customers_email_address'), 'readonly autocomplete="username"');
?>

<div class="contentContainer">
  <p class="text-danger text-right"><?php echo FORM_REQUIRED_INFORMATION; ?></p>

  <div class="contentText">
    <div class="form-group has-feedback">
      <label for="inputCurrent" class="control-label col-sm-3"><?php echo ENTRY_PASSWORD_CURRENT; ?></label>
      <div class="col-sm-9">
        <?php echo HTML::passwordField('password_current', NULL, 'required aria-required="true" autofocus="autofocus" id="inputCurrent" autocomplete="current-password" placeholder="' . ENTRY_PASSWORD_CURRENT_TEXT . '"', 'password'); ?>
        <?php echo FORM_REQUIRED_INPUT; ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputPassword" class="control-label col-sm-3"><?php echo ENTRY_PASSWORD_NEW; ?></label>
      <div class="col-sm-9">
        <?php echo HTML::passwordField('password_new', NULL, 'required aria-required="true" id="inputPassword" autocomplete="new-password" placeholder="' . ENTRY_PASSWORD_NEW_TEXT . '"', 'password'); ?>
        <?php echo FORM_REQUIRED_INPUT; ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputConfirmation" class="control-label col-sm-3"><?php echo ENTRY_PASSWORD_CONFIRMATION; ?></label>
      <div class="col-sm-9">
        <?php echo HTML::passwordField('password_confirmation', NULL, 'required aria-required="true" id="inputConfirmation" autocomplete="new-password" placeholder="' . ENTRY_PASSWORD_CONFIRMATION_TEXT . '"', 'password'); ?>
        <?php echo FORM_REQUIRED_INPUT; ?>
      </div>
    </div>
  </div>

  <div class="buttonSet row">
    <div class="col-xs-6"><?php echo HTML::button(IMAGE_BUTTON_BACK, 'fa fa-angle-left', OSCOM::link('account.php', '', 'SSL')); ?></div>
    <div class="col-xs-6 text-right"><?php echo HTML::button(IMAGE_BUTTON_CONTINUE, 'fa fa-angle-right', null, null, 'btn-success'); ?></div>
  </div>

</div>

</form>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
