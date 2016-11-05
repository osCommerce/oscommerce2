<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\Is;
  use OSC\OM\Mail;
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
    $Qproduct->bindInt(':language_id', $OSCOM_Language->getId());
    $Qproduct->execute();

    if ($Qproduct->fetch() !== false) {
      $valid_product = true;
    }
  }

  if ($valid_product == false) {
    OSCOM::redirect('index.php');
  }

  $OSCOM_Language->loadDefinitions('tell_a_friend');

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

      $messageStack->add('friend', OSCOM::getDef('error_from_name'));
    }

    if (!Is::email($from_email_address)) {
      $error = true;

      $messageStack->add('friend', OSCOM::getDef('error_from_address'));
    }

    if (empty($to_name)) {
      $error = true;

      $messageStack->add('friend', OSCOM::getDef('error_to_name'));
    }

    if (!Is::email($to_email_address)) {
      $error = true;

      $messageStack->add('friend', OSCOM::getDef('error_to_address'));
    }

    $actionRecorder = new actionRecorder('ar_tell_a_friend', (isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : null), $from_name);
    if (!$actionRecorder->canPerform()) {
      $error = true;

      $actionRecorder->record(false);

      $messageStack->add('friend', OSCOM::getDef('error_action_recorder', ['module_action_recorder_tell_a_friend_email_minutes' => (defined('MODULE_ACTION_RECORDER_TELL_A_FRIEND_EMAIL_MINUTES') ? (int)MODULE_ACTION_RECORDER_TELL_A_FRIEND_EMAIL_MINUTES : 15)]));
    }

    if ($error == false) {
      $email_subject = OSCOM::getDef('text_email_subject', ['from_name' => $from_name, 'store_name' => STORE_NAME]);
      $email_body = OSCOM::getDef('text_email_intro', ['to_name' => $to_name, 'from_name' => $from_name, 'products_name' => $Qproduct->value('products_name'), 'store_name' => STORE_NAME]) . "\n\n";

      if (tep_not_null($message)) {
        $email_body .= $message . "\n\n";
      }

      $email_body .= OSCOM::getDef('text_email_link', ['email_product_link' => OSCOM::link('product_info.php', 'products_id=' . $Qproduct->valueInt('products_id'), false)]) . "\n\n" .
                     OSCOM::getDef('text_email_signature', ['email_store_name_link' => STORE_NAME . "\n" . OSCOM::link('index.php', null, false) . "\n"]);

      $tellfriendEmail = new Mail($to_email_address, $to_name, $from_email_address, $from_name, $email_subject);
      $tellfriendEmail->setBody($email_body);
      $tellfriendEmail->send();

      $actionRecorder->record();

      $messageStack->add_session('header', OSCOM::getDef('text_email_successful_sent', ['email_products_name' => $Qproduct->value('products_name'), 'to_name' => HTML::outputProtected($to_name)]), 'success');

      OSCOM::redirect('product_info.php', 'products_id=' . $Qproduct->valueInt('products_id'));
    }
  } elseif (isset($_SESSION['customer_id'])) {
    $Qcustomer = $OSCOM_Db->get('customers', ['customers_firstname', 'customers_lastname', 'customers_email_address'], ['customers_id' => $_SESSION['customer_id']]);

    $from_name = $Qcustomer->value('customers_firstname') . ' ' . $Qcustomer->value('customers_lastname');
    $from_email_address = $Qcustomer->value('customers_email_address');
  }

  $breadcrumb->add(OSCOM::getDef('navbar_title'), OSCOM::link('tell_a_friend.php', 'products_id=' . $Qproduct->valueInt('products_id')));

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo OSCOM::getDef('heading_title', ['products_name' => $Qproduct->value('products_name')]); ?></h1>
</div>

<?php
  if ($messageStack->size('friend') > 0) {
    echo $messageStack->output('friend');
  }
?>

<?php echo HTML::form('email_friend', OSCOM::link('tell_a_friend.php', 'action=process&products_id=' . $Qproduct->value('products_id')), 'post', 'class="form-horizontal"', ['tokenize' => true]); ?>

<div class="contentContainer">

  <div class="text-danger text-right"><?php echo OSCOM::getDef('form_required_information'); ?></div>

  <h2><?php echo OSCOM::getDef('form_title_customer_details'); ?></h2>

  <div class="contentText">
    <div class="form-group has-feedback">
      <label for="inputFromName" class="control-label col-sm-3"><?php echo OSCOM::getDef('form_field_customer_name'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('from_name', $from_name, 'required aria-required="true" id="inputFromName" placeholder="' . OSCOM::getDef('form_field_customer_name') . '"');
        echo OSCOM::getDef('form_required_input');
        ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputFromEmail" class="control-label col-sm-3"><?php echo OSCOM::getDef('form_field_customer_email'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('from_email_address', $from_email_address, 'required aria-required="true" id="inputFromEmail" placeholder="' . OSCOM::getDef('form_field_customer_email') . '"', 'email');
        echo OSCOM::getDef('form_required_input');
        ?>
      </div>
    </div>
  </div>

  <h2><?php echo OSCOM::getDef('form_title_friend_details'); ?></h2>

  <div class="contentText">
    <div class="form-group has-feedback">
      <label for="inputToName" class="control-label col-sm-3"><?php echo OSCOM::getDef('form_field_friend_name'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('to_name', NULL, 'required aria-required="true" id="inputToName" placeholder="' . OSCOM::getDef('form_field_friend_name') . '"');
        echo OSCOM::getDef('form_required_input');
        ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputToEmail" class="control-label col-sm-3"><?php echo OSCOM::getDef('form_field_friend_email'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('to_email_address', NULL, 'required aria-required="true" id="inputToEmail" placeholder="' . OSCOM::getDef('form_field_friend_email') . '"', 'email');
        echo OSCOM::getDef('form_required_input');
        ?>
      </div>
    </div>
  </div>

  <hr>

  <div class="contentText">
    <div class="form-group has-feedback">
      <label for="inputMessage" class="control-label col-sm-3"><?php echo OSCOM::getDef('form_title_friend_message'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::textareaField('message', 40, 8, NULL, 'required aria-required="true" id="inputMessage" placeholder="' . OSCOM::getDef('form_title_friend_message') . '"');
        echo OSCOM::getDef('form_required_input');
        ?>
      </div>
    </div>
  </div>

  <div class="buttonSetrow">
    <div class="col-xs-6"><?php echo HTML::button(OSCOM::getDef('image_button_back'), 'fa fa-angle-left', OSCOM::link('product_info.php', 'products_id=' . $Qproduct->valueInt('products_id'))); ?></div>
    <div class="col-xs-6 text-right"><?php echo HTML::button(OSCOM::getDef('image_button_continue'), 'fa fa-angle-right', null, null, 'btn-success'); ?></div>
  </div>
</div>

</form>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
