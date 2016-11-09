<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_Braintree_CC_Cfg_cc_tokens {
    var $default = '0';
    var $title;
    var $description;
    var $sort_order = 200;

    function OSCOM_Braintree_CC_Cfg_cc_tokens() {
      global $OSCOM_Braintree;

      $this->title = $OSCOM_Braintree->getDef('cfg_cc_cc_tokens_title');
      $this->description = $OSCOM_Braintree->getDef('cfg_cc_cc_tokens_desc');
    }

    function getSetField() {
      global $OSCOM_Braintree;

      $input = '<input type="radio" id="ccTokensSelectionAlways" name="cc_tokens" value="2"' . (OSCOM_APP_PAYPAL_BRAINTREE_CC_CC_TOKENS == '2' ? ' checked="checked"' : '') . '><label for="ccTokensSelectionAlways">' . $OSCOM_Braintree->getDef('cfg_cc_cc_tokens_always') . '</label>' .
               '<input type="radio" id="ccTokensSelectionOptional" name="cc_tokens" value="1"' . (OSCOM_APP_PAYPAL_BRAINTREE_CC_CC_TOKENS == '1' ? ' checked="checked"' : '') . '><label for="ccTokensSelectionOptional">' . $OSCOM_Braintree->getDef('cfg_cc_cc_tokens_optional') . '</label>' .
               '<input type="radio" id="ccTokensSelectionDisabled" name="cc_tokens" value="0"' . (OSCOM_APP_PAYPAL_BRAINTREE_CC_CC_TOKENS == '0' ? ' checked="checked"' : '') . '><label for="ccTokensSelectionDisabled">' . $OSCOM_Braintree->getDef('cfg_cc_cc_tokens_disabled') . '</label>';

      $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="ccTokensSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#ccTokensSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
