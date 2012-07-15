<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

define('NAVBAR_TITLE_1', 'Login');
define('NAVBAR_TITLE_2', 'Password Forgotten');

define('HEADING_TITLE', 'I\'ve Forgotten My Password!');

define('TEXT_MAIN', 'If you\'ve forgotten your password, enter your e-mail address below and we\'ll send you instructions on how to securely change your password.');

define('TEXT_PASSWORD_RESET_INITIATED', 'Please check your e-mail for instructions on how to change your password. The instructions contain a link that is valid only for 24 hours or until your password has been updated.');

define('TEXT_NO_EMAIL_ADDRESS_FOUND', 'Error: The E-Mail Address was not found in our records, please try again.');

define('EMAIL_PASSWORD_RESET_SUBJECT', STORE_NAME . ' - New Password');
define('EMAIL_PASSWORD_RESET_BODY', 'A new password has been requested for your account at ' . STORE_NAME . '.' . "\n\n" . 'Please follow this personal link to securely change your password:' . "\n\n" . '%s' . "\n\n" . 'This link will be automatically discarded after 24 hours or after your password has been changed.' . "\n\n" . 'For help with any of our online services, please email the store-owner: ' . STORE_OWNER_EMAIL_ADDRESS . '.' . "\n\n");

define('ERROR_ACTION_RECORDER', 'Error: A password reset link has already been sent. Please try again in %s minutes.');
?>