<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  $error = false;
  $processed = false;

  if (tep_not_null($action)) {
    switch ($action) {
      case 'update':
        $customers_id = HTML::sanitize($_GET['cID']);
        $customers_firstname = HTML::sanitize($_POST['customers_firstname']);
        $customers_lastname = HTML::sanitize($_POST['customers_lastname']);
        $customers_email_address = HTML::sanitize($_POST['customers_email_address']);
        $customers_telephone = HTML::sanitize($_POST['customers_telephone']);
        $customers_fax = HTML::sanitize($_POST['customers_fax']);
        $customers_newsletter = HTML::sanitize($_POST['customers_newsletter']);

        if (ACCOUNT_GENDER == 'true') $customers_gender = HTML::sanitize($_POST['customers_gender']);
        if (ACCOUNT_DOB == 'true') $customers_dob = HTML::sanitize($_POST['customers_dob']);

        $customers_default_address_id = HTML::sanitize($_POST['customers_default_address_id']);
        $entry_street_address = HTML::sanitize($_POST['entry_street_address']);
        $entry_suburb = HTML::sanitize($_POST['entry_suburb']);
        $entry_postcode = HTML::sanitize($_POST['entry_postcode']);
        $entry_city = HTML::sanitize($_POST['entry_city']);
        $entry_country_id = HTML::sanitize($_POST['entry_country_id']);

        $entry_company = HTML::sanitize($_POST['entry_company']);
        $entry_state = HTML::sanitize($_POST['entry_state']);
        if (isset($_POST['entry_zone_id'])) $entry_zone_id = HTML::sanitize($_POST['entry_zone_id']);

        if (ACCOUNT_GENDER == 'true') {
          if (($customers_gender != 'm') && ($customers_gender != 'f')) {
            $error = true;
            $entry_gender_error = true;
          } else {
            $entry_gender_error = false;
          }
        }

        if (strlen($customers_firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
          $error = true;
          $entry_firstname_error = true;
        } else {
          $entry_firstname_error = false;
        }

        if (strlen($customers_lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
          $error = true;
          $entry_lastname_error = true;
        } else {
          $entry_lastname_error = false;
        }

        if (ACCOUNT_DOB == 'true') {
          if ((strlen($customers_dob) >= ENTRY_DOB_MIN_LENGTH) && ((is_numeric(tep_date_raw($customers_dob)) && @checkdate(substr(tep_date_raw($customers_dob), 4, 2), substr(tep_date_raw($customers_dob), 6, 2), substr(tep_date_raw($customers_dob), 0, 4))) || empty($customers_dob))) {
            $entry_date_of_birth_error = false;
          } else {
            $error = true;
            $entry_date_of_birth_error = true;
          }
        }

        $entry_email_address_error = false;

        if (!tep_validate_email($customers_email_address)) {
          $error = true;
          $entry_email_address_check_error = true;
        } else {
          $entry_email_address_check_error = false;
        }

        if (strlen($entry_street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
          $error = true;
          $entry_street_address_error = true;
        } else {
          $entry_street_address_error = false;
        }

        if (strlen($entry_postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
          $error = true;
          $entry_post_code_error = true;
        } else {
          $entry_post_code_error = false;
        }

        if (strlen($entry_city) < ENTRY_CITY_MIN_LENGTH) {
          $error = true;
          $entry_city_error = true;
        } else {
          $entry_city_error = false;
        }

        if ($entry_country_id == false) {
          $error = true;
          $entry_country_error = true;
        } else {
          $entry_country_error = false;
        }

        if (ACCOUNT_STATE == 'true') {
          if ($entry_country_error == true) {
            $entry_state_error = true;
          } else {
            $zone_id = 0;
            $entry_state_error = false;
            $Qcheck = $OSCOM_Db->get('zones', 'zone_country_id', ['zone_country_id' => (int)$entry_country_id]);
            $entry_state_has_zones = $Qcheck->fetch() !== false;
            if ($entry_state_has_zones == true) {
              $Qzone = $OSCOM_Db->get('zones', 'zone_id', [
                'zone_country_id' => (int)$entry_country_id,
                'zone_name' => $entry_state
              ]);

              if ($Qzone->fetch() !== false) {
                $entry_zone_id = $Qzone->valueInt('zone_id');
              } else {
                $error = true;
                $entry_state_error = true;
              }
            } else {
              if (strlen($entry_state) < ENTRY_STATE_MIN_LENGTH) {
                $error = true;
                $entry_state_error = true;
              }
            }
          }
        }

        if (strlen($customers_telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
          $error = true;
          $entry_telephone_error = true;
        } else {
          $entry_telephone_error = false;
        }

        $Qcheck = $OSCOM_Db->get('customers', 'customers_email_address', [
          'customers_email_address' => $customers_email_address,
          'customers_id' => [
            'op' => '!=',
            'val' => (int)$customers_id
          ]
        ]);

        if ($Qcheck->fetch() !== false) {
          $error = true;
          $entry_email_address_exists = true;
        } else {
          $entry_email_address_exists = false;
        }

        if ($error == false) {
          $sql_data_array = array('customers_firstname' => $customers_firstname,
                                  'customers_lastname' => $customers_lastname,
                                  'customers_email_address' => $customers_email_address,
                                  'customers_telephone' => $customers_telephone,
                                  'customers_fax' => $customers_fax,
                                  'customers_newsletter' => $customers_newsletter);

          if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $customers_gender;
          if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = tep_date_raw($customers_dob);

          $OSCOM_Db->save('customers', $sql_data_array, ['customers_id' => (int)$customers_id]);

          $OSCOM_Db->save('customers_info', ['customers_info_date_account_last_modified' => 'now()'], ['customers_info_id' => (int)$customers_id]);

          if ($entry_zone_id > 0) $entry_state = '';

          $sql_data_array = array('entry_firstname' => $customers_firstname,
                                  'entry_lastname' => $customers_lastname,
                                  'entry_street_address' => $entry_street_address,
                                  'entry_postcode' => $entry_postcode,
                                  'entry_city' => $entry_city,
                                  'entry_country_id' => $entry_country_id);

          if (ACCOUNT_COMPANY == 'true') $sql_data_array['entry_company'] = $entry_company;
          if (ACCOUNT_SUBURB == 'true') $sql_data_array['entry_suburb'] = $entry_suburb;

          if (ACCOUNT_STATE == 'true') {
            if ($entry_zone_id > 0) {
              $sql_data_array['entry_zone_id'] = $entry_zone_id;
              $sql_data_array['entry_state'] = '';
            } else {
              $sql_data_array['entry_zone_id'] = '0';
              $sql_data_array['entry_state'] = $entry_state;
            }
          }

          $OSCOM_Db->save('address_book', $sql_data_array, [
            'customers_id' => (int)$customers_id,
            'address_book_id' => (int)$customers_default_address_id
          ]);

          OSCOM::redirect(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')) . 'cID=' . $customers_id);
        } else if ($error == true) {
          $cInfo = new objectInfo($_POST);
          $processed = true;
        }

        break;
      case 'deleteconfirm':
        $customers_id = HTML::sanitize($_GET['cID']);

        if (isset($_POST['delete_reviews']) && ($_POST['delete_reviews'] == 'on')) {
          $Qreviews = $OSCOM_Db->get('reviews', 'reviews_id', ['customers_id' => (int)$customers_id]);

          while ($Qreviews->fetch()) {
            $OSCOM_Db->delete('reviews_description', ['reviews_id' => (int)$reviews['reviews_id']]);
          }

          $OSCOM_Db->delete('reviews', ['customers_id' => (int)$customers_id]);
        } else {
          $OSCOM_Db->save('reviews', ['customers_id' => 'null'], ['customers_id' => (int)$customers_id]);
        }

        $OSCOM_Db->delete('address_book', ['customers_id' => (int)$customers_id]);
        $OSCOM_Db->delete('customers', ['customers_id' => (int)$customers_id]);
        $OSCOM_Db->delete('customers_info', ['customers_info_id' => (int)$customers_id]);
        $OSCOM_Db->delete('customers_basket', ['customers_id' => (int)$customers_id]);
        $OSCOM_Db->delete('customers_basket_attributes', ['customers_id' => (int)$customers_id]);
        $OSCOM_Db->delete('whos_online', ['customer_id' => (int)$customers_id]);

        OSCOM::redirect(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')));
        break;
      default:
        if ($action != 'confirm') {
          $Qcustomer = $OSCOM_Db->prepare('select c.customers_id, c.customers_gender, c.customers_firstname, c.customers_lastname, c.customers_dob, c.customers_email_address, a.entry_company, a.entry_street_address, a.entry_suburb, a.entry_postcode, a.entry_city, a.entry_state, a.entry_zone_id, a.entry_country_id, c.customers_telephone, c.customers_fax, c.customers_newsletter, c.customers_default_address_id from :table_customers c left join :table_address_book a on c.customers_default_address_id = a.address_book_id where a.customers_id = c.customers_id and c.customers_id = :customers_id');
          $Qcustomer->bindInt(':customers_id', $_GET['cID']);
          $Qcustomer->execute();

          $cInfo = new objectInfo($Qcustomer->toArray());
        }
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');

  if ($action == 'edit' || $action == 'update') {
?>
<script type="text/javascript"><!--

function check_form() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";

  var customers_firstname = document.customers.customers_firstname.value;
  var customers_lastname = document.customers.customers_lastname.value;
<?php if (ACCOUNT_COMPANY == 'true') echo 'var entry_company = document.customers.entry_company.value;' . "\n"; ?>
<?php if (ACCOUNT_DOB == 'true') echo 'var customers_dob = document.customers.customers_dob.value;' . "\n"; ?>
  var customers_email_address = document.customers.customers_email_address.value;
  var entry_street_address = document.customers.entry_street_address.value;
  var entry_postcode = document.customers.entry_postcode.value;
  var entry_city = document.customers.entry_city.value;
  var customers_telephone = document.customers.customers_telephone.value;

<?php if (ACCOUNT_GENDER == 'true') { ?>
  if (document.customers.customers_gender[0].checked || document.customers.customers_gender[1].checked) {
  } else {
    error_message = error_message + "<?php echo JS_GENDER; ?>";
    error = 1;
  }
<?php } ?>

  if (customers_firstname.length < <?php echo ENTRY_FIRST_NAME_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_FIRST_NAME; ?>";
    error = 1;
  }

  if (customers_lastname.length < <?php echo ENTRY_LAST_NAME_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_LAST_NAME; ?>";
    error = 1;
  }

<?php if (ACCOUNT_DOB == 'true') { ?>
  if (customers_dob.length < <?php echo ENTRY_DOB_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_DOB; ?>";
    error = 1;
  }
<?php } ?>

  if (entry_street_address.length < <?php echo ENTRY_STREET_ADDRESS_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_ADDRESS; ?>";
    error = 1;
  }

  if (entry_postcode.length < <?php echo ENTRY_POSTCODE_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_POST_CODE; ?>";
    error = 1;
  }

  if (entry_city.length < <?php echo ENTRY_CITY_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_CITY; ?>";
    error = 1;
  }

<?php
  if (ACCOUNT_STATE == 'true') {
?>
  if (document.customers.elements['entry_state'].type != "hidden") {
    if (document.customers.entry_state.value.length < <?php echo ENTRY_STATE_MIN_LENGTH; ?>) {
       error_message = error_message + "<?php echo JS_STATE; ?>";
       error = 1;
    }
  }
<?php
  }
?>

  if (document.customers.elements['entry_country_id'].type != "hidden") {
    if (document.customers.entry_country_id.value == 0) {
      error_message = error_message + "<?php echo JS_COUNTRY; ?>";
      error = 1;
    }
  }

  if (customers_telephone.length < <?php echo ENTRY_TELEPHONE_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_TELEPHONE; ?>";
    error = 1;
  }

  if (error == 1) {
    alert(error_message);
    return false;
  } else {
    return true;
  }
}
//--></script>
<?php
  }
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  if ($action == 'edit' || $action == 'update') {
    $newsletter_array = array(array('id' => '1', 'text' => ENTRY_NEWSLETTER_YES),
                              array('id' => '0', 'text' => ENTRY_NEWSLETTER_NO));
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr><?php echo HTML::form('customers', OSCOM::link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('action')) . 'action=update'), 'post', 'onsubmit="return check_form();"') . HTML::hiddenField('customers_default_address_id', $cInfo->customers_default_address_id); ?>
        <td class="formAreaTitle"><?php echo CATEGORY_PERSONAL; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
<?php
    if (ACCOUNT_GENDER == 'true') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_GENDER; ?></td>
            <td class="main">
<?php
    if ($error == true) {
      if ($entry_gender_error == true) {
        echo HTML::radioField('customers_gender', 'm', $cInfo->customers_gender == 'm') . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . HTML::radioField('customers_gender', 'f', $cInfo->customers_gender == 'f') . '&nbsp;&nbsp;' . FEMALE . '&nbsp;' . ENTRY_GENDER_ERROR;
      } else {
        echo ($cInfo->customers_gender == 'm') ? MALE : FEMALE;
        echo HTML::hiddenField('customers_gender');
      }
    } else {
      echo HTML::radioField('customers_gender', 'm', $cInfo->customers_gender == 'm') . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . HTML::radioField('customers_gender', 'f', $cInfo->customers_gender == 'f') . '&nbsp;&nbsp;' . FEMALE;
    }
?></td>
          </tr>
<?php
    }
?>
          <tr>
            <td class="main"><?php echo ENTRY_FIRST_NAME; ?></td>
            <td class="main">
<?php
  if ($error == true) {
    if ($entry_firstname_error == true) {
      echo HTML::inputField('customers_firstname', $cInfo->customers_firstname, 'maxlength="32"') . '&nbsp;' . ENTRY_FIRST_NAME_ERROR;
    } else {
      echo $cInfo->customers_firstname . HTML::hiddenField('customers_firstname');
    }
  } else {
    echo HTML::inputField('customers_firstname', $cInfo->customers_firstname, 'maxlength="32"') . TEXT_FIELD_REQUIRED;
  }
?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_LAST_NAME; ?></td>
            <td class="main">
<?php
  if ($error == true) {
    if ($entry_lastname_error == true) {
      echo HTML::inputField('customers_lastname', $cInfo->customers_lastname, 'maxlength="32"') . '&nbsp;' . ENTRY_LAST_NAME_ERROR;
    } else {
      echo $cInfo->customers_lastname . HTML::hiddenField('customers_lastname');
    }
  } else {
    echo HTML::inputField('customers_lastname', $cInfo->customers_lastname, 'maxlength="32"') . TEXT_FIELD_REQUIRED;
  }
?></td>
          </tr>
<?php
    if (ACCOUNT_DOB == 'true') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_DATE_OF_BIRTH; ?></td>
            <td class="main">
<?php
    if ($error == true) {
      if ($entry_date_of_birth_error == true) {
        echo HTML::inputField('customers_dob', tep_date_short($cInfo->customers_dob), 'maxlength="10"') . '&nbsp;' . ENTRY_DATE_OF_BIRTH_ERROR;
      } else {
        echo $cInfo->customers_dob . HTML::hiddenField('customers_dob');
      }
    } else {
      echo HTML::inputField('customers_dob', tep_date_short($cInfo->customers_dob), 'maxlength="10" id="customers_dob"') . TEXT_FIELD_REQUIRED;
    }
?>
              <script type="text/javascript">$('#customers_dob').datepicker({dateFormat: '<?php echo JQUERY_DATEPICKER_FORMAT; ?>', changeMonth: true, changeYear: true, yearRange: '-100:+0'});</script>
            </td>
          </tr>
<?php
    }
?>
          <tr>
            <td class="main"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
            <td class="main">
<?php
  if ($error == true) {
    if ($entry_email_address_error == true) {
      echo HTML::inputField('customers_email_address', $cInfo->customers_email_address, 'maxlength="96"') . '&nbsp;' . ENTRY_EMAIL_ADDRESS_ERROR;
    } elseif ($entry_email_address_check_error == true) {
      echo HTML::inputField('customers_email_address', $cInfo->customers_email_address, 'maxlength="96"') . '&nbsp;' . ENTRY_EMAIL_ADDRESS_CHECK_ERROR;
    } elseif ($entry_email_address_exists == true) {
      echo HTML::inputField('customers_email_address', $cInfo->customers_email_address, 'maxlength="96"') . '&nbsp;' . ENTRY_EMAIL_ADDRESS_ERROR_EXISTS;
    } else {
      echo $customers_email_address . HTML::hiddenField('customers_email_address');
    }
  } else {
    echo HTML::inputField('customers_email_address', $cInfo->customers_email_address, 'maxlength="96"') . TEXT_FIELD_REQUIRED;
  }
?></td>
          </tr>
        </table></td>
      </tr>
<?php
    if (ACCOUNT_COMPANY == 'true') {
?>
      <tr>
        <td class="formAreaTitle"><?php echo CATEGORY_COMPANY; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php echo ENTRY_COMPANY; ?></td>
            <td class="main">
<?php
    if ($error == true) {
      echo $cInfo->entry_company . HTML::hiddenField('entry_company');
    } else {
      echo HTML::inputField('entry_company', $cInfo->entry_company, 'maxlength="32"');
    }
?></td>
          </tr>
        </table></td>
      </tr>
<?php
    }
?>
      <tr>
        <td class="formAreaTitle"><?php echo CATEGORY_ADDRESS; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php echo ENTRY_STREET_ADDRESS; ?></td>
            <td class="main">
<?php
  if ($error == true) {
    if ($entry_street_address_error == true) {
      echo HTML::inputField('entry_street_address', $cInfo->entry_street_address, 'maxlength="64"') . '&nbsp;' . ENTRY_STREET_ADDRESS_ERROR;
    } else {
      echo $cInfo->entry_street_address . HTML::hiddenField('entry_street_address');
    }
  } else {
    echo HTML::inputField('entry_street_address', $cInfo->entry_street_address, 'maxlength="64"') . TEXT_FIELD_REQUIRED;
  }
?></td>
          </tr>
<?php
    if (ACCOUNT_SUBURB == 'true') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_SUBURB; ?></td>
            <td class="main">
<?php
    if ($error == true) {
      echo $cInfo->entry_suburb . HTML::hiddenField('entry_suburb');
    } else {
      echo HTML::inputField('entry_suburb', $cInfo->entry_suburb, 'maxlength="32"');
    }
?></td>
          </tr>
<?php
    }
?>
          <tr>
            <td class="main"><?php echo ENTRY_POST_CODE; ?></td>
            <td class="main">
<?php
  if ($error == true) {
    if ($entry_post_code_error == true) {
      echo HTML::inputField('entry_postcode', $cInfo->entry_postcode, 'maxlength="8"') . '&nbsp;' . ENTRY_POST_CODE_ERROR;
    } else {
      echo $cInfo->entry_postcode . HTML::hiddenField('entry_postcode');
    }
  } else {
    echo HTML::inputField('entry_postcode', $cInfo->entry_postcode, 'maxlength="8"') . TEXT_FIELD_REQUIRED;
  }
?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CITY; ?></td>
            <td class="main">
<?php
  if ($error == true) {
    if ($entry_city_error == true) {
      echo HTML::inputField('entry_city', $cInfo->entry_city, 'maxlength="32"') . '&nbsp;' . ENTRY_CITY_ERROR;
    } else {
      echo $cInfo->entry_city . HTML::hiddenField('entry_city');
    }
  } else {
    echo HTML::inputField('entry_city', $cInfo->entry_city, 'maxlength="32"') . TEXT_FIELD_REQUIRED;
  }
?></td>
          </tr>
<?php
    if (ACCOUNT_STATE == 'true') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_STATE; ?></td>
            <td class="main">
<?php
    if ($error == true) {
      if ($entry_state_error == true) {
        if ($entry_state_has_zones == true) {
          $zones_array = array();
          $Qzones = $OSCOM_Db->get('zones', 'zone_name', ['zone_country_id' => $cInfo->entry_country_id], 'zone_name');
          while ($Qzones->fetch()) {
            $zones_array[] = [
              'id' => $Qzones->value('zone_name'),
              'text' => $Qzones->value('zone_name')
            ];
          }
          echo HTML::selectField('entry_state', $zones_array) . '&nbsp;' . ENTRY_STATE_ERROR;
        } else {
          echo HTML::inputField('entry_state', tep_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state)) . '&nbsp;' . ENTRY_STATE_ERROR;
        }
      } else {
        echo tep_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state) . HTML::hiddenField('entry_zone_id') . HTML::hiddenField('entry_state');
      }
    } else {
      echo HTML::inputField('entry_state', tep_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state));
    }

?></td>
         </tr>
<?php
    }
?>
          <tr>
            <td class="main"><?php echo ENTRY_COUNTRY; ?></td>
            <td class="main">
<?php
  if ($error == true) {
    if ($entry_country_error == true) {
      echo HTML::selectField('entry_country_id', tep_get_countries(), $cInfo->entry_country_id) . '&nbsp;' . ENTRY_COUNTRY_ERROR;
    } else {
      echo tep_get_country_name($cInfo->entry_country_id) . HTML::hiddenField('entry_country_id');
    }
  } else {
    echo HTML::selectField('entry_country_id', tep_get_countries(), $cInfo->entry_country_id);
  }
?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td class="formAreaTitle"><?php echo CATEGORY_CONTACT; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php echo ENTRY_TELEPHONE_NUMBER; ?></td>
            <td class="main">
<?php
  if ($error == true) {
    if ($entry_telephone_error == true) {
      echo HTML::inputField('customers_telephone', $cInfo->customers_telephone, 'maxlength="32"') . '&nbsp;' . ENTRY_TELEPHONE_NUMBER_ERROR;
    } else {
      echo $cInfo->customers_telephone . HTML::hiddenField('customers_telephone');
    }
  } else {
    echo HTML::inputField('customers_telephone', $cInfo->customers_telephone, 'maxlength="32"') . TEXT_FIELD_REQUIRED;
  }
?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_FAX_NUMBER; ?></td>
            <td class="main">
<?php
  if ($processed == true) {
    echo $cInfo->customers_fax . HTML::hiddenField('customers_fax');
  } else {
    echo HTML::inputField('customers_fax', $cInfo->customers_fax, 'maxlength="32"');
  }
?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td class="formAreaTitle"><?php echo CATEGORY_OPTIONS; ?></td>
      </tr>
      <tr>
        <td class="formArea"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php echo ENTRY_NEWSLETTER; ?></td>
            <td class="main">
<?php
  if ($processed == true) {
    if ($cInfo->customers_newsletter == '1') {
      echo ENTRY_NEWSLETTER_YES;
    } else {
      echo ENTRY_NEWSLETTER_NO;
    }
    echo HTML::hiddenField('customers_newsletter');
  } else {
    echo HTML::selectField('customers_newsletter', $newsletter_array, (($cInfo->customers_newsletter == '1') ? '1' : '0'));
  }
?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td align="right" class="smallText"><?php echo HTML::button(IMAGE_SAVE, 'fa fa-save', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('action')))); ?></td>
      </tr></form>
<?php
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr><?php echo HTML::form('search', OSCOM::link(FILENAME_CUSTOMERS), 'get', null, ['session_id' => true]); ?>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="smallText" align="right"><?php echo HEADING_TITLE_SEARCH . ' ' . HTML::inputField('search'); ?></td>
          </form></tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LASTNAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_FIRSTNAME; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACCOUNT_CREATED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $sql_query = 'select SQL_CALC_FOUND_ROWS c.customers_id, c.customers_lastname, c.customers_firstname, c.customers_email_address, a.entry_country_id from :table_customers c left join :table_address_book a on (c.customers_id = a.customers_id and c.customers_default_address_id = a.address_book_id)';

    if (isset($_GET['search']) && tep_not_null($_GET['search'])) {
      $sql_query .= ' where c.customers_lastname like :customers_lastname or c.customers_firstname like :customers_firstname or c.customers_email_address like :customers_email_address';
    }

    $sql_query .= ' order by c.customers_lastname, c.customers_firstname limit :page_set_offset, :page_set_max_results';

    $Qcustomers = $OSCOM_Db->prepare($sql_query);
    $Qcustomers->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
    $Qcustomers->execute();

    while ($Qcustomers->fetch()) {
      $Qinfo = $OSCOM_Db->get('customers_info', [
        'customers_info_date_account_created as date_account_created',
        'customers_info_date_account_last_modified as date_account_last_modified',
        'customers_info_date_of_last_logon as date_last_logon',
        'customers_info_number_of_logons as number_of_logons'
      ], [
        'customers_info_id' => $Qcustomers->valueInt('customers_id')
      ]);

      if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ((int)$_GET['cID'] === $Qcustomers->valueInt('customers_id')))) && !isset($cInfo)) {
        $Qcountry = $OSCOM_Db->get('countries', 'countries_name', ['countries_id' => $Qcustomers->valueInt('entry_country_id')]);

        $Qreviews = $OSCOM_Db->get('reviews', 'count(*) as number_of_reviews', ['customers_id' => $Qcustomers->valueInt('customers_id')]);

        $customer_info = array_merge($Qcountry->toArray(), $Qinfo->toArray(), $Qreviews->toArray());

        $cInfo_array = array_merge($Qcustomers->toArray(), $customer_info);
        $cInfo = new objectInfo($cInfo_array);
      }

      if (isset($cInfo) && is_object($cInfo) && ($Qcustomers->valueInt('customers_id') === (int)$cInfo->customers_id)) {
        echo '          <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '          <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID')) . 'cID=' . $Qcustomers->valueInt('customers_id')) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo $Qcustomers->value('customers_lastname'); ?></td>
                <td class="dataTableContent"><?php echo $Qcustomers->value('customers_firstname'); ?></td>
                <td class="dataTableContent" align="right"><?php echo tep_date_short($Qinfo->value('date_account_created')); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($cInfo) && is_object($cInfo) && ($Qcustomers->valueInt('customers_id') === (int)$cInfo->customers_id)) { echo HTML::image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . OSCOM::link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID')) . 'cID=' . $Qcustomers->valueInt('customers_id')) . '">' . HTML::image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qcustomers->getPageSetLabel(TEXT_DISPLAY_NUMBER_OF_CUSTOMERS); ?></td>
                    <td class="smallText" align="right"><?php echo $Qcustomers->getPageSetLinks(); ?></td>
                  </tr>
<?php
    if (isset($_GET['search']) && tep_not_null($_GET['search'])) {
?>
                  <tr>
                    <td class="smallText" align="right" colspan="2"><?php echo HTML::button(IMAGE_RESET, 'fa fa-refresh', OSCOM::link(FILENAME_CUSTOMERS)); ?></td>
                  </tr>
<?php
    }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'confirm':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_CUSTOMER . '</strong>');

      $contents = array('form' => HTML::form('customers', OSCOM::link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=deleteconfirm')));
      $contents[] = array('text' => TEXT_DELETE_INTRO . '<br /><br /><strong>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</strong>');
      if (isset($cInfo->number_of_reviews) && ($cInfo->number_of_reviews) > 0) $contents[] = array('text' => '<br />' . HTML::checkboxField('delete_reviews', 'on', true) . ' ' . sprintf(TEXT_DELETE_REVIEWS, $cInfo->number_of_reviews));
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_DELETE, 'fa fa-trash', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id)));
      break;
    default:
      if (isset($cInfo) && is_object($cInfo)) {
        $heading[] = array('text' => '<strong>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</strong>');

        $contents[] = array('align' => 'center', 'text' => HTML::button(IMAGE_EDIT, 'fa fa-edit', OSCOM::link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=edit')) . HTML::button(IMAGE_DELETE, 'fa fa-trash', OSCOM::link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=confirm')) . HTML::button(IMAGE_ORDERS, 'fa fa-shopping-cart', OSCOM::link(FILENAME_ORDERS, 'cID=' . $cInfo->customers_id)) . HTML::button(IMAGE_EMAIL, 'fa fa-envelope', OSCOM::link(FILENAME_MAIL, 'customer=' . $cInfo->customers_email_address)));
        $contents[] = array('text' => '<br />' . TEXT_DATE_ACCOUNT_CREATED . ' ' . tep_date_short($cInfo->date_account_created));
        $contents[] = array('text' => '<br />' . TEXT_DATE_ACCOUNT_LAST_MODIFIED . ' ' . tep_date_short($cInfo->date_account_last_modified));
        $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_LAST_LOGON . ' '  . tep_date_short($cInfo->date_last_logon));
        $contents[] = array('text' => '<br />' . TEXT_INFO_NUMBER_OF_LOGONS . ' ' . $cInfo->number_of_logons);
        $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY . ' ' . $cInfo->countries_name);
        $contents[] = array('text' => '<br />' . TEXT_INFO_NUMBER_OF_REVIEWS . ' ' . $cInfo->number_of_reviews);
      }
      break;
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
<?php
  }
?>
    </table>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
