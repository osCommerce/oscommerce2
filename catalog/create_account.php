<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\DateTime;
  use OSC\OM\Hash;
  use OSC\OM\HTML;
  use OSC\OM\Is;
  use OSC\OM\Mail;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  require('includes/application_top.php');

  $OSCOM_Language->loadDefinitions('create_account');

  $process = false;
  if (isset($_POST['action']) && ($_POST['action'] == 'process') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
    $process = true;

    if (ACCOUNT_GENDER == 'true') {
      if (isset($_POST['gender'])) {
        $gender = HTML::sanitize($_POST['gender']);
      } else {
        $gender = false;
      }
    }
    $firstname = HTML::sanitize($_POST['firstname']);
    $lastname = HTML::sanitize($_POST['lastname']);
    if (ACCOUNT_DOB == 'true') $dob = HTML::sanitize($_POST['dob']);
    $email_address = HTML::sanitize($_POST['email_address']);
    if (ACCOUNT_COMPANY == 'true') $company = HTML::sanitize($_POST['company']);
    $street_address = HTML::sanitize($_POST['street_address']);
    if (ACCOUNT_SUBURB == 'true') $suburb = HTML::sanitize($_POST['suburb']);
    $postcode = HTML::sanitize($_POST['postcode']);
    $city = HTML::sanitize($_POST['city']);
    if (ACCOUNT_STATE == 'true') {
      $state = HTML::sanitize($_POST['state']);
      if (isset($_POST['zone_id'])) {
        $zone_id = HTML::sanitize($_POST['zone_id']);
      } else {
        $zone_id = false;
      }
    }
    $country = HTML::sanitize($_POST['country']);
    $telephone = HTML::sanitize($_POST['telephone']);
    $fax = HTML::sanitize($_POST['fax']);
    if (isset($_POST['newsletter'])) {
      $newsletter = HTML::sanitize($_POST['newsletter']);
    } else {
      $newsletter = false;
    }
    $password = HTML::sanitize($_POST['password']);
    $confirmation = HTML::sanitize($_POST['confirmation']);

    $error = false;

    if (ACCOUNT_GENDER == 'true') {
      if ( ($gender != 'm') && ($gender != 'f') ) {
        $error = true;

        $messageStack->add('create_account', OSCOM::getDef('entry_gender_error'));
      }
    }

    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', OSCOM::getDef('entry_first_name_error', ['min_length' => ENTRY_FIRST_NAME_MIN_LENGTH]));
    }

    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', OSCOM::getDef('entry_last_name_error', ['min_length' => ENTRY_LAST_NAME_MIN_LENGTH]));
    }

    if (ACCOUNT_DOB == 'true') {
      $dobDateTime = new DateTime($dob);

      if ((strlen($dob) < ENTRY_DOB_MIN_LENGTH) || ($dobDateTime->isValid() === false)) {
        $error = true;

        $messageStack->add('create_account', OSCOM::getDef('entry_date_of_birth_error'));
      }
    }

    if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
      $error = true;
      $messageStack->add('create_account', OSCOM::getDef('entry_email_address_error', ['min_length' => ENTRY_EMAIL_ADDRESS_MIN_LENGTH]));
    } elseif (Is::email($email_address) == false) {
      $error = true;

      $messageStack->add('create_account', OSCOM::getDef('entry_email_address_check_error'));
    } else {
      $Qcheck = $OSCOM_Db->prepare('select customers_id from :table_customers where customers_email_address = :customers_email_address limit 1');
      $Qcheck->bindValue(':customers_email_address', $email_address);
      $Qcheck->execute();

      if ($Qcheck->fetch() !== false) {
        $error = true;

        $messageStack->add('create_account', OSCOM::getDef('entry_email_address_error_exists'));
      }
    }

    if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', OSCOM::getDef('entry_street_address_error', ['min_length' => ENTRY_STREET_ADDRESS_MIN_LENGTH]));
    }

    if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', OSCOM::getDef('entry_post_code_error', ['min_length' => ENTRY_POSTCODE_MIN_LENGTH]));
    }

    if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', OSCOM::getDef('entry_city_error', ['min_length' => ENTRY_CITY_MIN_LENGTH]));
    }

    if (is_numeric($country) == false) {
      $error = true;

      $messageStack->add('create_account', OSCOM::getDef('entry_country_error'));
    }

    if (ACCOUNT_STATE == 'true') {
      $zone_id = 0;

      $Qcheck = $OSCOM_Db->prepare('select zone_id from :table_zones where zone_country_id = :zone_country_id');
      $Qcheck->bindInt(':zone_country_id', $country);
      $Qcheck->execute();

      $entry_state_has_zones = ($Qcheck->fetch() !== false);

      if ($entry_state_has_zones == true) {
        $Qzone = $OSCOM_Db->prepare('select distinct zone_id from :table_zones where zone_country_id = :zone_country_id and (zone_name = :zone_name or zone_code = :zone_code)');
        $Qzone->bindInt(':zone_country_id', $country);
        $Qzone->bindValue(':zone_name', $state);
        $Qzone->bindValue(':zone_code', $state);
        $Qzone->execute();

        if (count($Qzone->fetchAll()) === 1) {
          $zone_id = $Qzone->valueInt('zone_id');
        } else {
          $error = true;

          $messageStack->add('create_account', OSCOM::getDef('entry_state_error_select'));
        }
      } else {
        if (strlen($state) < ENTRY_STATE_MIN_LENGTH) {
          $error = true;

          $messageStack->add('create_account', OSCOM::getDef('entry_state_error', ['min_length' => ENTRY_STATE_MIN_LENGTH]));
        }
      }
    }

    if (strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', OSCOM::getDef('entry_telephone_number_error', ['min_length' => ENTRY_TELEPHONE_MIN_LENGTH]));
    }


    if (strlen($password) < ENTRY_PASSWORD_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', OSCOM::getDef('entry_password_error', ['min_length' => ENTRY_PASSWORD_MIN_LENGTH]));
    } elseif ($password != $confirmation) {
      $error = true;

      $messageStack->add('create_account', OSCOM::getDef('entry_password_error_not_matching'));
    }

    if ($error == false) {
      $sql_data_array = array('customers_firstname' => $firstname,
                              'customers_lastname' => $lastname,
                              'customers_email_address' => $email_address,
                              'customers_telephone' => $telephone,
                              'customers_fax' => $fax,
                              'customers_newsletter' => $newsletter,
                              'customers_password' => Hash::encrypt($password));

      if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
      if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = $dobDateTime->getRaw(false);

      $OSCOM_Db->save('customers', $sql_data_array);

      $_SESSION['customer_id'] = $OSCOM_Db->lastInsertId();

      $sql_data_array = array('customers_id' => $_SESSION['customer_id'],
                              'entry_firstname' => $firstname,
                              'entry_lastname' => $lastname,
                              'entry_street_address' => $street_address,
                              'entry_postcode' => $postcode,
                              'entry_city' => $city,
                              'entry_country_id' => $country);

      if (ACCOUNT_GENDER == 'true') $sql_data_array['entry_gender'] = $gender;
      if (ACCOUNT_COMPANY == 'true') $sql_data_array['entry_company'] = $company;
      if (ACCOUNT_SUBURB == 'true') $sql_data_array['entry_suburb'] = $suburb;
      if (ACCOUNT_STATE == 'true') {
        if ($zone_id > 0) {
          $sql_data_array['entry_zone_id'] = $zone_id;
          $sql_data_array['entry_state'] = '';
        } else {
          $sql_data_array['entry_zone_id'] = '0';
          $sql_data_array['entry_state'] = $state;
        }
      }

      $OSCOM_Db->save('address_book', $sql_data_array);

      $address_id = $OSCOM_Db->lastInsertId();

      $OSCOM_Db->save('customers', ['customers_default_address_id' => (int)$address_id], ['customers_id' => (int)$_SESSION['customer_id']]);

      $OSCOM_Db->save('customers_info', ['customers_info_id' => (int)$_SESSION['customer_id'], 'customers_info_number_of_logons' => '0', 'customers_info_date_account_created' => 'now()']);

      Registry::get('Session')->recreate();

      $_SESSION['customer_first_name'] = $firstname;
      $_SESSION['customer_default_address_id'] = $address_id;
      $_SESSION['customer_country_id'] = $country;
      $_SESSION['customer_zone_id'] = $zone_id;

// restore cart contents
      $_SESSION['cart']->restore_contents();

// build the message content
      $name = $firstname . ' ' . $lastname;

      if (ACCOUNT_GENDER == 'true') {
         if ($gender == 'm') {
           $email_text = OSCOM::getDef('email_greet_mr', ['lastname' => $lastname]);
         } else {
           $email_text = OSCOM::getDef('email_greet_ms', ['lastname' => $lastname]);
         }
      } else {
        $email_text = OSCOM::getDef('email_greet_none', ['firstname' => $firstname]);
      }

      $email_text .= "\n\n" .
                     OSCOM::getDef('email_welcome', ['store_name' => STORE_NAME]) . "\n\n" .
                     OSCOM::getDef('email_text') . "\n\n" .
                     OSCOM::getDef('email_contact', ['store_email_address' => STORE_OWNER_EMAIL_ADDRESS]) . "\n\n" .
                     OSCOM::getDef('email_warning', ['store_email_address' => STORE_OWNER_EMAIL_ADDRESS]) . "\n";

      $customerEmail = new Mail($email_address, $name, STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, OSCOM::getDef('email_subject', ['store_name' => STORE_NAME]));
      $customerEmail->setBody($email_text);
      $customerEmail->send();

      OSCOM::redirect('create_account_success.php');
    }
  }

  $breadcrumb->add(OSCOM::getDef('navbar_title'), OSCOM::link('create_account.php'));

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo OSCOM::getDef('heading_title'); ?></h1>
</div>

<?php
  if ($messageStack->size('create_account') > 0) {
    echo $messageStack->output('create_account');
  }
?>

<div class="alert alert-warning">
  <?php echo OSCOM::getDef('text_origin_login', ['login_link' => OSCOM::link('login.php', tep_get_all_get_params())]); ?><span class="text-danger pull-right text-right"><?php echo OSCOM::getDef('form_required_information'); ?></span>
</div>

<?php echo HTML::form('create_account', OSCOM::link('create_account.php'), 'post', 'class="form-horizontal"', ['tokenize' => true, 'action' => 'process']); ?>

<div class="contentContainer">

  <h2><?php echo OSCOM::getDef('category_personal'); ?></h2>
  <div class="contentText">

<?php
  if (ACCOUNT_GENDER == 'true') {
?>
    <div class="form-group has-feedback">
      <label class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_gender'); ?></label>
      <div class="col-sm-9">
        <label class="radio-inline">
          <?php echo HTML::radioField('gender', 'm', NULL, 'required aria-required="true" aria-describedby="atGender"') . ' ' . OSCOM::getDef('male'); ?>
        </label>
        <label class="radio-inline">
          <?php echo HTML::radioField('gender', 'f') . ' ' . OSCOM::getDef('female'); ?>
        </label>
        <?php echo OSCOM::getDef('form_required_input'); ?>
        <?php if (tep_not_null(OSCOM::getDef('entry_gender_text'))) echo '<span id="atGender" class="help-block">' . OSCOM::getDef('entry_gender_text') . '</span>'; ?>
      </div>
    </div>
<?php
  }
?>
    <div class="form-group has-feedback">
      <label for="inputFirstName" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_first_name'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('firstname', NULL, 'required aria-required="true" id="inputFirstName" placeholder="' . OSCOM::getDef('entry_first_name_text') . '"');
        echo OSCOM::getDef('form_required_input');
        ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputLastName" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_last_name'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('lastname', NULL, 'required aria-required="true" id="inputLastName" placeholder="' . OSCOM::getDef('entry_last_name_text') . '"');
        echo OSCOM::getDef('form_required_input');
        ?>
      </div>
    </div>
<?php
  if (ACCOUNT_DOB == 'true') {
?>
    <div class="form-group has-feedback">
      <label for="dob" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_date_of_birth'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('dob', '', 'data-provide="datepicker" required aria-required="true" id="dob" placeholder="' . OSCOM::getDef('entry_date_of_birth_text') . '"');
        echo OSCOM::getDef('form_required_input');
        ?>
      </div>
    </div>
<?php
  }
?>
    <div class="form-group has-feedback">
      <label for="inputEmail" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_email_address'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('email_address', NULL, 'required aria-required="true" id="inputEmail" placeholder="' . OSCOM::getDef('entry_email_address_text') . '"', 'email');
        echo OSCOM::getDef('form_required_input');
        ?>
      </div>
    </div>
  </div>
<?php
  if (ACCOUNT_COMPANY == 'true') {
?>

  <h2><?php echo OSCOM::getDef('category_company'); ?></h2>

  <div class="contentText">
    <div class="form-group">
      <label for="inputCompany" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_company'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('company', NULL, 'id="inputCompany" placeholder="' . OSCOM::getDef('entry_company_text') . '"');
        ?>
      </div>
    </div>
  </div>

<?php
  }
?>

  <h2><?php echo OSCOM::getDef('category_address'); ?></h2>

  <div class="contentText">
    <div class="form-group has-feedback">
      <label for="inputStreet" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_street_address'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('street_address', NULL, 'required aria-required="true" id="inputStreet" placeholder="' . OSCOM::getDef('entry_street_address_text') . '"');
        echo OSCOM::getDef('form_required_input');
        ?>
      </div>
    </div>

<?php
  if (ACCOUNT_SUBURB == 'true') {
?>
    <div class="form-group">
      <label for="inputSuburb" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_suburb'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('suburb', NULL, 'id="inputSuburb" placeholder="' . OSCOM::getDef('entry_suburb_text') . '"');
        ?>
      </div>
    </div>
<?php
  }
?>
    <div class="form-group has-feedback">
      <label for="inputCity" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_city'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('city', NULL, 'required aria-required="true" id="inputCity" placeholder="' . OSCOM::getDef('entry_city_text') . '"');
        echo OSCOM::getDef('form_required_input');
        ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputZip" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_post_code'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('postcode', NULL, 'required aria-required="true" id="inputZip" placeholder="' . OSCOM::getDef('entry_post_code_text') . '"');
        echo OSCOM::getDef('form_required_input');
        ?>
      </div>
    </div>
<?php
  if (ACCOUNT_STATE == 'true') {
?>
    <div class="form-group has-feedback">
      <label for="inputState" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_state'); ?></label>
      <div class="col-sm-9">
        <?php
        if ($process == true) {
          if ($entry_state_has_zones == true) {
            $zones_array = array();

            $Qzones = $OSCOM_Db->prepare('select zone_name from :table_zones where zone_country_id = :zone_country_id order by zone_name');
            $Qzones->bindInt(':zone_country_id', $country);
            $Qzones->execute();

            while ($Qzones->fetch()) {
              $zones_array[] = array('id' => $Qzones->value('zone_name'), 'text' => $Qzones->value('zone_name'));
            }
            echo HTML::selectField('state', $zones_array, 0, 'id="inputState" aria-describedby="atState"');
            echo OSCOM::getDef('form_required_input');
            if (tep_not_null(OSCOM::getDef('entry_state_text'))) echo '<span id="atState" class="help-block">' . OSCOM::getDef('entry_state_text') . '</span>';
          } else {
            echo HTML::inputField('state', NULL, 'id="inputState" placeholder="' . OSCOM::getDef('entry_state_text') . '"');
            echo OSCOM::getDef('form_required_input');
          }
        } else {
          echo HTML::inputField('state', NULL, 'id="inputState" placeholder="' . OSCOM::getDef('entry_state_text') . '"');
          echo OSCOM::getDef('form_required_input');
        }
        ?>
      </div>
    </div>
<?php
  }
?>
    <div class="form-group has-feedback">
      <label for="inputCountry" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_country'); ?></label>
      <div class="col-sm-9">
        <?php
        echo tep_get_country_list('country', NULL, 'required aria-required="true" aria-describedby="atCountry" id="inputCountry"');
        echo OSCOM::getDef('form_required_input');
        if (tep_not_null(OSCOM::getDef('entry_country_text'))) echo '<span id="atCountry" class="help-block">' . OSCOM::getDef('entry_country_text') . '</span>';
        ?>
      </div>
    </div>
  </div>

  <h2><?php echo OSCOM::getDef('category_contact'); ?></h2>

  <div class="contentText">
    <div class="form-group has-feedback">
      <label for="inputTelephone" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_telephone_number'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('telephone', NULL, 'required aria-required="true" id="inputTelephone" placeholder="' . OSCOM::getDef('entry_telephone_number_text') . '"', 'tel');
        echo OSCOM::getDef('form_required_input');
        ?>
      </div>
    </div>
    <div class="form-group">
      <label for="inputFax" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_fax_number'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('fax', '', 'id="inputFax" placeholder="' . OSCOM::getDef('entry_fax_number_text') . '"', 'tel');
        ?>
      </div>
    </div>
    <div class="form-group">
      <label for="inputNewsletter" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_newsletter'); ?></label>
      <div class="col-sm-9">
        <div class="checkbox">
          <label>
            <?php echo HTML::checkboxField('newsletter', '1', NULL, 'id="inputNewsletter"'); ?>
            <?php if (tep_not_null(OSCOM::getDef('entry_newsletter_text'))) echo OSCOM::getDef('entry_newsletter_text'); ?>
          </label>
        </div>
      </div>
    </div>

  </div>

  <h2><?php echo OSCOM::getDef('category_password'); ?></h2>

  <div class="contentText">
    <div class="form-group has-feedback">
      <label for="inputPassword" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_password'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::passwordField('password', NULL, 'required aria-required="true" id="inputPassword" autocomplete="new-password" placeholder="' . OSCOM::getDef('entry_password_text') . '"', 'password');
        echo OSCOM::getDef('form_required_input');
        ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputConfirmation" class="control-label col-sm-3"><?php echo OSCOM::getDef('entry_password_confirmation'); ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::passwordField('confirmation', NULL, 'required aria-required="true" id="inputConfirmation" autocomplete="new-password" placeholder="' . OSCOM::getDef('entry_password_confirmation_text') . '"', 'password');
        echo OSCOM::getDef('form_required_input');
        ?>
      </div>
    </div>
  </div>

  <div class="buttonSet">
    <div class="text-right"><?php echo HTML::button(OSCOM::getDef('image_button_continue'), 'fa fa-user', null, null, 'btn-success'); ?></div>
  </div>

</div>

</form>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
