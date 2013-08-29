<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_TITLE', 'Authorize.net Advanced Integration Method (AIM)');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_PUBLIC_TITLE', 'Credit Card');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_DESCRIPTION', '<img src="images/icon_popup.gif" border="0">&nbsp;<a href="https://www.authorize.net" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit Authorize.net Website</a>');

  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_CREDIT_CARD_OWNER_FIRSTNAME', 'Card Owner First Name:');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_CREDIT_CARD_OWNER_LASTNAME', 'Card Owner Last Name:');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_CREDIT_CARD_NUMBER', 'Card Number:');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_CREDIT_CARD_EXPIRES', 'Card Expiry Date:');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_CREDIT_CARD_CCV', 'Card Code Number (CCV):');

  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_AVS_A', 'Address (Street) matches, ZIP does not');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_AVS_B', 'Address information not provided for AVS check');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_AVS_E', 'AVS error');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_AVS_G', 'Non-U.S. Card Issuing Bank');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_AVS_N', 'No Match on Address (Street) or ZIP');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_AVS_P', 'AVS not applicable for this transaction');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_AVS_R', 'Retry – System unavailable or timed out');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_AVS_S', 'Service not supported by issuer');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_AVS_U', 'Address information is unavailable');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_AVS_W', 'Nine digit ZIP matches, Address (Street) does not');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_AVS_X', 'Address (Street) and nine digit ZIP match');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_AVS_Y', 'Address (Street) and five digit ZIP match');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_AVS_Z', 'Five digit ZIP matches, Address (Street) does not');

  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CVV2_M', 'Match');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CVV2_N', 'No Match');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CVV2_P', 'Not Processed');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CVV2_S', 'Should have been present');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CVV2_U', 'Issuer unable to process request');

  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CAVV_0', 'CAVV not validated because erroneous data was submitted');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CAVV_1', 'CAVV failed validation');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CAVV_2', 'CAVV passed validation');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CAVV_3', 'CAVV validation could not be performed; issuer attempt incomplete');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CAVV_4', 'CAVV validation could not be performed; issuer system error');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CAVV_5', 'Reserved for future use');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CAVV_6', 'Reserved for future use');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CAVV_7', 'CAVV attempt – failed validation – issuer available (U.S.-issued card/non-U.S. acquirer)');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CAVV_8', 'CAVV attempt – passed validation – issuer available (U.S.-issued card/non-U.S. acquirer)');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CAVV_9', 'CAVV attempt – failed validation – issuer unavailable (U.S.-issued card/non-U.S. acquirer)');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CAVV_A', 'CAVV attempt – passed validation – issuerunavailable (U.S.-issued card/non-U.S. acquirer)');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_TEXT_CAVV_B', 'CAVV passed validation, information only, no liability shift');

  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ERROR_TITLE', 'There has been an error processing your credit card');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ERROR_GENERAL', 'Please try again and if problems persist, please try another payment method.');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ERROR_DECLINED', 'This credit card transaction has been declined. Please try again and if problems persist, please try another credit card or payment method.');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ERROR_INVALID_EXP_DATE', 'The credit card expiration date is invalid. Please check the card information and try again.');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ERROR_EXPIRED', 'The credit card has expired. Please try again with another card or payment method.');
  define('MODULE_PAYMENT_AUTHORIZENET_CC_AIM_ERROR_CCV', 'The credit card code number (CCV) is invalid. Please check the card information and try again.');
?>
