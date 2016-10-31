<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_Braintree_CC_Cfg_three_d_secure {
    var $default = '0';
    var $title;
    var $description;
    var $sort_order = 350;

    function OSCOM_Braintree_CC_Cfg_three_d_secure() {
      global $OSCOM_Braintree;

      $this->title = $OSCOM_Braintree->getDef('cfg_cc_three_d_secure_title');
      $this->description = $OSCOM_Braintree->getDef('cfg_cc_three_d_secure_desc');
    }

    function getSetField() {
      global $OSCOM_Braintree;

      $input = '<input type="radio" id="threeDSecureSelectionAll" name="three_d_secure" value="1"' . (OSCOM_APP_PAYPAL_BRAINTREE_CC_THREE_D_SECURE == '1' ? ' checked="checked"' : '') . '><label for="threeDSecureSelectionAll">' . $OSCOM_Braintree->getDef('cfg_cc_three_d_secure_all_cards') . '</label>' .
               '<input type="radio" id="threeDSecureSelectionNew" name="three_d_secure" value="2"' . (OSCOM_APP_PAYPAL_BRAINTREE_CC_THREE_D_SECURE == '2' ? ' checked="checked"' : '') . '><label for="threeDSecureSelectionNew">' . $OSCOM_Braintree->getDef('cfg_cc_three_d_secure_new_cards') . '</label>' .
               '<input type="radio" id="threeDSecureSelectionDisabled" name="three_d_secure" value="0"' . (OSCOM_APP_PAYPAL_BRAINTREE_CC_THREE_D_SECURE == '0' ? ' checked="checked"' : '') . '><label for="threeDSecureSelectionDisabled">' . $OSCOM_Braintree->getDef('cfg_cc_three_d_secure_disabled') . '</label>';

      $ssl_check = '';

      if ((defined('ENABLE_SSL_CATALOG') && (ENABLE_SSL_CATALOG == 'false')) || (ENABLE_SSL == false)) {
        $ssl_check = '<div class="bt-alerts"><div class="bt-alerts-error" style="padding: 10px;">' . $OSCOM_Braintree->getDef('cfg_cc_three_d_secure_ssl_check') . '</div></div>';
      }

      $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}

    {$ssl_check}
  </p>

  <div id="threeDSecureSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#threeDSecureSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
