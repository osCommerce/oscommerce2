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

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/contact_us.php');

  if (isset($_GET['action']) && ($_GET['action'] == 'send') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
    $error = false;

    $name = HTML::sanitize($_POST['name']);
    $email_address = HTML::sanitize($_POST['email']);
    $enquiry = HTML::sanitize($_POST['enquiry']);

    if (!tep_validate_email($email_address)) {
      $error = true;

      $messageStack->add('contact', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
    }

    $actionRecorder = new actionRecorder('ar_contact_us', (isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : null), $name);
    if (!$actionRecorder->canPerform()) {
      $error = true;

      $actionRecorder->record(false);

      $messageStack->add('contact', sprintf(ERROR_ACTION_RECORDER, (defined('MODULE_ACTION_RECORDER_CONTACT_US_EMAIL_MINUTES') ? (int)MODULE_ACTION_RECORDER_CONTACT_US_EMAIL_MINUTES : 15)));
    }

    if ($error == false) {
      tep_mail(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, EMAIL_SUBJECT, $enquiry, $name, $email_address);

      $actionRecorder->record();

      tep_redirect(OSCOM::link('contact_us.php', 'action=success'));
    }
  }

  $breadcrumb->add(NAVBAR_TITLE, OSCOM::link('contact_us.php'));

  require('includes/template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php
  if ($messageStack->size('contact') > 0) {
    echo $messageStack->output('contact');
  }

  if (isset($_GET['action']) && ($_GET['action'] == 'success')) {
?>

<div class="contentContainer">
  <div class="alert alert-success">
    <?php echo TEXT_SUCCESS; ?>
  </div>

  <div class="text-right">
    <?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', OSCOM::link('index.php'), 'primary', null, 'btn-default btn-block'); ?>
  </div>
</div>

<?php
  } else {
?>

<?php echo HTML::form('contact_us', OSCOM::link('contact_us.php', 'action=send'), 'post', 'class="form-horizontal" role="form"', true); ?>

<div class="contentContainer">

  <p class="inputRequirement text-right"><?php echo FORM_REQUIRED_INFORMATION; ?></p>

  <div class="contentText">
    <div class="form-group has-feedback">
      <label for="inputFromName" class="control-label col-sm-3"><?php echo ENTRY_NAME; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('name', NULL, 'required aria-required="true" autofocus="autofocus" id="inputFromName" placeholder="' . ENTRY_NAME_TEXT . '"');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputFromEmail" class="control-label col-sm-3"><?php echo ENTRY_EMAIL; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('email', NULL, 'required aria-required="true" id="inputFromEmail" placeholder="' . ENTRY_EMAIL_ADDRESS_TEXT . '"', 'email');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputEnquiry" class="control-label col-sm-3"><?php echo ENTRY_ENQUIRY; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::textareaField('enquiry', 50, 15, NULL, 'required aria-required="true" id="inputEnquiry" placeholder="' . ENTRY_ENQUIRY_TEXT . '"');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
  </div>

  <div class="text-right">
    <?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', null, 'primary', null, 'btn-success btn-block'); ?>
  </div>
</div>

</form>

<?php
  }

  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
