<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  define('MODULE_PAYMENT_STRIPE_TEXT_TITLE', 'Stripe');
  define('MODULE_PAYMENT_STRIPE_TEXT_PUBLIC_TITLE', 'Credit Card');
  define('MODULE_PAYMENT_STRIPE_TEXT_DESCRIPTION', '<img src="images/icon_info.gif" border="0" />&nbsp;<a href="http://library.oscommerce.com/Package&en&stripe&oscom23&stripe_js" target="_blank" style="text-decoration: underline; font-weight: bold;">View Online Documentation</a><br /><br /><img src="images/icon_popup.gif" border="0">&nbsp;<a href="https://www.stripe.com" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit Stripe Website</a>');

  define('MODULE_PAYMENT_STRIPE_ERROR_ADMIN_CURL', 'This module requires cURL to be enabled in PHP and will not load until it has been enabled on this webserver.');
  define('MODULE_PAYMENT_STRIPE_ERROR_ADMIN_CONFIGURATION', 'This module will not load until the Publishable Key and Secret Key parameters have been configured. Please edit and configure the settings of this module.');

  define('MODULE_PAYMENT_STRIPE_CREDITCARD_NEW', 'Enter a new Card');
  define('MODULE_PAYMENT_STRIPE_CREDITCARD_OWNER', 'Name on Card:');
  define('MODULE_PAYMENT_STRIPE_CREDITCARD_NUMBER', 'Card Number:');
  define('MODULE_PAYMENT_STRIPE_CREDITCARD_EXPIRY', 'Expiry Date:');
  define('MODULE_PAYMENT_STRIPE_CREDITCARD_CVC', 'Security Code:');
  define('MODULE_PAYMENT_STRIPE_CREDITCARD_SAVE', 'Save Card for next purchase?');

  define('MODULE_PAYMENT_STRIPE_ERROR_TITLE', 'There has been an error processing your credit card');
  define('MODULE_PAYMENT_STRIPE_ERROR_GENERAL', 'Please try again and if problems persist, please try another payment method.');
  define('MODULE_PAYMENT_STRIPE_ERROR_CARDSTORED', 'The stored card could not be found. Please try again and if problems persist, please try another payment method.');

  define('MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_LINK_TITLE', 'Test API Server Connection');
  define('MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_TITLE', 'API Server Connection Test');
  define('MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_GENERAL_TEXT', 'Testing connection to server..');
  define('MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_BUTTON_CLOSE', 'Close');
  define('MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_TIME', 'Connection Time:');
  define('MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_SUCCESS', 'Success!');
  define('MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_FAILED', 'Failed! Please review the Verify SSL Certificate settings and try again.');
  define('MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_ERROR', 'An error occurred. Please refresh the page, review your settings, and try again.');
?>
