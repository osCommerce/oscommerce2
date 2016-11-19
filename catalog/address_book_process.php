<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot();
    OSCOM::redirect('login.php');
  }

  $OSCOM_Language->loadDefinitions('address_book_process');

  if (isset($_GET['action']) && ($_GET['action'] == 'deleteconfirm') && isset($_GET['delete']) && is_numeric($_GET['delete']) && isset($_GET['formid']) && ($_GET['formid'] == md5($_SESSION['sessiontoken']))) {
    if ((int)$_GET['delete'] == $_SESSION['customer_default_address_id']) {
      $messageStack->add_session('addressbook', OSCOM::getDef('warning_primary_address_deletion'), 'warning');
    } else {
      $OSCOM_Db->delete('address_book', ['address_book_id' => (int)$_GET['delete'], 'customers_id' => (int)$_SESSION['customer_id']]);

      $messageStack->add_session('addressbook', OSCOM::getDef('success_address_book_entry_deleted'), 'success');
    }

    OSCOM::redirect('address_book.php');
  }

// error checking when updating or adding an entry
  $process = false;
  if (isset($_POST['action']) && (($_POST['action'] == 'process') || ($_POST['action'] == 'update')) && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
    $process = true;
    $error = false;

    if (ACCOUNT_GENDER == 'true') $gender = HTML::sanitize($_POST['gender']);
    if (ACCOUNT_COMPANY == 'true') $company = HTML::sanitize($_POST['company']);
    $firstname = HTML::sanitize($_POST['firstname']);
    $lastname = HTML::sanitize($_POST['lastname']);
    $street_address = HTML::sanitize($_POST['street_address']);
    if (ACCOUNT_SUBURB == 'true') $suburb = HTML::sanitize($_POST['suburb']);
    $postcode = HTML::sanitize($_POST['postcode']);
    $city = HTML::sanitize($_POST['city']);
    $country = HTML::sanitize($_POST['country']);
    if (ACCOUNT_STATE == 'true') {
      if (isset($_POST['zone_id'])) {
        $zone_id = HTML::sanitize($_POST['zone_id']);
      } else {
        $zone_id = false;
      }
      $state = HTML::sanitize($_POST['state']);
    }

    if (ACCOUNT_GENDER == 'true') {
      if ( ($gender != 'm') && ($gender != 'f') ) {
        $error = true;

        $messageStack->add('addressbook', OSCOM::getDef('entry_gender_error'));
      }
    }

    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('addressbook', OSCOM::getDef('entry_first_name_error', ['min_length' => ENTRY_FIRST_NAME_MIN_LENGTH]));
    }

    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('addressbook', OSCOM::getDef('entry_last_name_error', ['min_length' => ENTRY_LAST_NAME_MIN_LENGTH]));
    }

    if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
      $error = true;

      $messageStack->add('addressbook', OSCOM::getDef('entry_street_address_error', ['min_length' => ENTRY_STREET_ADDRESS_MIN_LENGTH]));
    }

    if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
      $error = true;

      $messageStack->add('addressbook', OSCOM::getDef('entry_post_code_error', ['min_length' => ENTRY_POSTCODE_MIN_LENGTH]));
    }

    if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
      $error = true;

      $messageStack->add('addressbook', OSCOM::getDef('entry_city_error', ['min_length' => ENTRY_CITY_MIN_LENGTH]));
    }

    if (!is_numeric($country)) {
      $error = true;

      $messageStack->add('addressbook', OSCOM::getDef('entry_country_error'));
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

          $messageStack->add('addressbook', OSCOM::getDef('entry_state_error_select'));
        }
      } else {
        if (strlen($state) < ENTRY_STATE_MIN_LENGTH) {
          $error = true;

          $messageStack->add('addressbook', OSCOM::getDef('entry_state_error', ['min_length' => ENTRY_STATE_MIN_LENGTH]));
        }
      }
    }

    if ($error == false) {
      $sql_data_array = array('entry_firstname' => $firstname,
                              'entry_lastname' => $lastname,
                              'entry_street_address' => $street_address,
                              'entry_postcode' => $postcode,
                              'entry_city' => $city,
                              'entry_country_id' => (int)$country);

      if (ACCOUNT_GENDER == 'true') $sql_data_array['entry_gender'] = $gender;
      if (ACCOUNT_COMPANY == 'true') $sql_data_array['entry_company'] = $company;
      if (ACCOUNT_SUBURB == 'true') $sql_data_array['entry_suburb'] = $suburb;
      if (ACCOUNT_STATE == 'true') {
        if ($zone_id > 0) {
          $sql_data_array['entry_zone_id'] = (int)$zone_id;
          $sql_data_array['entry_state'] = '';
        } else {
          $sql_data_array['entry_zone_id'] = '0';
          $sql_data_array['entry_state'] = $state;
        }
      }

      if ($_POST['action'] == 'update') {
        $Qcheck = $OSCOM_Db->prepare('select address_book_id from :table_address_book where address_book_id = :address_book_id and customers_id = :customers_id');
        $Qcheck->bindInt(':address_book_id', $_GET['edit']);
        $Qcheck->bindInt(':customers_id', $_SESSION['customer_id']);
        $Qcheck->execute();

        if ($Qcheck->fetch() !== false) {
          $OSCOM_Db->save('address_book', $sql_data_array, ['address_book_id' => (int)$_GET['edit'], 'customers_id' => (int)$_SESSION['customer_id']]);

// reregister session variables
          if ( (isset($_POST['primary']) && ($_POST['primary'] == 'on')) || ($_GET['edit'] == $_SESSION['customer_default_address_id']) ) {
            $_SESSION['customer_first_name'] = $firstname;
            $_SESSION['customer_country_id'] = $country;
            $_SESSION['customer_zone_id'] = (($zone_id > 0) ? (int)$zone_id : '0');
            $_SESSION['customer_default_address_id'] = (int)$_GET['edit'];

            $sql_data_array = array('customers_firstname' => $firstname,
                                    'customers_lastname' => $lastname,
                                    'customers_default_address_id' => (int)$_GET['edit']);

            if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;

            $OSCOM_Db->save('customers', $sql_data_array, ['customers_id' => (int)$_SESSION['customer_id']]);
          }

          $messageStack->add_session('addressbook', OSCOM::getDef('success_address_book_entry_updated'), 'success');
        }
      } else {
        if (tep_count_customer_address_book_entries() < MAX_ADDRESS_BOOK_ENTRIES) {
          $sql_data_array['customers_id'] = (int)$_SESSION['customer_id'];

          $OSCOM_Db->save('address_book', $sql_data_array);

          $new_address_book_id = $OSCOM_Db->lastInsertId();

// reregister session variables
          if (isset($_POST['primary']) && ($_POST['primary'] == 'on')) {
            $_SESSION['customer_first_name'] = $firstname;
            $_SESSION['customer_country_id'] = $country;
            $_SESSION['customer_zone_id'] = (($zone_id > 0) ? (int)$zone_id : '0');
            if (isset($_POST['primary']) && ($_POST['primary'] == 'on')) $_SESSION['customer_default_address_id'] = $new_address_book_id;

            $sql_data_array = array('customers_firstname' => $firstname,
                                    'customers_lastname' => $lastname);

            if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
            if (isset($_POST['primary']) && ($_POST['primary'] == 'on')) $sql_data_array['customers_default_address_id'] = $new_address_book_id;

            $OSCOM_Db->save('customers', $sql_data_array, ['customers_id' => (int)$_SESSION['customer_id']]);

            $messageStack->add_session('addressbook', OSCOM::getDef('success_address_book_entry_updated'), 'success');
          }
        }
      }

      OSCOM::redirect('address_book.php');
    }
  }

  if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $Qentry = $OSCOM_Db->prepare('select entry_gender, entry_company, entry_firstname, entry_lastname, entry_street_address, entry_suburb, entry_postcode, entry_city, entry_state, entry_zone_id, entry_country_id from :table_address_book where address_book_id = :address_book_id and customers_id = :customers_id');
    $Qentry->bindInt(':address_book_id', $_GET['edit']);
    $Qentry->bindInt(':customers_id', $_SESSION['customer_id']);
    $Qentry->execute();

    if ($Qentry->fetch() === false) {
      $messageStack->add_session('addressbook', OSCOM::getDef('error_nonexisting_address_book_entry'));

      OSCOM::redirect('address_book.php');
    }

    $entry = $Qentry->toArray();
  } elseif (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if ($_GET['delete'] == $_SESSION['customer_default_address_id']) {
      $messageStack->add_session('addressbook', OSCOM::getDef('warning_primary_address_deletion'), 'warning');

      OSCOM::redirect('address_book.php');
    } else {
      $Qcheck = $OSCOM_Db->prepare('select address_book_id from :table_address_book where address_book_id = :address_book_id and customers_id = :customers_id');
      $Qcheck->bindInt(':address_book_id', $_GET['delete']);
      $Qcheck->bindInt(':customers_id', $_SESSION['customer_id']);
      $Qcheck->execute();

      if ($Qcheck->fetch() === false) {
        $messageStack->add_session('addressbook', OSCOM::getDef('error_nonexisting_address_book_entry'));

        OSCOM::redirect('address_book.php');
      }
    }
  } else {
    $entry = array();
  }

  if (!isset($_GET['delete']) && !isset($_GET['edit'])) {
    if (tep_count_customer_address_book_entries() >= MAX_ADDRESS_BOOK_ENTRIES) {
      $messageStack->add_session('addressbook', OSCOM::getDef('error_address_book_full'));

      OSCOM::redirect('address_book.php');
    }
  }

  $breadcrumb->add(OSCOM::getDef('navbar_title_1'), OSCOM::link('account.php'));
  $breadcrumb->add(OSCOM::getDef('navbar_title_2'), OSCOM::link('address_book.php'));

  if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $breadcrumb->add(OSCOM::getDef('navbar_title_modify_entry'), OSCOM::link('address_book_process.php', 'edit=' . $_GET['edit']));
  } elseif (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $breadcrumb->add(OSCOM::getDef('navbar_title_delete_entry'), OSCOM::link('address_book_process.php', 'delete=' . $_GET['delete']));
  } else {
    $breadcrumb->add(OSCOM::getDef('navbar_title_add_entry'), OSCOM::link('address_book_process.php'));
  }

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php if (isset($_GET['edit'])) { echo OSCOM::getDef('heading_title_modify_entry'); } elseif (isset($_GET['delete'])) { echo OSCOM::getDef('heading_title_delete_entry'); } else { echo OSCOM::getDef('heading_title_add_entry'); } ?></h1>
</div>

<?php
  if ($messageStack->size('addressbook') > 0) {
    echo $messageStack->output('addressbook');
  }
?>

<?php
  if (isset($_GET['delete'])) {
?>

<div class="contentContainer">

  <div class="contentText row">
    <div class="col-sm-8">
      <div class="alert alert-warning"><?php echo OSCOM::getDef('delete_address_description'); ?></div>
    </div>
    <div class="col-sm-4">
      <div class="panel panel-danger">
        <div class="panel-heading"><?php echo OSCOM::getDef('delete_address_title'); ?></div>

        <div class="panel-body">
          <?php echo tep_address_label($_SESSION['customer_id'], $_GET['delete'], true, ' ', '<br />'); ?>
        </div>
      </div>
    </div>
  </div>

  <div class="buttonSet">
    <span class="buttonAction"><?php echo HTML::button(OSCOM::getDef('image_button_delete'), 'fa fa-trash', OSCOM::link('address_book_process.php', 'delete=' . $_GET['delete'] . '&action=deleteconfirm&formid=' . md5($_SESSION['sessiontoken'])), null, 'btn-danger'); ?></span>

    <?php echo HTML::button(OSCOM::getDef('image_button_back'), 'fa fa-angle-left', OSCOM::link('address_book.php')); ?>
  </div>

</div>

<?php
  } else {
?>

<?php echo HTML::form('addressbook', OSCOM::link('address_book_process.php', (isset($_GET['edit']) ? 'edit=' . $_GET['edit'] : '')), 'post', 'class="form-horizontal"', ['tokenize' => true]); ?>

<div class="contentContainer">

<?php include('includes/content/address_book_details.php'); ?>

<?php
    if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
?>

  <div class="buttonSet row">
    <div class="col-xs-6"><?php echo HTML::button(OSCOM::getDef('image_button_back'), 'fa fa-angle-left', OSCOM::link('address_book.php')); ?></div>
    <div class="col-xs-6 text-right"><?php echo HTML::hiddenField('action', 'update') . HTML::hiddenField('edit', $_GET['edit']) . HTML::button(OSCOM::getDef('image_button_update'), 'fa fa-refresh'); ?></div>
  </div>

<?php
    } else {
      if (sizeof($_SESSION['navigation']->snapshot) > 0) {
        $back_link = OSCOM::link($_SESSION['navigation']->snapshot['page'], tep_array_to_string($_SESSION['navigation']->snapshot['get'], array(session_name())));
      } else {
        $back_link = OSCOM::link('address_book.php');
      }
?>

  <div class="buttonSet row">
    <div class="col-xs-6"><?php echo HTML::button(OSCOM::getDef('image_button_back'), 'fa fa-angle-left', $back_link); ?></div>
    <div class="col-xs-6 text-right"><?php echo HTML::hiddenField('action', 'process') . HTML::button(OSCOM::getDef('image_button_continue'), 'fa fa-angle-right'); ?></div>
  </div>

<?php
    }
?>

</div>

</form>

<?php
  }
?>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
