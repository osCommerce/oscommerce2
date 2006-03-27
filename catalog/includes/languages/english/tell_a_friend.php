<?php
/*
  $Id: tell_a_friend.php,v 1.7 2003/06/10 18:20:39 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

define('NAVBAR_TITLE', 'Tell A Friend');

define('HEADING_TITLE', 'Tell A Friend About \'%s\'');

define('FORM_TITLE_CUSTOMER_DETAILS', 'Your Details');
define('FORM_TITLE_FRIEND_DETAILS', 'Your Friends Details');
define('FORM_TITLE_FRIEND_MESSAGE', 'Your Message');

define('FORM_FIELD_CUSTOMER_NAME', 'Your Name:');
define('FORM_FIELD_CUSTOMER_EMAIL', 'Your E-Mail Address:');
define('FORM_FIELD_FRIEND_NAME', 'Your Friends Name:');
define('FORM_FIELD_FRIEND_EMAIL', 'Your Friends E-Mail Address:');

define('TEXT_EMAIL_SUCCESSFUL_SENT', 'Your email about <b>%s</b> has been successfully sent to <b>%s</b>.');

define('TEXT_EMAIL_SUBJECT', 'Your friend %s has recommended this great product from %s');
define('TEXT_EMAIL_INTRO', 'Hi %s!' . "\n\n" . 'Your friend, %s, thought that you would be interested in %s from %s.');
define('TEXT_EMAIL_LINK', 'To view the product click on the link below or copy and paste the link into your web browser:' . "\n\n" . '%s');
define('TEXT_EMAIL_SIGNATURE', 'Regards,' . "\n\n" . '%s');

define('ERROR_TO_NAME', 'Error: Your friends name must not be empty.');
define('ERROR_TO_ADDRESS', 'Error: Your friends e-mail address must be a valid e-mail address.');
define('ERROR_FROM_NAME', 'Error: Your name must not be empty.');
define('ERROR_FROM_ADDRESS', 'Error: Your e-mail address must be a valid e-mail address.');
?>
