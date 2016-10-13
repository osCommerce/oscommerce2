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
  require('includes/languages/' . $_SESSION['language'] . '/account_edit.php');

  if (isset($_POST['action']) && ($_POST['action'] == 'process') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
    if (ACCOUNT_GENDER == 'true') $gender = HTML::sanitize($_POST['gender']);
    $firstname = HTML::sanitize($_POST['firstname']);
    $lastname = HTML::sanitize($_POST['lastname']);
    if (ACCOUNT_DOB == 'true') $dob = HTML::sanitize($_POST['dob']);
    $email_address = HTML::sanitize($_POST['email_address']);
    $telephone = HTML::sanitize($_POST['telephone']);
    $fax = HTML::sanitize($_POST['fax']);

    $error = false;

    if (ACCOUNT_GENDER == 'true') {
      if ( ($gender != 'm') && ($gender != 'f') ) {
        $error = true;

        $messageStack->add('account_edit', ENTRY_GENDER_ERROR);
      }
    }

    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_edit', ENTRY_FIRST_NAME_ERROR);
    }

    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_edit', ENTRY_LAST_NAME_ERROR);
    }

    if (ACCOUNT_DOB == 'true') {
      if ((strlen($dob) < ENTRY_DOB_MIN_LENGTH) || (!empty($dob) && (!is_numeric(tep_date_raw($dob)) || !@checkdate(substr(tep_date_raw($dob), 4, 2), substr(tep_date_raw($dob), 6, 2), substr(tep_date_raw($dob), 0, 4))))) {
        $error = true;

        $messageStack->add('account_edit', ENTRY_DATE_OF_BIRTH_ERROR);
      }
    }

    if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_ERROR);
    }

    if (!tep_validate_email($email_address)) {
      $error = true;

      $messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
    }

    $Qcheck = $OSCOM_Db->prepare('select customers_id from :table_customers where customers_email_address = :customers_email_address and customers_id != :customers_id limit 1');
    $Qcheck->bindValue(':customers_email_address', $email_address);
    $Qcheck->bindInt(':customers_id', $_SESSION['customer_id']);
    $Qcheck->execute();

    if ($Qcheck->fetch() !== false) {
      $error = true;

      $messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
    }

    if (strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_edit', ENTRY_TELEPHONE_NUMBER_ERROR);
    }

    if ($error == false) {
      $sql_data_array = array('customers_firstname' => $firstname,
                              'customers_lastname' => $lastname,
                              'customers_email_address' => $email_address,
                              'customers_telephone' => $telephone,
                              'customers_fax' => $fax);

      if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
      if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = tep_date_raw($dob);

      $OSCOM_Db->save('customers', $sql_data_array, ['customers_id' => (int)$_SESSION['customer_id']]);

      $OSCOM_Db->save('customers_info', ['customers_info_date_account_last_modified' => 'now()'], ['customers_info_id' => (int)$_SESSION['customer_id']]);

      $sql_data_array = ['entry_firstname' => $firstname,
                         'entry_lastname' => $lastname];

      $OSCOM_Db->save('address_book', $sql_data_array, ['customers_id' => (int)$_SESSION['customer_id'], 'address_book_id' => (int)$_SESSION['customer_default_address_id']]);

// reset the session variables
      $_SESSION['customer_first_name'] = $firstname;

      $messageStack->add_session('account', SUCCESS_ACCOUNT_UPDATED, 'success');

      OSCOM::redirect('account.php', '', 'SSL');
    }
  }

  $Qaccount = $OSCOM_Db->prepare('select * from :table_customers where customers_id = :customers_id');
  $Qaccount->bindInt(':customers_id', $_SESSION['customer_id']);
  $Qaccount->execute();

  $breadcrumb->add(NAVBAR_TITLE_1, OSCOM::link('account.php', '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, OSCOM::link('account_edit.php', '', 'SSL'));

  require('includes/template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php
  if ($messageStack->size('account_edit') > 0) {
    echo $messageStack->output('account_edit');
  }
?>

<?php echo HTML::form('account_edit', OSCOM::link('account_edit.php', '', 'SSL'), 'post', 'class="form-horizontal"', ['tokenize' => true, 'action' => 'process']); ?>

<div class="contentContainer">
  <div class="text-danger text-right"><?php echo FORM_REQUIRED_INFORMATION; ?></div>

  <?php
  if (ACCOUNT_GENDER == 'true') {
    if (isset($gender)) {
      $male = ($gender == 'm') ? true : false;
    } else {
      $male = ($Qaccount->value('customers_gender') == 'm') ? true : false;
    }
    $female = !$male;
  ?>
  <div class="form-group has-feedback">
    <label class="control-label col-sm-3"><?php echo ENTRY_GENDER; ?></label>
    <div class="col-sm-9">
      <label class="radio-inline">
        <?php echo HTML::radioField('gender', 'm', $male, 'required aria-required="true" aria-describedby="atGender"') . ' ' . MALE; ?>
      </label>
      <label class="radio-inline">
        <?php echo HTML::radioField('gender', 'f', $female) . ' ' . FEMALE; ?>
      </label>
      <?php echo FORM_REQUIRED_INPUT; ?>
      <?php if (tep_not_null(ENTRY_GENDER_TEXT)) echo '<span id="atGender" class="help-block">' . ENTRY_GENDER_TEXT . '</span>'; ?>
    </div>
  </div>
  <?php
  }
  ?>
  <div class="form-group has-feedback">
    <label for="inputFirstName" class="control-label col-sm-3"><?php echo ENTRY_FIRST_NAME; ?></label>
    <div class="col-sm-9">
      <?php echo HTML::inputField('firstname', $Qaccount->value('customers_firstname'), 'required aria-required="true" id="inputFirstName" placeholder="' . ENTRY_FIRST_NAME_TEXT . '"'); ?>
      <?php echo FORM_REQUIRED_INPUT; ?>
    </div>
  </div>
  <div class="form-group has-feedback">
    <label for="inputLastName" class="control-label col-sm-3"><?php echo ENTRY_LAST_NAME; ?></label>
    <div class="col-sm-9">
      <?php echo HTML::inputField('lastname', $Qaccount->value('customers_lastname'), 'required aria-required="true" id="inputLastName" placeholder="' . ENTRY_LAST_NAME_TEXT . '"'); ?>
      <?php echo FORM_REQUIRED_INPUT; ?>
    </div>
  </div>

  <?php
  if (ACCOUNT_DOB == 'true') {
?>
  <div class="form-group has-feedback">
    <label for="inputName" class="control-label col-sm-3"><?php echo ENTRY_DATE_OF_BIRTH; ?></label>
    <div class="col-sm-9">
      <?php echo HTML::inputField('dob', tep_date_short($Qaccount->value('customers_dob')), 'required aria-required="true" id="dob" placeholder="' . ENTRY_DATE_OF_BIRTH_TEXT . '"'); ?>
      <?php echo FORM_REQUIRED_INPUT; ?>
    </div>
  </div>
<?php
  }
?>

  <div class="form-group has-feedback">
    <label for="inputEmail" class="control-label col-sm-3"><?php echo ENTRY_EMAIL_ADDRESS; ?></label>
    <div class="col-sm-9">
      <?php echo HTML::inputField('email_address', $Qaccount->value('customers_email_address'), 'required aria-required="true" id="inputEmail" placeholder="' . ENTRY_EMAIL_ADDRESS_TEXT . '"', 'email'); ?>
      <?php echo FORM_REQUIRED_INPUT; ?>
    </div>
  </div>
  <div class="form-group has-feedback">
    <label for="inputTelephone" class="control-label col-sm-3"><?php echo ENTRY_TELEPHONE_NUMBER; ?></label>
    <div class="col-sm-9">
      <?php echo HTML::inputField('telephone', $Qaccount->value('customers_telephone'), 'required aria-required="true" id="inputTelephone" placeholder="' . ENTRY_TELEPHONE_NUMBER_TEXT . '"', 'tel'); ?>
      <?php echo FORM_REQUIRED_INPUT; ?>
    </div>
  </div>
  <div class="form-group">
    <label for="inputFax" class="control-label col-sm-3"><?php echo ENTRY_FAX_NUMBER; ?></label>
    <div class="col-sm-9">
      <?php echo HTML::inputField('fax', $Qaccount->value('customers_fax'), 'id="inputFax" placeholder="' . ENTRY_FAX_NUMBER_TEXT . '"'); ?>
    </div>
  </div>

  <div class="buttonSet row">
    <div class="col-xs-6"><?php echo HTML::button(IMAGE_BUTTON_BACK, 'fa fa-angle-left', OSCOM::link('account.php', '', 'SSL')); ?></div>
    <div class="col-xs-6 text-right"><?php echo HTML::button(IMAGE_BUTTON_CONTINUE, 'fa fa-angle-right', null, 'primary', null, 'btn-success'); ?></div>
  </div>
</div>

</form>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
