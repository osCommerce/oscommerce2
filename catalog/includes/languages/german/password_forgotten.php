<?php
/*
  $Id: password_forgotten.php,v 1.11 2003/06/25 21:17:02 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

define('NAVBAR_TITLE_1', 'Anmelden');
define('NAVBAR_TITLE_2', 'Passwort vergessen');

define('HEADING_TITLE', 'Wie war noch mal mein Passwort?');

define('TEXT_MAIN', 'Sollten Sie Ihr Passwort nicht mehr wissen, geben Sie bitte unten Ihre eMail-Adresse ein um umgehend ein neues Passwort per eMail zu erhalten.');

define('TEXT_NO_EMAIL_ADDRESS_FOUND', 'Fehler: Die eingegebene eMail-Adresse ist nicht registriert. Bitte versuchen Sie es noch einmal.');

define('EMAIL_PASSWORD_REMINDER_SUBJECT', STORE_NAME . ' - Ihr neues Passwort.');
define('EMAIL_PASSWORD_REMINDER_BODY', 'Über die Adresse ' . $REMOTE_ADDR . ' haben wir eine Anfrage zur Passworterneuerung erhalten.' . "\n\n" . 'Ihr neues Passwort für \'' . STORE_NAME . '\' lautet ab sofort:' . "\n\n" . '   %s' . "\n\n");

define('SUCCESS_PASSWORD_SENT', 'Success: Ein neues Passwort wurde per eMail verschickt.');
?>
