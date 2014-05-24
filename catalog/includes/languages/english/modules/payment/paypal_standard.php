<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  define('MODULE_PAYMENT_PAYPAL_STANDARD_TEXT_TITLE', 'PayPal Payments Standard');
  define('MODULE_PAYMENT_PAYPAL_STANDARD_TEXT_PUBLIC_TITLE', 'PayPal (including Credit and Debit Cards)');
  define('MODULE_PAYMENT_PAYPAL_STANDARD_TEXT_DESCRIPTION', '<img src="images/icon_info.gif" border="0" />&nbsp;<a href="http://library.oscommerce.com/Package&en&paypal&oscom23&payments_standard" target="_blank" style="text-decoration: underline; font-weight: bold;">View Online Documentation</a><br /><br /><img src="images/icon_popup.gif" border="0" />&nbsp;<a href="https://www.paypal.com" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit PayPal Website</a>');

  define('MODULE_PAYMENT_PAYPAL_STANDARD_ERROR_ADMIN_CURL', 'This module requires cURL to be enabled in PHP and will not load until it has been enabled on this webserver.');
  define('MODULE_PAYMENT_PAYPAL_STANDARD_ERROR_ADMIN_CONFIGURATION', 'This module will not load until the Seller E-Mail Address parameter has been configured. Please edit and configure the settings of this module.');

  define('MODULE_PAYMENT_PAYPAL_STANDARD_TEXT_PAYPAL_RETURN_BUTTON', 'Back to ' . STORE_NAME); // Maximum length 60 characters, otherwise it is ignored.
  define('MODULE_PAYMENT_PAYPAL_STANDARD_TEXT_INVALID_TRANSACTION', 'Could not verify the PayPal transaction. Please try again.');

  define('MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_LINK_TITLE', 'Test API Server Connection');
  define('MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_TITLE', 'API Server Connection Test');
  define('MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_GENERAL_TEXT', 'Testing connection to server..');
  define('MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_BUTTON_CLOSE', 'Close');
  define('MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_TIME', 'Connection Time:');
  define('MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_SUCCESS', 'Success!');
  define('MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_FAILED', 'Failed! Please review the Verify SSL Certificate settings and try again.');
  define('MODULE_PAYMENT_PAYPAL_STANDARD_DIALOG_CONNECTION_ERROR', 'An error occurred. Please refresh the page, review your settings, and try again.');
?>
