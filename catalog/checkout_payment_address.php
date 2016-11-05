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

// if the customer is not logged on, redirect them to the login page
  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot();
    OSCOM::redirect('login.php');
  }

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($_SESSION['cart']->count_contents() < 1) {
    OSCOM::redirect('shopping_cart.php');
  }

  $OSCOM_Language->loadDefinitions('checkout_payment_address');

  $error = false;
  $process = false;
  if (isset($_POST['action']) && ($_POST['action'] == 'submit') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
// process a new billing address
    if (tep_not_null($_POST['firstname']) && tep_not_null($_POST['lastname']) && tep_not_null($_POST['street_address'])) {
      $process = true;

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

          $messageStack->add('checkout_address', OSCOM::getDef('entry_gender_error'));
        }
      }

      if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
        $error = true;

        $messageStack->add('checkout_address', OSCOM::getDef('entry_first_name_error', ['min_length' => ENTRY_FIRST_NAME_MIN_LENGTH]));
      }

      if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
        $error = true;

        $messageStack->add('checkout_address', OSCOM::getDef('entry_last_name_error', ['min_length' => ENTRY_LAST_NAME_MIN_LENGTH]));
      }

      if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
        $error = true;

        $messageStack->add('checkout_address', OSCOM::getDef('entry_street_address_error', ['min_length' => ENTRY_STREET_ADDRESS_MIN_LENGTH]));
      }

      if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
        $error = true;

        $messageStack->add('checkout_address', OSCOM::getDef('entry_post_code_error', ['min_length' => ENTRY_POSTCODE_MIN_LENGTH]));
      }

      if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
        $error = true;

        $messageStack->add('checkout_address', OSCOM::getDef('entry_city_error', ['min_length' => ENTRY_CITY_MIN_LENGTH]));
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

            $messageStack->add('checkout_address', OSCOM::getDef('entry_state_error_select'));
          }
        } else {
          if (strlen($state) < ENTRY_STATE_MIN_LENGTH) {
            $error = true;

            $messageStack->add('checkout_address', OSCOM::getDef('entry_state_error', ['min_length' => ENTRY_STATE_MIN_LENGTH]));
          }
        }
      }

      if ( (is_numeric($country) == false) || ($country < 1) ) {
        $error = true;

        $messageStack->add('checkout_address', OSCOM::getDef('entry_country_error'));
      }

      if ($error == false) {
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

        $_SESSION['billto'] = $OSCOM_Db->lastInsertId();

        if (isset($_SESSION['payment'])) unset($_SESSION['payment']);

        OSCOM::redirect('checkout_payment.php');
      }
// process the selected billing destination
    } elseif (isset($_POST['address'])) {
      $reset_payment = false;
      if (isset($_SESSION['billto'])) {
        if ($_SESSION['billto'] != $_POST['address']) {
          if (isset($_SESSION['payment'])) {
            $reset_payment = true;
          }
        }
      }

      $_SESSION['billto'] = $_POST['address'];

      $Qcheck = $OSCOM_Db->prepare('select address_book_id from :table_address_book where address_book_id = :address_book_id and customers_id = :customers_id');
      $Qcheck->bindInt(':address_book_id', $_SESSION['billto']);
      $Qcheck->bindInt(':customers_id', $_SESSION['customer_id']);
      $Qcheck->execute();

      if ($Qcheck->fetch() !== false) {
        if ($reset_payment == true) unset($_SESSION['payment']);
        OSCOM::redirect('checkout_payment.php');
      } else {
        unset($_SESSION['billto']);
      }
// no addresses to select from - customer decided to keep the current assigned address
    } else {
      $_SESSION['billto'] = $_SESSION['customer_default_address_id'];

      OSCOM::redirect('checkout_payment.php');
    }
  }

// if no billing destination address was selected, use their own address as default
  if (!isset($_SESSION['billto'])) {
    $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
  }

  $breadcrumb->add(OSCOM::getDef('navbar_title_1'), OSCOM::link('checkout_payment.php'));
  $breadcrumb->add(OSCOM::getDef('navbar_title_2'), OSCOM::link('checkout_payment_address.php'));

  $addresses_count = tep_count_customer_address_book_entries();

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo OSCOM::getDef('heading_title'); ?></h1>
</div>

<?php
  if ($messageStack->size('checkout_address') > 0) {
    echo $messageStack->output('checkout_address');
  }
?>

<?php echo HTML::form('checkout_address', OSCOM::link('checkout_payment_address.php'), 'post', 'class="form-horizontal"', ['tokenize' => true]); ?>

<div class="contentContainer">

<?php
  if ($process == false) {
?>

  <h2><?php echo OSCOM::getDef('table_heading_payment_address'); ?></h2>

  <div class="contentText row">
    <div class="col-sm-8">
      <div class="alert alert-warning"><?php echo OSCOM::getDef('text_selected_payment_destination'); ?></div>
    </div>
    <div class="col-sm-4">
      <div class="panel panel-primary">
        <div class="panel-heading"><?php echo OSCOM::getDef('title_payment_address'); ?></div>

        <div class="panel-body">
          <?php echo tep_address_label($_SESSION['customer_id'], $_SESSION['billto'], true, ' ', '<br />'); ?>
        </div>
      </div>
    </div>
  </div>

  <div class="clearfix"></div>


<?php
    if ($addresses_count > 1) {
?>

  <h2><?php echo OSCOM::getDef('table_heading_address_book_entries'); ?></h2>

  <div class="alert alert-info"><?php echo OSCOM::getDef('text_select_other_payment_destination'); ?></div>

  <div class="contentText row">

<?php
      $Qab = $OSCOM_Db->prepare('select address_book_id, entry_firstname as firstname, entry_lastname as lastname, entry_company as company, entry_street_address as street_address, entry_suburb as suburb, entry_city as city, entry_postcode as postcode, entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id from :table_address_book where customers_id = :customers_id order by firstname, lastname');
      $Qab->bindInt(':customers_id', $_SESSION['customer_id']);
      $Qab->execute();

      while ($Qab->fetch()) {
        $format_id = tep_get_address_format_id($Qab->valueInt('country_id'));


?>
      <div class="col-sm-4">
        <div class="panel panel-<?php echo ($Qab->valueInt('address_book_id') == $_SESSION['billto']) ? 'primary' : 'default'; ?>">
          <div class="panel-heading"><?php echo HTML::outputProtected($Qab->value('firstname') . ' ' . $Qab->value('lastname')); ?></strong></div>
          <div class="panel-body">
            <?php echo tep_address_format($format_id, $Qab->toArray(), true, ' ', '<br />'); ?>
          </div>
          <div class="panel-footer text-center"><?php echo HTML::radioField('address', $Qab->valueInt('address_book_id'), ($Qab->valueInt('address_book_id') == $_SESSION['billto'])); ?></div>
        </div>
      </div>

<?php
      }
?>

  </div>

<?php
    }
  }

  if ($addresses_count < MAX_ADDRESS_BOOK_ENTRIES) {
?>

  <h2><?php echo OSCOM::getDef('table_heading_new_payment_address'); ?></h2>

  <div class="alert alert-info"><?php echo OSCOM::getDef('text_create_new_payment_address'); ?></div>

  <?php require('includes/content/checkout_new_address.php'); ?>

<?php
  }
?>

  <div class="buttonSet">
    <div class="text-right"><?php echo HTML::hiddenField('action', 'submit') . HTML::button(OSCOM::getDef('image_button_continue'), 'fa fa-angle-right', null, null, 'btn-success'); ?></div>
  </div>

  <div class="clearfix"></div>

  <div class="contentText">
    <div class="stepwizard">
      <div class="stepwizard-row">
        <div class="stepwizard-step">
          <button type="button" class="btn btn-default btn-circle" disabled="disabled">1</button>
          <p><?php echo OSCOM::getDef('checkout_bar_delivery'); ?></p>
        </div>
        <div class="stepwizard-step">
          <button type="button" class="btn btn-primary btn-circle">2</button>
          <p><?php echo OSCOM::getDef('checkout_bar_payment'); ?></p>
        </div>
        <div class="stepwizard-step">
          <button type="button" class="btn btn-default btn-circle" disabled="disabled">3</button>
          <p><?php echo OSCOM::getDef('checkout_bar_confirmation'); ?></p>
        </div>
      </div>
    </div>
  </div>


<?php
  if ($process == true) {
?>

  <div class="buttonSet">
    <?php echo HTML::button(OSCOM::getDef('image_button_back'), 'fa fa-angle-left', OSCOM::link('checkout_payment_address.php')); ?>
  </div>

<?php
  }
?>

</div>

</form>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
