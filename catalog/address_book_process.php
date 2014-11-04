<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  if (!isset($_SESSION['customer_id'])) {
    $navigation->set_snapshot();
    osc_redirect(osc_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

// needs to be included earlier to set the success message in the messageStack
  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . FILENAME_ADDRESS_BOOK_PROCESS);

  if (isset($_GET['action']) && ($_GET['action'] == 'deleteconfirm') && isset($_GET['delete']) && is_numeric($_GET['delete']) && isset($_GET['formid']) && ($_GET['formid'] == md5($_SESSION['sessiontoken']))) {
    if ((int)$_GET['delete'] == $customer_default_address_id) {
      $messageStack->add_session('addressbook', WARNING_PRIMARY_ADDRESS_DELETION, 'warning');
    } else {
      osc_db_query("delete from " . TABLE_ADDRESS_BOOK . " where address_book_id = '" . (int)$_GET['delete'] . "' and customers_id = '" . (int)$customer_id . "'");

      $messageStack->add_session('addressbook', SUCCESS_ADDRESS_BOOK_ENTRY_DELETED, 'success');
    }

    osc_redirect(osc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
  }

// error checking when updating or adding an entry
  $process = false;
  if (isset($_POST['action']) && (($_POST['action'] == 'process') || ($_POST['action'] == 'update')) && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
    $process = true;
    $error = false;

    if (ACCOUNT_GENDER == 'true') $gender = osc_db_prepare_input($_POST['gender']);
    if (ACCOUNT_COMPANY == 'true') $company = osc_db_prepare_input($_POST['company']);
    $firstname = osc_db_prepare_input($_POST['firstname']);
    $lastname = osc_db_prepare_input($_POST['lastname']);
    $street_address = osc_db_prepare_input($_POST['street_address']);
    if (ACCOUNT_SUBURB == 'true') $suburb = osc_db_prepare_input($_POST['suburb']);
    $postcode = osc_db_prepare_input($_POST['postcode']);
    $city = osc_db_prepare_input($_POST['city']);
    $country = osc_db_prepare_input($_POST['country']);
    if (ACCOUNT_STATE == 'true') {
      if (isset($_POST['zone_id'])) {
        $zone_id = osc_db_prepare_input($_POST['zone_id']);
      } else {
        $zone_id = false;
      }
      $state = osc_db_prepare_input($_POST['state']);
    }

    if (ACCOUNT_GENDER == 'true') {
      if ( ($gender != 'm') && ($gender != 'f') ) {
        $error = true;

        $messageStack->add('addressbook', ENTRY_GENDER_ERROR);
      }
    }

    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('addressbook', ENTRY_FIRST_NAME_ERROR);
    }

    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('addressbook', ENTRY_LAST_NAME_ERROR);
    }

    if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
      $error = true;

      $messageStack->add('addressbook', ENTRY_STREET_ADDRESS_ERROR);
    }

    if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
      $error = true;

      $messageStack->add('addressbook', ENTRY_POST_CODE_ERROR);
    }

    if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
      $error = true;

      $messageStack->add('addressbook', ENTRY_CITY_ERROR);
    }

    if (!is_numeric($country)) {
      $error = true;

      $messageStack->add('addressbook', ENTRY_COUNTRY_ERROR);
    }

    if (ACCOUNT_STATE == 'true') {
      $zone_id = 0;
      $check_query = osc_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "'");
      $check = osc_db_fetch_array($check_query);
      $entry_state_has_zones = ($check['total'] > 0);
      if ($entry_state_has_zones == true) {
        $zone_query = osc_db_query("select distinct zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "' and (zone_name = '" . osc_db_input($state) . "' or zone_code = '" . osc_db_input($state) . "')");
        if (osc_db_num_rows($zone_query) == 1) {
          $zone = osc_db_fetch_array($zone_query);
          $zone_id = $zone['zone_id'];
        } else {
          $error = true;

          $messageStack->add('addressbook', ENTRY_STATE_ERROR_SELECT);
        }
      } else {
        if (strlen($state) < ENTRY_STATE_MIN_LENGTH) {
          $error = true;

          $messageStack->add('addressbook', ENTRY_STATE_ERROR);
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
        $check_query = osc_db_query("select address_book_id from " . TABLE_ADDRESS_BOOK . " where address_book_id = '" . (int)$_GET['edit'] . "' and customers_id = '" . (int)$customer_id . "' limit 1");
        if (osc_db_num_rows($check_query) == 1) {
          osc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "address_book_id = '" . (int)$_GET['edit'] . "' and customers_id ='" . (int)$customer_id . "'");

// reregister session variables
          if ( (isset($_POST['primary']) && ($_POST['primary'] == 'on')) || ($_GET['edit'] == $customer_default_address_id) ) {
            $customer_first_name = $firstname;
            $customer_country_id = $country;
            $customer_zone_id = (($zone_id > 0) ? (int)$zone_id : '0');
            $customer_default_address_id = (int)$_GET['edit'];

            $sql_data_array = array('customers_firstname' => $firstname,
                                    'customers_lastname' => $lastname,
                                    'customers_default_address_id' => (int)$_GET['edit']);

            if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;

            osc_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . (int)$customer_id . "'");
          }

          $messageStack->add_session('addressbook', SUCCESS_ADDRESS_BOOK_ENTRY_UPDATED, 'success');
        }
      } else {
        if (osc_count_customer_address_book_entries() < MAX_ADDRESS_BOOK_ENTRIES) {
          $sql_data_array['customers_id'] = (int)$customer_id;
          osc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);

          $new_address_book_id = osc_db_insert_id();

// reregister session variables
          if (isset($_POST['primary']) && ($_POST['primary'] == 'on')) {
            $customer_first_name = $firstname;
            $customer_country_id = $country;
            $customer_zone_id = (($zone_id > 0) ? (int)$zone_id : '0');
            if (isset($_POST['primary']) && ($_POST['primary'] == 'on')) $customer_default_address_id = $new_address_book_id;

            $sql_data_array = array('customers_firstname' => $firstname,
                                    'customers_lastname' => $lastname);

            if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
            if (isset($_POST['primary']) && ($_POST['primary'] == 'on')) $sql_data_array['customers_default_address_id'] = $new_address_book_id;

            osc_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . (int)$customer_id . "'");

            $messageStack->add_session('addressbook', SUCCESS_ADDRESS_BOOK_ENTRY_UPDATED, 'success');
          }
        }
      }

      osc_redirect(osc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
    }
  }

  if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $entry_query = osc_db_query("select entry_gender, entry_company, entry_firstname, entry_lastname, entry_street_address, entry_suburb, entry_postcode, entry_city, entry_state, entry_zone_id, entry_country_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$_GET['edit'] . "'");

    if (!osc_db_num_rows($entry_query)) {
      $messageStack->add_session('addressbook', ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY);

      osc_redirect(osc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
    }

    $entry = osc_db_fetch_array($entry_query);
  } elseif (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if ($_GET['delete'] == $customer_default_address_id) {
      $messageStack->add_session('addressbook', WARNING_PRIMARY_ADDRESS_DELETION, 'warning');

      osc_redirect(osc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
    } else {
      $check_query = osc_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where address_book_id = '" . (int)$_GET['delete'] . "' and customers_id = '" . (int)$customer_id . "'");
      $check = osc_db_fetch_array($check_query);

      if ($check['total'] < 1) {
        $messageStack->add_session('addressbook', ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY);

        osc_redirect(osc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
      }
    }
  } else {
    $entry = array();
  }

  if (!isset($_GET['delete']) && !isset($_GET['edit'])) {
    if (osc_count_customer_address_book_entries() >= MAX_ADDRESS_BOOK_ENTRIES) {
      $messageStack->add_session('addressbook', ERROR_ADDRESS_BOOK_FULL);

      osc_redirect(osc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
    }
  }

  $breadcrumb->add(NAVBAR_TITLE_1, osc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, osc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));

  if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $breadcrumb->add(NAVBAR_TITLE_MODIFY_ENTRY, osc_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'edit=' . $_GET['edit'], 'SSL'));
  } elseif (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $breadcrumb->add(NAVBAR_TITLE_DELETE_ENTRY, osc_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'delete=' . $_GET['delete'], 'SSL'));
  } else {
    $breadcrumb->add(NAVBAR_TITLE_ADD_ENTRY, osc_href_link(FILENAME_ADDRESS_BOOK_PROCESS, '', 'SSL'));
  }

  require(DIR_WS_INCLUDES . 'template_top.php');

  if (!isset($_GET['delete'])) {
    include('includes/form_check.js.php');
  }
?>

<div class="page-header">
  <h1><?php if (isset($_GET['edit'])) { echo HEADING_TITLE_MODIFY_ENTRY; } elseif (isset($_GET['delete'])) { echo HEADING_TITLE_DELETE_ENTRY; } else { echo HEADING_TITLE_ADD_ENTRY; } ?></h1>
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
      <div class="alert alert-danger"><?php echo DELETE_ADDRESS_DESCRIPTION; ?></div>
    </div>
    <div class="col-sm-4">
      <div class="panel panel-danger">
        <div class="panel-heading"><?php echo SELECTED_ADDRESS; ?></div>

        <div class="panel-body">
          <?php echo osc_address_label($customer_id, $_GET['delete'], true, ' ', '<br />'); ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-6 text-right pull-right"><?php echo osc_draw_button(IMAGE_BUTTON_DELETE, 'glyphicon glyphicon-trash', osc_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'delete=' . $_GET['delete'] . '&action=deleteconfirm&formid=' . md5($_SESSION['sessiontoken']), 'SSL'), 'primary', null, 'btn-danger'); ?></div>
    <div class="col-sm-6"><?php echo osc_draw_button(IMAGE_BUTTON_BACK, 'glyphicon glyphicon-chevron-left', osc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL')); ?></div>
  </div>

</div>

<?php
  } else {
?>

<?php echo osc_draw_form('addressbook', osc_href_link(FILENAME_ADDRESS_BOOK_PROCESS, (isset($_GET['edit']) ? 'edit=' . $_GET['edit'] : ''), 'SSL'), 'post', 'class="form-horizontal" role="form"', true); ?>

<div class="contentContainer">

<?php
    if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
?>
  <div class="row">
    <div class="col-sm-8">
      <div class="alert alert-warning"><?php echo EDIT_ADDRESS_DESCRIPTION; ?></div>
    </div>
    <div class="col-sm-4">
      <div class="panel panel-warning">
        <div class="panel-heading"><?php echo SELECTED_ADDRESS; ?></div>

        <div class="panel-body">
          <?php echo osc_address_label($customer_id, (int)$_GET['edit'], true, ' ', '<br />'); ?>
        </div>
      </div>
    </div>
  </div>
<?php
}
?>

<?php include(DIR_WS_MODULES . 'address_book_details.php'); ?>

<?php
    if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
?>

  <div class="row">
    <div class="col-sm-6 text-right pull-right"><?php echo osc_draw_hidden_field('action', 'update') . osc_draw_hidden_field('edit', $_GET['edit']) . osc_draw_button(IMAGE_BUTTON_UPDATE, 'glyphicon glyphicon-refresh', null, 'primary', null, 'btn-success'); ?></div>
    <div class="col-sm-6"><?php echo osc_draw_button(IMAGE_BUTTON_BACK, 'glyphicon glyphicon-chevron-left', osc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL')); ?></div>
  </div>

<?php
    } else {
      if (sizeof($navigation->snapshot) > 0) {
        $back_link = osc_href_link($navigation->snapshot['page'], osc_array_to_string($navigation->snapshot['get'], array(session_name())), $navigation->snapshot['mode']);
      } else {
        $back_link = osc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL');
      }
?>

  <div class="row">
    <div class="col-sm-6 text-right pull-right"><?php echo osc_draw_hidden_field('action', 'process') . osc_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', null, null, null, 'btn-success'); ?></div>
    <div class="col-sm-6"><?php echo osc_draw_button(IMAGE_BUTTON_BACK, 'glyphicon glyphicon-chevron-left', $back_link); ?></div>
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
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
