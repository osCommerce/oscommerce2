<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\Registry;

  class cm_login_form {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_LOGIN_FORM_TITLE;
      $this->description = MODULE_CONTENT_LOGIN_FORM_DESCRIPTION;

      if ( defined('MODULE_CONTENT_LOGIN_FORM_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_LOGIN_FORM_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_LOGIN_FORM_STATUS == 'True');
      }
    }

    function execute() {
      global $login_customer_id, $messageStack, $oscTemplate;

      $OSCOM_Db = Registry::get('Db');

      $error = false;

      if (isset($_GET['action']) && ($_GET['action'] == 'process') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
        $email_address = HTML::sanitize($_POST['email_address']);
        $password = HTML::sanitize($_POST['password']);

// Check if email exists
        $Qcustomer = $OSCOM_Db->get('customers', ['customers_id', 'customers_password'], ['customers_email_address' => $email_address], null, 1);

        if ($Qcustomer->fetch() === false) {
          $error = true;
        } else {
// Check that password is good
          if (!tep_validate_password($password, $Qcustomer->value('customers_password'))) {
            $error = true;
          } else {
// set $login_customer_id globally and perform post login code in catalog/login.php
            $login_customer_id = $Qcustomer->valueInt('customers_id');

// migrate old hashed password to new phpass password
            if (tep_password_type($Qcustomer->value('customers_password')) != 'phpass') {
              $OSCOM_Db->save('customers', ['customers_password' => tep_encrypt_password($password)], ['customers_id' => $login_customer_id]);
            }
          }
        }
      }

      if ($error == true) {
        $messageStack->add('login', MODULE_CONTENT_LOGIN_TEXT_LOGIN_ERROR);
      }

      ob_start();
      include(DIR_WS_MODULES . 'content/' . $this->group . '/templates/login_form.php');
      $template = ob_get_clean();

      $oscTemplate->addContent($template, $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_LOGIN_FORM_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Login Form Module',
        'configuration_key' => 'MODULE_CONTENT_LOGIN_FORM_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to enable the login form module?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Content Width',
        'configuration_key' => 'MODULE_CONTENT_LOGIN_FORM_CONTENT_WIDTH',
        'configuration_value' => 'Half',
        'configuration_description' => 'Should the content be shown in a full or half width container?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'Full\', \'Half\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_CONTENT_LOGIN_FORM_SORT_ORDER',
        'configuration_value' => '0',
        'configuration_description' => 'Sort order of display. Lowest is displayed first.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    function keys() {
      return array('MODULE_CONTENT_LOGIN_FORM_STATUS', 'MODULE_CONTENT_LOGIN_FORM_CONTENT_WIDTH', 'MODULE_CONTENT_LOGIN_FORM_SORT_ORDER');
    }
  }
?>
