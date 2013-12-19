<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  define('MODULE_PAYMENT_BRAINTREE_CC_TEXT_TITLE', 'Braintree Payment Solutions');
  define('MODULE_PAYMENT_BRAINTREE_CC_TEXT_PUBLIC_TITLE', 'Credit Card');
  define('MODULE_PAYMENT_BRAINTREE_CC_TEXT_DESCRIPTION', '<img src="images/icon_popup.gif" border="0">&nbsp;<a href="https://www.braintreepayments.com" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit Braintree Payments Website</a>');
  define('MODULE_PAYMENT_BRAINTREE_CC_CREDITCARD_NEW', 'Enter a new Card');
  define('MODULE_PAYMENT_BRAINTREE_CC_CREDITCARD_LAST_4', 'Last 4 Digits:');
  define('MODULE_PAYMENT_BRAINTREE_CC_CREDITCARD_OWNER', 'Name on Card:');
  define('MODULE_PAYMENT_BRAINTREE_CC_CREDITCARD_NUMBER', 'Card Number:');
  define('MODULE_PAYMENT_BRAINTREE_CC_CREDITCARD_EXPIRY', 'Expiry Date:');
  define('MODULE_PAYMENT_BRAINTREE_CC_CREDITCARD_CVV', 'Security Code:');
  define('MODULE_PAYMENT_BRAINTREE_CC_CREDITCARD_SAVE', 'Save Card for next purchase?');

  define('MODULE_PAYMENT_BRAINTREE_CC_CURRENCY_CHARGE', 'The currency currently used to display prices is in %3$s. Your credit card will be charged a total of <span style="white-space: nowrap;">%1$s %2$s</span> for this order.');

  define('MODULE_PAYMENT_BRAINTREE_CC_ERROR_TITLE', 'There has been an error processing your credit card');
  define('MODULE_PAYMENT_BRAINTREE_CC_ERROR_GENERAL', 'Please try again and if problems persist, please try another payment method.');
  define('MODULE_PAYMENT_BRAINTREE_CC_ERROR_CARDOWNER', 'The card owners name must be provided to complete the order. Please try again and if problems persist, please try another payment method.');
  define('MODULE_PAYMENT_BRAINTREE_CC_ERROR_CARDNUMBER', 'The card number was not able to be processed. Please try again and if problems persist, please try another payment method.');
  define('MODULE_PAYMENT_BRAINTREE_CC_ERROR_CARDEXPIRES', 'The card expiry date was not able to be processed. Please try again and if problems persist, please try another payment method.');
  define('MODULE_PAYMENT_BRAINTREE_CC_ERROR_CARDCVV', 'The card security code was not able to be processed. Please try again and if problems persist, please try another payment method.');

  define('MODULE_PAYMENT_BRAINTREE_CC_DIALOG_CONNECTION_LINK_TITLE', 'Test Gateway Connection');
  define('MODULE_PAYMENT_BRAINTREE_CC_DIALOG_CONNECTION_TITLE', 'Gateway Connection Test');
  define('MODULE_PAYMENT_BRAINTREE_CC_DIALOG_CONNECTION_GENERAL_ERROR', 'An error occurred. Please refresh the page and try again.');
  define('MODULE_PAYMENT_BRAINTREE_CC_DIALOG_CONNECTION_GENERAL_TEXT', 'Testing connection to gateway..');
  define('MODULE_PAYMENT_BRAINTREE_CC_DIALOG_CONNECTION_BUTTON_CLOSE', 'Close');
  define('MODULE_PAYMENT_BRAINTREE_CC_DIALOG_CONNECTION_SUCCESS_TITLE', 'Success!');
  define('MODULE_PAYMENT_BRAINTREE_CC_DIALOG_CONNECTION_SUCCESS_TEXT_LIVE', 'A connection to the live gateway can been made.');
  define('MODULE_PAYMENT_BRAINTREE_CC_DIALOG_CONNECTION_SUCCESS_TEXT_TEST', 'A connection to the test gateway can been made.');
  define('MODULE_PAYMENT_BRAINTREE_CC_DIALOG_CONNECTION_ERROR_TITLE', 'Error!');
  define('MODULE_PAYMENT_BRAINTREE_CC_DIALOG_CONNECTION_ERROR_TEXT_LIVE', 'A connection to the live gateway could not be made.<br /><br />Disable Verify SSL Certificate in the module configuration and try again.');
  define('MODULE_PAYMENT_BRAINTREE_CC_DIALOG_CONNECTION_ERROR_TEXT_TEST', 'A connection to the test gateway could not be made.<br /><br />Disable Verify SSL Certificate in the module configuration and try again.');
  define('MODULE_PAYMENT_BRAINTREE_CC_DIALOG_CONNECTION_NOT_INSTALLED', 'The Braintree Payment Solutions payment module is not installed. Please install it and try again.');
?>
