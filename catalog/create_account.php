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
  use OSC\OM\Registry;

  require('includes/application_top.php');

// needs to be included earlier to set the success message in the messageStack
  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/create_account.php');

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

        $messageStack->add('create_account', ENTRY_GENDER_ERROR);
      }
    }

    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', ENTRY_FIRST_NAME_ERROR);
    }

    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', ENTRY_LAST_NAME_ERROR);
    }

    if (ACCOUNT_DOB == 'true') {
      if ((strlen($dob) < ENTRY_DOB_MIN_LENGTH) || (!empty($dob) && (!is_numeric(tep_date_raw($dob)) || !@checkdate(substr(tep_date_raw($dob), 4, 2), substr(tep_date_raw($dob), 6, 2), substr(tep_date_raw($dob), 0, 4))))) {
        $error = true;

        $messageStack->add('create_account', ENTRY_DATE_OF_BIRTH_ERROR);
      }
    }

    if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
      $error = true;
      $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_ERROR);
    } elseif (tep_validate_email($email_address) == false) {
      $error = true;

      $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
    } else {
      $Qcheck = $OSCOM_Db->prepare('select customers_id from :table_customers where customers_email_address = :customers_email_address limit 1');
      $Qcheck->bindValue(':customers_email_address', $email_address);
      $Qcheck->execute();

      if ($Qcheck->fetch() !== false) {
        $error = true;

        $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
      }
    }

    if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', ENTRY_STREET_ADDRESS_ERROR);
    }

    if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', ENTRY_POST_CODE_ERROR);
    }

    if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', ENTRY_CITY_ERROR);
    }

    if (is_numeric($country) == false) {
      $error = true;

      $messageStack->add('create_account', ENTRY_COUNTRY_ERROR);
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

          $messageStack->add('create_account', ENTRY_STATE_ERROR_SELECT);
        }
      } else {
        if (strlen($state) < ENTRY_STATE_MIN_LENGTH) {
          $error = true;

          $messageStack->add('create_account', ENTRY_STATE_ERROR);
        }
      }
    }

    if (strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', ENTRY_TELEPHONE_NUMBER_ERROR);
    }


    if (strlen($password) < ENTRY_PASSWORD_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', ENTRY_PASSWORD_ERROR);
    } elseif ($password != $confirmation) {
      $error = true;

      $messageStack->add('create_account', ENTRY_PASSWORD_ERROR_NOT_MATCHING);
    }

    if ($error == false) {
      $sql_data_array = array('customers_firstname' => $firstname,
                              'customers_lastname' => $lastname,
                              'customers_email_address' => $email_address,
                              'customers_telephone' => $telephone,
                              'customers_fax' => $fax,
                              'customers_newsletter' => $newsletter,
                              'customers_password' => tep_encrypt_password($password));

      if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
      if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = tep_date_raw($dob);

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
           $email_text = sprintf(EMAIL_GREET_MR, $lastname);
         } else {
           $email_text = sprintf(EMAIL_GREET_MS, $lastname);
         }
      } else {
        $email_text = sprintf(EMAIL_GREET_NONE, $firstname);
      }

      $email_text .= EMAIL_WELCOME . EMAIL_TEXT . EMAIL_CONTACT . EMAIL_WARNING;
      tep_mail($name, $email_address, EMAIL_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

      OSCOM::redirect('create_account_success.php', '', 'SSL');
    }
  }

  $breadcrumb->add(NAVBAR_TITLE, OSCOM::link('create_account.php', '', 'SSL'));

  require('includes/template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php
  if ($messageStack->size('create_account') > 0) {
    echo $messageStack->output('create_account');
  }
?>

<div class="alert alert-warning">
  <?php echo sprintf(TEXT_ORIGIN_LOGIN, OSCOM::link('login.php', tep_get_all_get_params(), 'SSL')); ?><span class="text-danger pull-right text-right"><?php echo FORM_REQUIRED_INFORMATION; ?></span>
</div>

<?php echo HTML::form('create_account', OSCOM::link('create_account.php', '', 'SSL'), 'post', 'class="form-horizontal"', ['tokenize' => true, 'action' => 'process']); ?>

<div class="contentContainer">

  <h2><?php echo CATEGORY_PERSONAL; ?></h2>
  <div class="contentText">

<?php
  if (ACCOUNT_GENDER == 'true') {
?>
    <div class="form-group has-feedback">
      <label class="control-label col-sm-3"><?php echo ENTRY_GENDER; ?></label>
      <div class="col-sm-9">
        <label class="radio-inline">
          <?php echo HTML::radioField('gender', 'm', NULL, 'required aria-required="true" aria-describedby="atGender"') . ' ' . MALE; ?>
        </label>
        <label class="radio-inline">
          <?php echo HTML::radioField('gender', 'f') . ' ' . FEMALE; ?>
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
        <?php
        echo HTML::inputField('firstname', NULL, 'required aria-required="true" id="inputFirstName" placeholder="' . ENTRY_FIRST_NAME_TEXT . '"');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputLastName" class="control-label col-sm-3"><?php echo ENTRY_LAST_NAME; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('lastname', NULL, 'required aria-required="true" id="inputLastName" placeholder="' . ENTRY_LAST_NAME_TEXT . '"');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
<?php
  if (ACCOUNT_DOB == 'true') {
?>
    <div class="form-group has-feedback">
      <label for="dob" class="control-label col-sm-3"><?php echo ENTRY_DATE_OF_BIRTH; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('dob', '', 'required aria-required="true" id="dob" placeholder="' . ENTRY_DATE_OF_BIRTH_TEXT . '"');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
<?php
  }
?>
    <div class="form-group has-feedback">
      <label for="inputEmail" class="control-label col-sm-3"><?php echo ENTRY_EMAIL_ADDRESS; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('email_address', NULL, 'required aria-required="true" id="inputEmail" placeholder="' . ENTRY_EMAIL_ADDRESS_TEXT . '"', 'email');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
  </div>
<?php
  if (ACCOUNT_COMPANY == 'true') {
?>

  <h2><?php echo CATEGORY_COMPANY; ?></h2>

  <div class="contentText">
    <div class="form-group">
      <label for="inputCompany" class="control-label col-sm-3"><?php echo ENTRY_COMPANY; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('company', NULL, 'id="inputCompany" placeholder="' . ENTRY_COMPANY_TEXT . '"');
        ?>
      </div>
    </div>
  </div>

<?php
  }
?>

  <h2><?php echo CATEGORY_ADDRESS; ?></h2>

  <div class="contentText">
    <div class="form-group has-feedback">
      <label for="inputStreet" class="control-label col-sm-3"><?php echo ENTRY_STREET_ADDRESS; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('street_address', NULL, 'required aria-required="true" id="inputStreet" placeholder="' . ENTRY_STREET_ADDRESS_TEXT . '"');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>

<?php
  if (ACCOUNT_SUBURB == 'true') {
?>
    <div class="form-group">
      <label for="inputSuburb" class="control-label col-sm-3"><?php echo ENTRY_SUBURB; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('suburb', NULL, 'id="inputSuburb" placeholder="' . ENTRY_SUBURB_TEXT . '"');
        ?>
      </div>
    </div>
<?php
  }
?>
    <div class="form-group has-feedback">
      <label for="inputCity" class="control-label col-sm-3"><?php echo ENTRY_CITY; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('city', NULL, 'required aria-required="true" id="inputCity" placeholder="' . ENTRY_CITY_TEXT . '"');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputZip" class="control-label col-sm-3"><?php echo ENTRY_POST_CODE; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('postcode', NULL, 'required aria-required="true" id="inputZip" placeholder="' . ENTRY_POST_CODE_TEXT . '"');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
<?php
  if (ACCOUNT_STATE == 'true') {
?>
    <div class="form-group has-feedback">
      <label for="inputState" class="control-label col-sm-3"><?php echo ENTRY_STATE; ?></label>
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
            echo FORM_REQUIRED_INPUT;
            if (tep_not_null(ENTRY_STATE_TEXT)) echo '<span id="atState" class="help-block">' . ENTRY_STATE_TEXT . '</span>';
          } else {
            echo HTML::inputField('state', NULL, 'id="inputState" placeholder="' . ENTRY_STATE_TEXT . '"');
            echo FORM_REQUIRED_INPUT;
          }
        } else {
          echo HTML::inputField('state', NULL, 'id="inputState" placeholder="' . ENTRY_STATE_TEXT . '"');
          echo FORM_REQUIRED_INPUT;
        }
        ?>
      </div>
    </div>
<?php
  }
?>
    <div class="form-group has-feedback">
      <label for="inputCountry" class="control-label col-sm-3"><?php echo ENTRY_COUNTRY; ?></label>
      <div class="col-sm-9">
        <?php
        echo tep_get_country_list('country', NULL, 'required aria-required="true" aria-describedby="atCountry" id="inputCountry"');
        echo FORM_REQUIRED_INPUT;
        if (tep_not_null(ENTRY_COUNTRY_TEXT)) echo '<span id="atCountry" class="help-block">' . ENTRY_COUNTRY_TEXT . '</span>';
        ?>
      </div>
    </div>
  </div>

  <h2><?php echo CATEGORY_CONTACT; ?></h2>

  <div class="contentText">
    <div class="form-group has-feedback">
      <label for="inputTelephone" class="control-label col-sm-3"><?php echo ENTRY_TELEPHONE_NUMBER; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('telephone', NULL, 'required aria-required="true" id="inputTelephone" placeholder="' . ENTRY_TELEPHONE_NUMBER_TEXT . '"', 'tel');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
    <div class="form-group">
      <label for="inputFax" class="control-label col-sm-3"><?php echo ENTRY_FAX_NUMBER; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::inputField('fax', '', 'id="inputFax" placeholder="' . ENTRY_FAX_NUMBER_TEXT . '"', 'tel');
        ?>
      </div>
    </div>
    <div class="form-group">
      <label for="inputNewsletter" class="control-label col-sm-3"><?php echo ENTRY_NEWSLETTER; ?></label>
      <div class="col-sm-9">
        <div class="checkbox">
          <label>
            <?php echo HTML::checkboxField('newsletter', '1', NULL, 'id="inputNewsletter"'); ?>
            <?php if (tep_not_null(ENTRY_NEWSLETTER_TEXT)) echo ENTRY_NEWSLETTER_TEXT; ?>
          </label>
        </div>
      </div>
    </div>

  </div>

  <h2><?php echo CATEGORY_PASSWORD; ?></h2>

  <div class="contentText">
    <div class="form-group has-feedback">
      <label for="inputPassword" class="control-label col-sm-3"><?php echo ENTRY_PASSWORD; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::passwordField('password', NULL, 'required aria-required="true" id="inputPassword" autocomplete="new-password" placeholder="' . ENTRY_PASSWORD_TEXT . '"', 'password');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputConfirmation" class="control-label col-sm-3"><?php echo ENTRY_PASSWORD_CONFIRMATION; ?></label>
      <div class="col-sm-9">
        <?php
        echo HTML::passwordField('confirmation', NULL, 'required aria-required="true" id="inputConfirmation" autocomplete="new-password" placeholder="' . ENTRY_PASSWORD_CONFIRMATION_TEXT . '"', 'password');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
  </div>

  <div class="buttonSet">
    <div class="text-right"><?php echo HTML::button(IMAGE_BUTTON_CONTINUE, 'fa fa-user', null, 'primary', null, 'btn-success'); ?></div>
  </div>

</div>

</form>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
