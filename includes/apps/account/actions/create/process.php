<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_create_process {
    public static function execute(app $app) {
      global $OSCOM_Customer, $OSCOM_MessageStack, $OSCOM_PDO;

      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        if (ACCOUNT_GENDER == 'true') $gender = isset($_POST['gender']) ? trim($_POST['gender']) : null;
        $firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : null;
        $lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : null;
        if (ACCOUNT_DOB == 'true') $dob = isset($_POST['dob']) ? trim($_POST['dob']) : null;
        $email_address = isset($_POST['email_address']) ? trim($_POST['email_address']) : null;
        $newsletter = isset($_POST['newsletter']) ? trim($_POST['newsletter']) : null;
        $password = isset($_POST['password']) ? trim($_POST['password']) : null;
        $confirmation = isset($_POST['confirmation']) ? trim($_POST['confirmation']) : null;

        $error = false;

        if ( ACCOUNT_GENDER == 'true' ) {
          if ( ($gender != 'm') && ($gender != 'f') ) {
            $error = true;

            $OSCOM_MessageStack->addError('create_account', ENTRY_GENDER_ERROR);
          }
        }

        if ( strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH ) {
          $error = true;

          $OSCOM_MessageStack->addError('create_account', ENTRY_FIRST_NAME_ERROR);
        }

        if ( strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH ) {
          $error = true;

          $OSCOM_MessageStack->addError('create_account', ENTRY_LAST_NAME_ERROR);
        }

        if ( ACCOUNT_DOB == 'true' ) {
          if ( !is_numeric(osc_date_raw($dob)) || (@checkdate(substr(osc_date_raw($dob), 4, 2), substr(osc_date_raw($dob), 6, 2), substr(osc_date_raw($dob), 0, 4)) == false) ) {
            $error = true;

            $OSCOM_MessageStack->addError('create_account', ENTRY_DATE_OF_BIRTH_ERROR);
          }
        }

        if ( strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH ) {
          $error = true;

          $OSCOM_MessageStack->addError('create_account', ENTRY_EMAIL_ADDRESS_ERROR);
        } elseif ( !osc_validate_email($email_address) ) {
          $error = true;

          $OSCOM_MessageStack->addError('create_account', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
        } else {
          $Qcheck = $OSCOM_PDO->prepare('select customers_email_address from :table_customers where customers_email_address = :customers_email_address limit 1');
          $Qcheck->bindValue(':customers_email_address', $email_address);
          $Qcheck->execute();

          if ( $Qcheck->fetch() !== false ) {
            $error = true;

            $OSCOM_MessageStack->addError('create_account', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
          }
        }

        if ( strlen($password) < ENTRY_PASSWORD_MIN_LENGTH ) {
          $error = true;

          $OSCOM_MessageStack->addError('create_account', ENTRY_PASSWORD_ERROR);
        } elseif ( $password != $confirmation ) {
          $error = true;

          $OSCOM_MessageStack->addError('create_account', ENTRY_PASSWORD_ERROR_NOT_MATCHING);
        }

        if ( $error === false ) {
          $sql_data_array = array('customers_firstname' => $firstname,
                                  'customers_lastname' => $lastname,
                                  'customers_email_address' => $email_address,
                                  'customers_newsletter' => $newsletter,
                                  'customers_password' => osc_encrypt_password($password));

          if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
          if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = osc_date_raw($dob);

          $OSCOM_PDO->perform('customers', $sql_data_array);

          $customer_id = $OSCOM_PDO->lastInsertId();

          $OSCOM_PDO->perform('customers_info', array('customers_info_id' => (int)$customer_id,
                                                      'customers_info_number_of_logons' => '0',
                                                      'customers_info_date_account_created' => 'now()'));

          $OSCOM_Customer->setData($customer_id);

// reset session token
          $_SESSION['sessiontoken'] = md5(osc_rand() . osc_rand() . osc_rand() . osc_rand());

// restore cart contents
          $_SESSION['cart']->restore_contents();

          if ( SESSION_RECREATE == 'True' ) {
            osc_session_recreate();
          }

// build the message content
          $name = $firstname . ' ' . $lastname;

          if ( ACCOUNT_GENDER == 'true' ) {
            if ( $gender == 'm' ) {
              $email_text = sprintf(EMAIL_GREET_MR, $lastname);
            } else {
              $email_text = sprintf(EMAIL_GREET_MS, $lastname);
            }
          } else {
            $email_text = sprintf(EMAIL_GREET_NONE, $firstname);
          }

          $email_text .= EMAIL_WELCOME . EMAIL_TEXT . EMAIL_CONTACT . EMAIL_WARNING;

          osc_mail($name, $email_address, EMAIL_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

          osc_redirect(osc_href_link('account', 'create&success', 'SSL'));
        }
      }
    }
  }
?>
