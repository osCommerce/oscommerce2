<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_Braintree_CC_Cfg_status {
    var $default = '1';
    var $title;
    var $description;
    var $sort_order = 100;

    function OSCOM_Braintree_CC_Cfg_status() {
      global $OSCOM_Braintree;

      $this->title = $OSCOM_Braintree->getDef('cfg_cc_status_title');
      $this->description = $OSCOM_Braintree->getDef('cfg_cc_status_desc');
    }

    function getSetField() {
      global $OSCOM_Braintree;

      $input = '<input type="radio" id="statusSelectionLive" name="status" value="1"' . (OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS == '1' ? ' checked="checked"' : '') . '><label for="statusSelectionLive">' . $OSCOM_Braintree->getDef('cfg_cc_status_live') . '</label>' .
               '<input type="radio" id="statusSelectionSandbox" name="status" value="0"' . (OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS == '0' ? ' checked="checked"' : '') . '><label for="statusSelectionSandbox">' . $OSCOM_Braintree->getDef('cfg_cc_status_sandbox') . '</label>' .
               '<input type="radio" id="statusSelectionDisabled" name="status" value="-1"' . (OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS == '-1' ? ' checked="checked"' : '') . '><label for="statusSelectionDisabled">' . $OSCOM_Braintree->getDef('cfg_cc_status_disabled') . '</label>';

      $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="statusSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#statusSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
