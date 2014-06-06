<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  define('MODULE_PAYMENT_BRAINTREE_CC_TEXT_TITLE', 'Braintree Payment Solutions');
  define('MODULE_PAYMENT_BRAINTREE_CC_TEXT_PUBLIC_TITLE', 'Credit Card');
  define('MODULE_PAYMENT_BRAINTREE_CC_TEXT_DESCRIPTION', '<img src="images/icon_info.gif" border="0" />&nbsp;<a href="http://library.oscommerce.com/Package&en&braintree&oscom23&braintree_js" target="_blank" style="text-decoration: underline; font-weight: bold;">View Online Documentation</a><br /><br /><img src="images/icon_popup.gif" border="0">&nbsp;<a href="https://www.braintreepayments.com" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit Braintree Payments Website</a>');

  define('MODULE_PAYMENT_BRAINTREE_CC_ERROR_ADMIN_PHP', 'The minimum PHP version this module supports is %s and will not load until the webserver has been installed with a newer version.');
  define('MODULE_PAYMENT_BRAINTREE_CC_ERROR_ADMIN_PHP_EXTENSIONS', 'This module requires the following PHP extensions and will and will not load until PHP has been updated:<br /><br />%s');
  define('MODULE_PAYMENT_BRAINTREE_CC_ERROR_ADMIN_MERCHANT_ACCOUNTS', 'This module will not load until a merchant account has been defined for the %s currency.');
  define('MODULE_PAYMENT_BRAINTREE_CC_ERROR_ADMIN_CONFIGURATION', 'This module will not load until the Merchant ID, Public Key, Private Key, and Client Side Encryption Key parameters have been configured. Please edit and configure the settings of this module.');

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
?>
