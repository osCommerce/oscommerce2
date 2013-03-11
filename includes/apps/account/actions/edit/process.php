<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_edit_process {
    public static function execute(app $app) {
      global $OSCOM_Customer, $OSCOM_MessageStack, $OSCOM_PDO;

      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        if (ACCOUNT_GENDER == 'true') $gender = isset($_POST['gender']) ? trim($_POST['gender']) : null;
        $firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : null;
        $lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : null;
        if (ACCOUNT_DOB == 'true') $dob = isset($_POST['dob']) ? trim($_POST['dob']) : null;
        $email_address = isset($_POST['email_address']) ? trim($_POST['email_address']) : null;
        $telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : null;
        $fax = isset($_POST['fax']) ? trim($_POST['fax']) : null;

        $error = false;

        if (ACCOUNT_GENDER == 'true') {
          if ( ($gender != 'm') && ($gender != 'f') ) {
            $error = true;

            $OSCOM_MessageStack->addError('account_edit', ENTRY_GENDER_ERROR);
          }
        }

        if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
          $error = true;

          $OSCOM_MessageStack->addError('account_edit', ENTRY_FIRST_NAME_ERROR);
        }

        if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
          $error = true;

          $OSCOM_MessageStack->addError('account_edit', ENTRY_LAST_NAME_ERROR);
        }

        if (ACCOUNT_DOB == 'true') {
          if ((is_numeric(osc_date_raw($dob)) == false) || (@checkdate(substr(osc_date_raw($dob), 4, 2), substr(osc_date_raw($dob), 6, 2), substr(osc_date_raw($dob), 0, 4)) == false)) {
            $error = true;

            $OSCOM_MessageStack->addError('account_edit', ENTRY_DATE_OF_BIRTH_ERROR);
          }
        }

        if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
          $error = true;

          $OSCOM_MessageStack->addError('account_edit', ENTRY_EMAIL_ADDRESS_ERROR);
        }

        if (!osc_validate_email($email_address)) {
          $error = true;

          $OSCOM_MessageStack->addError('account_edit', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
        }

        $Qcheck = $OSCOM_PDO->prepare('select customers_id from :table_customers where customers_email_address = :customers_email_address and customers_id != :customers_id limit 1');
        $Qcheck->bindValue(':customers_email_address', $email_address);
        $Qcheck->bindInt(':customers_id', $OSCOM_Customer->getID());
        $Qcheck->execute();

        if ( $Qcheck->fetch() !== false ) {
          $error = true;

          $OSCOM_MessageStack->addError('account_edit', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
        }

        if (strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
          $error = true;

          $OSCOM_MessageStack->addError('account_edit', ENTRY_TELEPHONE_NUMBER_ERROR);
        }

        if ($error == false) {
          $sql_data_array = array('customers_firstname' => $firstname,
                                  'customers_lastname' => $lastname,
                                  'customers_email_address' => $email_address,
                                  'customers_telephone' => $telephone,
                                  'customers_fax' => $fax);

          if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
          if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = osc_date_raw($dob);

          $OSCOM_PDO->perform('customers', $sql_data_array, array('customers_id' => $OSCOM_Customer->getID()));
          $OSCOM_PDO->perform('customers_info', array('customers_info_date_account_last_modified' => 'now()'), array('customers_info_id' => $OSCOM_Customer->getID()));

// reset the session variables
          $OSCOM_Customer->setData($OSCOM_Customer->getID());

          $OSCOM_MessageStack->addSuccess('account', SUCCESS_ACCOUNT_UPDATED);

          osc_redirect(osc_href_link('account', '', 'SSL'));
        }
      }
    }
  }
?>
