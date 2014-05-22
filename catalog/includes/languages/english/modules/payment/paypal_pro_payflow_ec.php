<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TEXT_TITLE', 'PayPal Express Checkout (Payflow Edition)');
  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TEXT_PUBLIC_TITLE', 'PayPal (including Credit and Debit Cards)');
  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TEXT_DESCRIPTION', '<img src="images/icon_info.gif" border="0" />&nbsp;<a href="http://library.oscommerce.com/Package&en&paypal&oscom23&express_checkout_payflow" target="_blank" style="text-decoration: underline; font-weight: bold;">View Online Documentation</a><br /><br /><img src="images/icon_popup.gif" border="0" />&nbsp;<a href="https://www.paypal.com" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit PayPal Website</a>');

  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_DIRECT_MODULE', 'PayPal mandates the PayPal Payments Pro (Payflow Edition) payment module be enabled if this module is to be activated. This module will not load until the PayPal Payments Pro (Payflow Edition) module has been installed.');
  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_ADMIN_CURL', 'This module requires cURL to be enabled in PHP and will not load until it has been enabled on this webserver.');
  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_ADMIN_CONFIGURATION', 'This module will not load until the Vendor and Password parameters have been configured. Please edit and configure the settings of this module.');

  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TEXT_BUTTON', 'Checkout with PayPal');
  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_BUTTON', 'https://www.paypalobjects.com/webstatic/en_US/btn/btn_checkout_pp_142x27.png');

  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_TEXT_COMMENTS', 'Comments:');

  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_EMAIL_PASSWORD', 'An account has automatically been created for you with the following e-mail address and password:' . "\n\n" . 'Store Account E-Mail Address: %s' . "\n" . 'Store Account Password: %s' . "\n\n");

  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_GENERAL', 'Error: A general problem has occurred with the transaction. Please try again.');
  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_CFG_ERROR', 'Error: Payment module configuration error. Please verify the login credentials.');
  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_ADDRESS', 'Error: A match of the Shipping Address City, State, and Postal Code failed. Please try again.');
  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_DECLINED', 'Error: This transaction has been declined. Please try again.');
  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_EXPRESS_DISABLED', 'Error: PayPal Express Checkout has been disabled for this merchant. Please contact PayPal Customer Service.');

  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_DIALOG_CONNECTION_LINK_TITLE', 'Test API Server Connection');
  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_DIALOG_CONNECTION_TITLE', 'API Server Connection Test');
  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_DIALOG_CONNECTION_GENERAL_TEXT', 'Testing connection to server..');
  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_DIALOG_CONNECTION_BUTTON_CLOSE', 'Close');
  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_DIALOG_CONNECTION_TIME', 'Connection Time:');
  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_DIALOG_CONNECTION_SUCCESS', 'Success!');
  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_DIALOG_CONNECTION_FAILED', 'Failed! Please review the Verify SSL Certificate settings and try again.');
  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_DIALOG_CONNECTION_ERROR', 'An error occurred. Please refresh the page, review your settings, and try again.');

  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_ERROR_NO_SHIPPING_AVAILABLE_TO_SHIPPING_ADDRESS', 'Shipping is currently not available for the selected shipping address. Please select or create a new shipping address to use with your purchase.');
  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_WARNING_LOCAL_LOGIN_REQUIRED', 'Please log into your account to verify the order.');
  define('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_EC_NOTICE_CHECKOUT_CONFIRMATION', 'Please review and confirm your order below. Your order will not be processed until it has been confirmed.');
?>
