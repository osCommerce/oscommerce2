<?php
/*
  $Id: ipayment.php,v 1.6 2003/07/08 16:45:36 dgw_ Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  define('MODULE_PAYMENT_IPAYMENT_TEXT_TITLE', 'iPayment');
  define('MODULE_PAYMENT_IPAYMENT_TEXT_DESCRIPTION', 'Tarjeta de Cr&eacute;dito para Pruebas:<br><br>Numero: 4111111111111111<br>Caducidad: Cualquiera');
  define('IPAYMENT_ERROR_HEADING', 'Ha ocurrido un error procesando su tarjeta de cr&eacute;dito');
  define('IPAYMENT_ERROR_MESSAGE', '¡Revise los datos de su tarjeta de cr&eacute;dito!');
  define('MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_OWNER', 'Titular de la Tarjeta:');
  define('MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_NUMBER', 'N&uacute;mero de la Tarjeta:');
  define('MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_EXPIRES', 'Fecha de Caducidad:');
  define('MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_CHECKNUMBER', 'N&uacute;mero de Comprobaci&oacute;n:');
  define('MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_CHECKNUMBER_LOCATION', '(lo puede encontrar en la parte de atr&aacute;s de la tarjeta de credito)');

  define('MODULE_PAYMENT_IPAYMENT_TEXT_JS_CC_OWNER', '* El nombre del titular de la tarjeta de cr&eacute;dito debe de tener al menos ' . CC_OWNER_MIN_LENGTH . ' caracteres.\n');
  define('MODULE_PAYMENT_IPAYMENT_TEXT_JS_CC_NUMBER', '* El n&uacute;mero de la tarjeta de cr&eacute;dito debe tener al menos ' . CC_NUMBER_MIN_LENGTH . ' caracteres.\n');
?>
