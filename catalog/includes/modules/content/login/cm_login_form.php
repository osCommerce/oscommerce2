<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class cm_login_form {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_login_form() {
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

      $error = false;

      if (isset($_GET['action']) && ($_GET['action'] == 'process') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
        $email_address = osc_db_prepare_input($_POST['email_address']);
        $password = osc_db_prepare_input($_POST['password']);

// Check if email exists
        $customer_query = osc_db_query("select customers_id, customers_password from " . TABLE_CUSTOMERS . " where customers_email_address = '" . osc_db_input($email_address) . "' limit 1");
        if (!osc_db_num_rows($customer_query)) {
          $error = true;
        } else {
          $customer = osc_db_fetch_array($customer_query);

// Check that password is good
          if (!tep_validate_password($password, $customer['customers_password'])) {
            $error = true;
          } else {
// set $login_customer_id globally and perform post login code in catalog/login.php
            $login_customer_id = (int)$customer['customers_id'];

// migrate old hashed password to new phpass password
            if (tep_password_type($customer['customers_password']) != 'phpass') {
              osc_db_query("update " . TABLE_CUSTOMERS . " set customers_password = '" . tep_encrypt_password($password) . "' where customers_id = '" . (int)$login_customer_id . "'");
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
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Login Form Module', 'MODULE_CONTENT_LOGIN_FORM_STATUS', 'True', 'Do you want to enable the login form module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Width', 'MODULE_CONTENT_LOGIN_FORM_CONTENT_WIDTH', 'Half', 'Should the content be shown in a full or half width container?', '6', '1', 'tep_cfg_select_option(array(\'Full\', \'Half\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_LOGIN_FORM_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      osc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_CONTENT_LOGIN_FORM_STATUS', 'MODULE_CONTENT_LOGIN_FORM_CONTENT_WIDTH', 'MODULE_CONTENT_LOGIN_FORM_SORT_ORDER');
    }
  }
?>
