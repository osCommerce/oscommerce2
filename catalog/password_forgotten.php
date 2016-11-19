<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Hash;
  use OSC\OM\HTML;
  use OSC\OM\Mail;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  $OSCOM_Language->loadDefinitions('password_forgotten');

  $password_reset_initiated = false;

  if (isset($_GET['action']) && ($_GET['action'] == 'process') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
    $email_address = HTML::sanitize($_POST['email_address']);

    $Qcheck = $OSCOM_Db->get('customers', ['customers_firstname', 'customers_lastname', 'customers_id'], ['customers_email_address' => $email_address]);

    if ( $Qcheck->fetch() !== false ) {
      $actionRecorder = new actionRecorder('ar_reset_password', $Qcheck->valueInt('customers_id'), $email_address);

      if ($actionRecorder->canPerform()) {
        $actionRecorder->record();

        $reset_key = Hash::getRandomString(40);

        $OSCOM_Db->save('customers_info', ['password_reset_key' => $reset_key, 'password_reset_date' => 'now()'], ['customers_info_id' => $Qcheck->valueInt('customers_id')]);

        $reset_key_url = OSCOM::link('password_reset.php', 'account=' . urlencode($email_address) . '&key=' . $reset_key, false);

        if ( strpos($reset_key_url, '&amp;') !== false ) {
          $reset_key_url = str_replace('&amp;', '&', $reset_key_url);
        }

        $passwordEmail = new Mail($email_address, $Qcheck->value('customers_firstname') . ' ' . $Qcheck->value('customers_lastname'), STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, OSCOM::getDef('email_password_reset_subject', ['store_name' => STORE_NAME]));
        $passwordEmail->setBody(OSCOM::getDef('email_password_reset_body', ['store_name' => STORE_NAME, 'store_email_address' => STORE_OWNER_EMAIL_ADDRESS, 'reset_url' => $reset_key_url]));
        $passwordEmail->send();

        $password_reset_initiated = true;
      } else {
        $actionRecorder->record(false);

        $messageStack->add('password_forgotten', OSCOM::getDef('error_action_recorder', ['module_action_recorder_reset_password_minutes' => (defined('MODULE_ACTION_RECORDER_RESET_PASSWORD_MINUTES') ? (int)MODULE_ACTION_RECORDER_RESET_PASSWORD_MINUTES : 5)]));
      }
    } else {
      $messageStack->add('password_forgotten', OSCOM::getDef('text_no_email_address_found'));
    }
  }

  $breadcrumb->add(OSCOM::getDef('navbar_title_1'), OSCOM::link('login.php'));
  $breadcrumb->add(OSCOM::getDef('navbar_title_2'), OSCOM::link('password_forgotten.php'));

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo OSCOM::getDef('heading_title'); ?></h1>
</div>

<?php
  if ($messageStack->size('password_forgotten') > 0) {
    echo $messageStack->output('password_forgotten');
  }

  if ($password_reset_initiated == true) {
?>

<div class="contentContainer">
  <div class="contentText">
    <div class="alert alert-success"><?php echo OSCOM::getDef('text_password_reset_initiated'); ?></div>
  </div>
</div>

<?php
  } else {
?>

<?php echo HTML::form('password_forgotten', OSCOM::link('password_forgotten.php', 'action=process'), 'post', 'class="form-horizontal"', ['tokenize' => true]); ?>

<div class="contentContainer">
  <div class="contentText">
    <div class="alert alert-info"><?php echo OSCOM::getDef('text_main'); ?></div>

    <div class="form-group has-feedback">
      <label for="inputEmail" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_email_address'); ?></label>
      <div class="col-sm-9">
        <?php echo HTML::inputField('email_address', NULL, 'required aria-required="true" autofocus="autofocus" id="inputEmail" placeholder="' . OSCOM::getDef('entry_email_address_text') . '"', 'email'); ?>
        <?php echo OSCOM::getDef('form_required_input'); ?>
      </div>
    </div>
  </div>

  <div class="buttonSet row">
    <div class="col-xs-6"><?php echo HTML::button(OSCOM::getDef('image_button_back'), 'fa fa-angle-left', OSCOM::link('login.php')); ?></div>
    <div class="col-xs-6 text-right"><?php echo HTML::button(OSCOM::getDef('image_button_continue'), 'fa fa-angle-right', null, null, 'btn-success'); ?></div>
  </div>
</div>

</form>

<?php
  }

  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
