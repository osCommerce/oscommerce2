<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Hash;
  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  chdir('../../../../');
  require('includes/application_top.php');

  if (!isset($_SESSION['customer_id'])) {
    OSCOM::redirect('login.php');
  }

  if ( MODULE_CONTENT_ACCOUNT_SET_PASSWORD_ALLOW_PASSWORD != 'True' ) {
    OSCOM::redirect('account.php');
  }

  $Qcustomer = $OSCOM_Db->get('customers', 'customers_password', ['customers_id' => $_SESSION['customer_id']]);

  if (!empty($Qcustomer->value('customers_password'))) {
    OSCOM::redirect('account.php');
  }

  $OSCOM_Language->loadDefinitions('modules/content/account/cm_account_set_password');

  if (isset($_POST['action']) && ($_POST['action'] == 'process') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
    $password_new = HTML::sanitize($_POST['password_new']);
    $password_confirmation = HTML::sanitize($_POST['password_confirmation']);

    $error = false;

    if (strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_password', OSCOM::getDef('entry_password_new_error', ['min_length' => ENTRY_PASSWORD_MIN_LENGTH]));
    } elseif ($password_new != $password_confirmation) {
      $error = true;

      $messageStack->add('account_password', OSCOM::getDef('entry_password_new_error_not_matching'));
    }

    if ($error == false) {
      $OSCOM_Db->save('customers', ['customers_password' => Hash::encrypt($password_new)], ['customers_id' => $_SESSION['customer_id']]);
      $OSCOM_Db->save('customers_info', ['customers_info_date_account_last_modified' => 'now()'], ['customers_info_id' => $_SESSION['customer_id']]);

      $messageStack->add_session('account', MODULE_CONTENT_ACCOUNT_SET_PASSWORD_SUCCESS_PASSWORD_SET, 'success');

      OSCOM::redirect('account.php');
    }
  }

  $breadcrumb->add(MODULE_CONTENT_ACCOUNT_SET_PASSWORD_NAVBAR_TITLE_1, OSCOM::link('account.php'));
  $breadcrumb->add(MODULE_CONTENT_ACCOUNT_SET_PASSWORD_NAVBAR_TITLE_2, OSCOM::link('ext/modules/content/account/set_password.php'));

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo MODULE_CONTENT_ACCOUNT_SET_PASSWORD_HEADING_TITLE; ?></h1>
</div>

<?php
  if ($messageStack->size('account_password') > 0) {
    echo $messageStack->output('account_password');
  }
?>

<?php echo HTML::form('account_password', OSCOM::link('ext/modules/content/account/set_password.php'), 'post', 'class="form-horizontal"', ['tokenize' => true, 'action' => 'process']); ?>

<div class="contentContainer">
  <p class="text-danger text-right"><?php echo OSCOM::getDef('form_required_information'); ?></p>

  <div class="contentText">
    <div class="form-group has-feedback">
      <label for="inputPassword" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_password_new'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::passwordField('password_new', NULL, 'required aria-required="true" autofocus="autofocus" id="inputPassword" autocomplete="new-password" placeholder="' . OSCOM::getDef('entry_password_new_text') . '"', 'password');
        echo OSCOM::getDef('form_required_input');
        ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputConfirmation" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_password_confirmation'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::passwordField('password_confirmation', NULL, 'required aria-required="true" id="inputConfirmation" autocomplete="new-password" placeholder="' . OSCOM::getDef('entry_password_confirmation_text') . '"', 'password');
        echo OSCOM::getDef('form_required_input');
        ?>
      </div>
    </div>
  </div>

  <div class="buttonSet row">
    <div class="col-xs-6"><?php echo HTML::button(OSCOM::getDef('image_button_back'), 'fa fa-angle-left', OSCOM::link('account.php')); ?></div>
    <div class="col-xs-6 text-right"><?php echo HTML::button(OSCOM::getDef('image_button_continue'), 'fa fa-angle-right', null, null, 'btn-success'); ?></div>
  </div>
</div>

</form>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
