<?php
/*
  $Id: tell_a_friend.php,v 1.10 2003/07/08 16:45:36 dgw_ Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

define('NAVBAR_TITLE', 'Enviar a un Amigo');

define('HEADING_TITLE', 'Enviar informaci&oacute;n sobre \'%s\' un amigo');

define('FORM_TITLE_CUSTOMER_DETAILS', 'Tus Datos');
define('FORM_TITLE_FRIEND_DETAILS', 'Los Datos De Tu Amigo');
define('FORM_TITLE_FRIEND_MESSAGE', 'Tu Mensaje');

define('FORM_FIELD_CUSTOMER_NAME', 'Tu Nombre:');
define('FORM_FIELD_CUSTOMER_EMAIL', 'Tu Email:');
define('FORM_FIELD_FRIEND_NAME', 'El Nombre De Tu Amigo:');
define('FORM_FIELD_FRIEND_EMAIL', 'El Email De Tu Amigo:');

define('TEXT_EMAIL_SUCCESSFUL_SENT', 'Tu email sobre <b>%s</b> ha sido enviado con &eacute;xito a <b>%s</b>.');

define('TEXT_EMAIL_SUBJECT', 'Tu amigo %s te quiere recomendar "%s"');
define('TEXT_EMAIL_INTRO', 'Hola %s!' . "\n\n" . 'Tu amigo %s, pensó que estarias interesado en %s de %s.');
define('TEXT_EMAIL_LINK', 'Para ver el producto usa el siguiente enlace:' . "\n\n" . '%s');
define('TEXT_EMAIL_SIGNATURE', 'Atentamente,' . "\n\n" . '%s');

define('ERROR_TO_NAME', 'Error: La direcci&oacute;n de su amigo no puede estar vacia.');
define('ERROR_TO_ADDRESS', 'Error: La direcci&oacute;n de su amigo debe ser v&aacute;lida.');
define('ERROR_FROM_NAME', 'Error: Su nombre no debe estar vacio.');
define('ERROR_FROM_ADDRESS', 'Error: Su direcci&oacute;n de email debe de ser v&aacute;lida.');
?>
