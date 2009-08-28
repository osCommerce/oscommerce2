<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2009 osCommerce

  Released under the GNU General Public License
*/

  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_TEXT_TITLE', 'Sage Pay Direct');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_TEXT_PUBLIC_TITLE', 'Tarjeta de Cr&eacute;dito (Procesados por Sage Pay)');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_TEXT_DESCRIPTION', '<img src="images/icon_popup.gif" border="0">&nbsp;<a href="https://support.sagepay.com/apply/default.aspx?PartnerID=C74D7B82-E9EB-4FBD-93DB-76F0F551C802&PromotionCode=osc223" target="_blank" style="text-decoration: underline; font-weight: bold;">Visita la web de Sage Pay</a>&nbsp;<a href="javascript:toggleDivBlock(\'sagePayInfo\');">(info)</a><span id="sagePayInfo" style="display: none;"><br><i>Con el uso del Link para usar Sage Pay osCommerce dar a cada Cliente nuevo un pequeno Bonus.</i></span>');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_TYPE', 'Tipo de tarjeta:');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_OWNER', 'Titular de la tarjeta:');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_NUMBER', 'N&uacute;mero de tarjeta:');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_STARTS', 'Fecha de inicio:');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_STARTS_INFO', '(es solo para tarjeta de Maestro, Solo, American Express)');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_EXPIRES', 'Fecha de caducidad:');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_ISSUE_NUMBER', 'Issue n&uacute;mero de tarjeta:');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_ISSUE_NUMBER_INFO', '(es solo para tarjeta de Maestro y Solo)');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_CREDIT_CARD_CVC', 'C&oacute;digo de seguridad:');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_3DAUTH_TITLE', '3D Secure Verification');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_3DAUTH_INFO', 'Please click on the CONTINUE button to authenticate your card at the website of your bank.');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_3DAUTH_BUTTON', 'CONTINUE');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_TITLE', 'There has been an error processing your credit card');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_GENERAL', 'Please try again and if problems persist, please try another payment method.');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_CARDTYPE', 'The card type is not supported. Please try again with another card and if problems persist, please try another payment method.');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_CARDOWNER', 'The card owners name must be provided to complete the order. Please try again and if problems persist, please try another payment method.');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_CARDNUMBER', 'The card number was not able to be processed. Please try again and if problems persist, please try another payment method.');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_CARDSTART', 'The card start date was not able to be processed. Please try again and if problems persist, please try another payment method.');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_CARDEXPIRES', 'The card expiry date was not able to be processed. Please try again and if problems persist, please try another payment method.');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_CARDISSUE', 'The card issue number was not able to be processed. Please try again and if problems persist, please try another payment method.');
  define('MODULE_PAYMENT_SAGE_PAY_DIRECT_ERROR_CARDCVC', 'The card check number (CVC) was not able to be processed. Please try again and if problems persist, please try another payment method.');
?>
