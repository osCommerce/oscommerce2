<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
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

        $passwordEmail = new Mail($email_address, $Qcheck->value('customers_firstname') . ' ' . $Qcheck->value('customers_lastname'), STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, EMAIL_PASSWORD_RESET_SUBJECT);
        $passwordEmail->setBody(sprintf(EMAIL_PASSWORD_RESET_BODY, $reset_key_url));
        $passwordEmail->send();

        $password_reset_initiated = true;
      } else {
        $actionRecorder->record(false);

        $messageStack->add('password_forgotten', sprintf(ERROR_ACTION_RECORDER, (defined('MODULE_ACTION_RECORDER_RESET_PASSWORD_MINUTES') ? (int)MODULE_ACTION_RECORDER_RESET_PASSWORD_MINUTES : 5)));
      }
    } else {
      $messageStack->add('password_forgotten', TEXT_NO_EMAIL_ADDRESS_FOUND);
    }
  }

  $breadcrumb->add(NAVBAR_TITLE_1, OSCOM::link('login.php'));
  $breadcrumb->add(NAVBAR_TITLE_2, OSCOM::link('password_forgotten.php'));

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php
  if ($messageStack->size('password_forgotten') > 0) {
    echo $messageStack->output('password_forgotten');
  }

  if ($password_reset_initiated == true) {
?>

<div class="contentContainer">
  <div class="contentText">
    <div class="alert alert-success"><?php echo TEXT_PASSWORD_RESET_INITIATED; ?></div>
  </div>
</div>

<?php
  } else {
?>

<?php echo HTML::form('password_forgotten', OSCOM::link('password_forgotten.php', 'action=process'), 'post', 'class="form-horizontal"', ['tokenize' => true]); ?>

<div class="contentContainer">
  <div class="contentText">
    <div class="alert alert-info"><?php echo TEXT_MAIN; ?></div>

    <div class="form-group has-feedback">
      <label for="inputEmail" class="control-label col-sm-3"><?php echo ENTRY_EMAIL_ADDRESS; ?></label>
      <div class="col-sm-9">
        <?php echo HTML::inputField('email_address', NULL, 'required aria-required="true" autofocus="autofocus" id="inputEmail" placeholder="' . ENTRY_EMAIL_ADDRESS_TEXT . '"', 'email'); ?>
        <?php echo FORM_REQUIRED_INPUT; ?>
      </div>
    </div>
  </div>

  <div class="buttonSet row">
    <div class="col-xs-6"><?php echo HTML::button(IMAGE_BUTTON_BACK, 'fa fa-angle-left', OSCOM::link('login.php')); ?></div>
    <div class="col-xs-6 text-right"><?php echo HTML::button(IMAGE_BUTTON_CONTINUE, 'fa fa-angle-right', null, null, 'btn-success'); ?></div>
  </div>
</div>

</form>

<?php
  }

  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
