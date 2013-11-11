<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  define('MODULE_PAYMENT_STRIPE_TEXT_TITLE', 'Stripe');
  define('MODULE_PAYMENT_STRIPE_TEXT_PUBLIC_TITLE', 'Credit Card');
  define('MODULE_PAYMENT_STRIPE_TEXT_DESCRIPTION', '<img src="images/icon_popup.gif" border="0">&nbsp;<a href="https://www.stripe.com" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit Stripe Website</a>');
  define('MODULE_PAYMENT_STRIPE_CREDITCARD_NEW', 'Enter a new Card');
  define('MODULE_PAYMENT_STRIPE_CREDITCARD_LAST_4', 'Last 4 Digits:');
  define('MODULE_PAYMENT_STRIPE_CREDITCARD_OWNER', 'Name on Card:');
  define('MODULE_PAYMENT_STRIPE_CREDITCARD_NUMBER', 'Card Number:');
  define('MODULE_PAYMENT_STRIPE_CREDITCARD_EXPIRY', 'Expiry Date:');
  define('MODULE_PAYMENT_STRIPE_CREDITCARD_CVC', 'Security Code:');
  define('MODULE_PAYMENT_STRIPE_CREDITCARD_SAVE', 'Save Card for next purchase?');

  define('MODULE_PAYMENT_STRIPE_ERROR_TITLE', 'There has been an error processing your credit card');
  define('MODULE_PAYMENT_STRIPE_ERROR_GENERAL', 'Please try again and if problems persist, please try another payment method.');
  define('MODULE_PAYMENT_STRIPE_ERROR_CARDSTORED', 'The stored card could not be found. Please try again and if problems persist, please try another payment method.');

  define('MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_LINK_TITLE', 'Test Gateway Connection');
  define('MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_TITLE', 'Gateway Connection Test');
  define('MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_GENERAL_ERROR', 'An error occurred. Please refresh the page and try again.');
  define('MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_GENERAL_TEXT', 'Testing connection to gateway..');
  define('MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_BUTTON_CLOSE', 'Close');
  define('MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_SUCCESS_TITLE', 'Success!');
  define('MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_SUCCESS_TEXT', 'A connection to the gateway can been made.');
  define('MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_ERROR_TITLE', 'Error!');
  define('MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_ERROR_TEXT', 'A connection to the gateway could not be made.<br /><br />Disable Verify SSL Certificate in the module configuration and try again.');
  define('MODULE_PAYMENT_STRIPE_DIALOG_CONNECTION_NOT_INSTALLED', 'The Stripe payment module is not installed. Please install it and try again.');
?>
