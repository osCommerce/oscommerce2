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

  if (!isset($_SESSION['customer_id']) && (ALLOW_GUEST_TO_TELL_A_FRIEND == 'false')) {
    $_SESSION['navigation']->set_snapshot();
    OSCOM::redirect('login.php');
  }

  $valid_product = false;
  if (isset($_GET['products_id'])) {
    $Qproduct = $OSCOM_Db->prepare('select p.products_id, pd.products_name from :table_products p, :table_products_description pd where p.products_id = :products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id');
    $Qproduct->bindInt(':products_id', $_GET['products_id']);
    $Qproduct->bindInt(':language_id', $_SESSION['languages_id']);
    $Qproduct->execute();

    if ($Qproduct->fetch() !== false) {
      $valid_product = true;
    }
  }

  if ($valid_product == false) {
    OSCOM::redirect('index.php');
  }

  require('includes/languages/' . $_SESSION['language'] . '/tell_a_friend.php');

  $from_name = null;
  $from_email_address = null;

  if (isset($_GET['action']) && ($_GET['action'] == 'process') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
    $error = false;

    $to_email_address = HTML::sanitize($_POST['to_email_address']);
    $to_name = HTML::sanitize($_POST['to_name']);
    $from_email_address = HTML::sanitize($_POST['from_email_address']);
    $from_name = HTML::sanitize($_POST['from_name']);
    $message = HTML::sanitize($_POST['message']);

    if (empty($from_name)) {
      $error = true;

      $messageStack->add('friend', ERROR_FROM_NAME);
    }

    if (!tep_validate_email($from_email_address)) {
      $error = true;

      $messageStack->add('friend', ERROR_FROM_ADDRESS);
    }

    if (empty($to_name)) {
      $error = true;

      $messageStack->add('friend', ERROR_TO_NAME);
    }

    if (!tep_validate_email($to_email_address)) {
      $error = true;

      $messageStack->add('friend', ERROR_TO_ADDRESS);
    }

    $actionRecorder = new actionRecorder('ar_tell_a_friend', (isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : null), $from_name);
    if (!$actionRecorder->canPerform()) {
      $error = true;

      $actionRecorder->record(false);

      $messageStack->add('friend', sprintf(ERROR_ACTION_RECORDER, (defined('MODULE_ACTION_RECORDER_TELL_A_FRIEND_EMAIL_MINUTES') ? (int)MODULE_ACTION_RECORDER_TELL_A_FRIEND_EMAIL_MINUTES : 15)));
    }

    if ($error == false) {
      $email_subject = sprintf(TEXT_EMAIL_SUBJECT, $from_name, STORE_NAME);
      $email_body = sprintf(TEXT_EMAIL_INTRO, $to_name, $from_name, $Qproduct->value('products_name'), STORE_NAME) . "\n\n";

      if (tep_not_null($message)) {
        $email_body .= $message . "\n\n";
      }

      $email_body .= sprintf(TEXT_EMAIL_LINK, OSCOM::link('product_info.php', 'products_id=' . $Qproduct->valueInt('products_id'), false)) . "\n\n" .
                     sprintf(TEXT_EMAIL_SIGNATURE, STORE_NAME . "\n" . OSCOM::link('index.php', null, false) . "\n");

      tep_mail($to_name, $to_email_address, $email_subject, $email_body, $from_name, $from_email_address);

      $actionRecorder->record();

      $messageStack->add_session('header', sprintf(TEXT_EMAIL_SUCCESSFUL_SENT, $Qproduct->value('products_name'), HTML::outputProtected($to_name)), 'success');

      OSCOM::redirect('product_info.php', 'products_id=' . $Qproduct->valueInt('products_id'));
    }
  } elseif (isset($_SESSION['customer_id'])) {
    $Qcustomer = $OSCOM_Db->get('customers', ['customers_firstname', 'customers_lastname', 'customers_email_address'], ['customers_id' => $_SESSION['customer_id']]);

    $from_name = $Qcustomer->value('customers_firstname') . ' ' . $Qcustomer->value('customers_lastname');
    $from_email_address = $Qcustomer->value('customers_email_address');
  }

  $breadcrumb->add(NAVBAR_TITLE, OSCOM::link('tell_a_friend.php', 'products_id=' . $Qproduct->valueInt('products_id')));

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo sprintf(HEADING_TITLE, $Qproduct->value('products_name')); ?></h1>
</div>

<?php
  if ($messageStack->size('friend') > 0) {
    echo $messageStack->output('friend');
  }
?>

<?php echo HTML::form('email_friend', OSCOM::link('tell_a_friend.php', 'action=process&products_id=' . $Qproduct->value('products_id')), 'post', 'class="form-horizontal"', ['tokenize' => true]); ?>

<div class="contentContainer">

  <div class="text-danger text-right"><?php echo FORM_REQUIRED_INFORMATION; ?></div>

  <h2><?php echo FORM_TITLE_CUSTOMER_DETAILS; ?></h2>

  <div class="contentText">
    <div class="form-group has-feedback">
      <label for="inputFromName" class="control-label col-sm-3"><?php echo FORM_FIELD_CUSTOMER_NAME; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('from_name', $from_name, 'required aria-required="true" id="inputFromName" placeholder="' . FORM_FIELD_CUSTOMER_NAME . '"');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputFromEmail" class="control-label col-sm-3"><?php echo FORM_FIELD_CUSTOMER_EMAIL; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('from_email_address', $from_email_address, 'required aria-required="true" id="inputFromEmail" placeholder="' . FORM_FIELD_CUSTOMER_EMAIL . '"', 'email');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
  </div>

  <h2><?php echo FORM_TITLE_FRIEND_DETAILS; ?></h2>

  <div class="contentText">
    <div class="form-group has-feedback">
      <label for="inputToName" class="control-label col-sm-3"><?php echo FORM_FIELD_FRIEND_NAME; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('to_name', NULL, 'required aria-required="true" id="inputToName" placeholder="' . FORM_FIELD_FRIEND_NAME . '"');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputToEmail" class="control-label col-sm-3"><?php echo FORM_FIELD_FRIEND_EMAIL; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('to_email_address', NULL, 'required aria-required="true" id="inputToEmail" placeholder="' . FORM_FIELD_FRIEND_EMAIL . '"', 'email');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
  </div>

  <hr>

  <div class="contentText">
    <div class="form-group has-feedback">
      <label for="inputMessage" class="control-label col-sm-3"><?php echo FORM_TITLE_FRIEND_MESSAGE; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::textareaField('message', 40, 8, NULL, 'required aria-required="true" id="inputMessage" placeholder="' . FORM_TITLE_FRIEND_MESSAGE . '"');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
  </div>

  <div class="buttonSetrow">
    <div class="col-xs-6"><?php echo HTML::button(IMAGE_BUTTON_BACK, 'fa fa-angle-left', OSCOM::link('product_info.php', 'products_id=' . $Qproduct->valueInt('products_id'))); ?></div>
    <div class="col-xs-6 text-right"><?php echo HTML::button(IMAGE_BUTTON_CONTINUE, 'fa fa-angle-right', null, null, 'btn-success'); ?></div>
  </div>
</div>

</form>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
