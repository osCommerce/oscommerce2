<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  define('MODULE_CONTENT_PAYPAL_LOGIN_TITLE', 'Log In with PayPal');
  define('MODULE_CONTENT_PAYPAL_LOGIN_DESCRIPTION', 'Enable Log In with PayPal with seamless checkout for PayPal Express Checkout payments<br /><br /><img src="images/icon_popup.gif" border="0">&nbsp;<a href="https://www.paypal.com" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit PayPal Website</a>');

  define('MODULE_CONTENT_PAYPAL_LOGIN_TEMPLATE_TITLE', 'Log In with PayPal');
  define('MODULE_CONTENT_PAYPAL_LOGIN_TEMPLATE_CONTENT', 'Have a PayPal account? Securely log in with PayPal to shop even faster!');
  define('MODULE_CONTENT_PAYPAL_LOGIN_TEMPLATE_SANDBOX', 'Test Mode: The Sandbox server is currently selected.');

  define('MODULE_CONTENT_PAYPAL_LOGIN_ERROR_ADMIN_CONFIGURATION', 'This module will not load until the Client ID and Secret parameters have been configured. Please edit and configure the settings of this module.');

  define('MODULE_CONTENT_PAYPAL_LOGIN_LANGUAGE_LOCALE', 'en-us');

  define('MODULE_CONTENT_PAYPAL_LOGIN_EMAIL_PASSWORD', 'An account has automatically been created for you with the following e-mail address and password:' . "\n\n" . 'Store Account E-Mail Address: %s' . "\n" . 'Store Account Password: %s' . "\n\n");

  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_GROUP_personal', 'Personal Information');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_GROUP_address', 'Address Information');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_GROUP_account', 'Account Information');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_GROUP_checkout', 'Checkout Express');

  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_full_name', 'Full Name');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_date_of_birth', 'Date of Birth');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_age_range', 'Age Range');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_gender', 'Gender');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_email_address', 'Email Address');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_street_address', 'Street Address');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_city', 'City');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_state', 'State');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_country', 'Country');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_zip_code', 'Zip Code');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_phone', 'Phone');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_account_status', 'Account Status (verified)');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_account_type', 'Account Type');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_account_creation_date', 'Account Creation Date');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_time_zone', 'Time Zone');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_locale', 'Locale');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_language', 'Language');
  define('MODULES_CONTENT_PAYPAL_LOGIN_ATTR_seamless_checkout', 'Seamless Checkout');

  define('MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_LINK_TITLE', 'Test Gateway Connection');
  define('MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_TITLE', 'Gateway Connection Test');
  define('MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_GENERAL_ERROR', 'An error occurred. Please refresh the page and try again.');
  define('MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_GENERAL_TEXT', 'Testing connection to gateway..');
  define('MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_BUTTON_CLOSE', 'Close');
  define('MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_SUCCESS_TITLE', 'Success!');
  define('MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_SUCCESS_TEXT_LIVE', 'A connection to the live gateway can been made.');
  define('MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_SUCCESS_TEXT_TEST', 'A connection to the test gateway can been made.');
  define('MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_ERROR_TITLE', 'Error!');
  define('MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_ERROR_TEXT_LIVE', 'A connection to the live gateway could not be made.<br /><br />Disable Verify SSL Certificate in the module configuration and try again.');
  define('MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_ERROR_TEXT_TEST', 'A connection to the test gateway could not be made.<br /><br />Disable Verify SSL Certificate in the module configuration and try again.');
  define('MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_NOT_INSTALLED', 'The Log In with PayPal content module is not installed. Please install it and try again.');
?>
