<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $moneybookers_ping_button = '';
  if (defined('MODULE_PAYMENT_MONEYBOOKERS_STATUS') && tep_not_null(MODULE_PAYMENT_MONEYBOOKERS_SECRET_WORD)) {
    $moneybookers_ping_button = '<p><img src="images/icons/locked.gif" border="0">&nbsp;<a href=' . tep_href_link('ext/modules/payment/moneybookers/activation.php', 'action=testSecretWord', 'SSL') . ' style="text-decoration: underline; font-weight: bold;">Test Secret Word</a></p>';
  }

  define('MODULE_PAYMENT_MONEYBOOKERS_TEXT_TITLE', 'Moneybookers - Core Module');
  define('MODULE_PAYMENT_MONEYBOOKERS_TEXT_PUBLIC_TITLE', 'Moneybookers eWallet');
  define('MODULE_PAYMENT_MONEYBOOKERS_TEXT_DESCRIPTION', '<img src="images/icon_popup.gif" border="0">&nbsp;<a href="http://www.moneybookers.com/partners/oscommerce" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit Moneybookers Website</a>' . $moneybookers_ping_button);
  define('MODULE_PAYMENT_MONEYBOOKERS_RETURN_TEXT', 'Continue and return to ' . STORE_NAME);
  define('MODULE_PAYMENT_MONEYBOOKERS_LANGUAGE_CODE', 'EN');

  define('MB_ACTIVATION_TITLE', 'Moneybookers Account Activation');
  define('MB_ACTIVATION_ACCOUNT_TITLE', 'Verify Account');
  define('MB_ACTIVATION_ACCOUNT_TEXT', 'Activating Moneybookers Quick Checkout enables you to take direct payments from credit cards, debit cards and over 60 other local payment options in over 200 countries as well as the Moneybookers eWallet.<br /><br />To have access to the international payment network of Moneybookers <a href="http://www.moneybookers.com/partners/oscommerce" target="_blank">please register here</a> for a free account if you don\'t have one yet.');
  define('MB_ACTIVATION_EMAIL_ADDRESS', 'Moneybookers Account E-Mail Address:');
  define('MB_ACTIVATION_ACTIVATE_TITLE', 'Account Activation');
  define('MB_ACTIVATION_ACTIVATE_TEXT', 'An activation request has been sent to Moneybookers. Please be aware that the verification process to use Moneybookers Quick Checkout could take up to 72 hours. <strong>You will be contacted by Moneybookers when the verification process has been completed.</strong><br /><br /><i>After activation Moneybookers will give you access to a new section in your Moneybookers account called "Merchant Tools". Please choose a secret word (do not use your password for this) and enter it into the merchant tools section and in the configuration of the payment module on the next page.</i>');
  define('MB_ACTIVATION_NONEXISTING_ACCOUNT_TITLE', 'Account Error');
  define('MB_ACTIVATION_NONEXISTING_ACCOUNT_TEXT', 'The e-mail address is not a registered Moneybookers account. Please <a href="http://www.moneybookers.com/partners/oscommerce" target="_blank">register here</a> to begin selling with Moneybookers.');
  define('MB_ACTIVATION_SECRET_WORD_TITLE', 'Secret Word Test');
  define('MB_ACTIVATION_SECRET_WORD_SUCCESS_TEXT', 'The secret word has been setup <strong>correctly</strong>! Transactions can now be securely verified with the payment gateway.');
  define('MB_ACTIVATION_SECRET_WORD_FAIL_TEXT', 'The secret word configuration has <strong>failed</strong>! Please review the secret word at your Moneybookers "Merchant Tools" account and the configuration of the payment module.');
  define('MB_ACTIVATION_SECRET_WORD_ERROR_TITLE', 'Error');
  define('MB_ACTIVATION_SECRET_WORD_ERROR_EXCEEDED', 'The maximum number of tries has been exceeded. Please try again in an hour.');
  define('MB_ACTIVATION_CORE_REQUIRED_TITLE', 'Core Moneybookers Module Required');
  define('MB_ACTIVATION_CORE_REQUIRED_TEXT', 'The core Moneybookers payment module is required to support the Moneybookers Quick Checkout payment options. Please continue to install and configure the core payment module.');
  define('MB_ACTIVATION_VERIFY_ACCOUNT_BUTTON', 'Verify Account');
  define('MB_ACTIVATION_CONTINUE_BUTTON', 'Continue and configure payment module');
  define('MB_ACTIVATION_SUPPORT_TITLE', 'Support');
  define('MB_ACTIVATION_SUPPORT_TEXT', 'Do you have questions? Contact Moneybookers by e-mail at <a href="mailto:ecommerce@moneybookers.com">ecommerce@moneybookers.com</a> or by phone +44 (0) 870 383 0762. Your question may also already be answered on the <a href="http://forums.oscommerce.com/forum/78-moneybookers/" target="_blank">osCommerce Community Support forum</a>.');
?>
