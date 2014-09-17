<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_LOGIN_Cfg_content_width {
    var $default = 'Full';
    var $title = 'Content Width';
    var $description = 'Should the content be shown in a full or half width container?';
    var $app_configured = false;
    var $set_func = 'tep_cfg_select_option(array(\'Full\', \'Half\'), ';

    function getSetField() {
      $input = '<input type="radio" id="contentWidthSelectionHalf" name="content_width" value="Half"' . (OSCOM_APP_PAYPAL_LOGIN_CONTENT_WIDTH == 'Half' ? ' checked="checked"' : '') . '><label for="contentWidthSelectionHalf">Half</label>' .
               '<input type="radio" id="contentWidthSelectionFull" name="content_width" value="Full"' . (OSCOM_APP_PAYPAL_LOGIN_CONTENT_WIDTH == 'Full' ? ' checked="checked"' : '') . '><label for="contentWidthSelectionFull">Full</label>';

      $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="contentWidthSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#contentWidthSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
