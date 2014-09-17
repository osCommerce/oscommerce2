<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_LOGIN_Cfg_theme {
    var $default = 'Blue';
    var $sort_order = 600;

    function getSetField() {
      $input = '<input type="radio" id="themeSelectionBlue" name="theme" value="Blue"' . (OSCOM_APP_PAYPAL_LOGIN_THEME == 'Blue' ? ' checked="checked"' : '') . '><label for="themeSelectionBlue">Blue</label>' .
               '<input type="radio" id="themeSelectionNeutral" name="theme" value="Neutral"' . (OSCOM_APP_PAYPAL_LOGIN_THEME == 'Neutral' ? ' checked="checked"' : '') . '><label for="themeSelectionNeutral">Neutral</label>';

      $result = <<<EOT
<div>
  <p>
    <label>Theme</label>

    The theme to use for the style of the login button.
  </p>

  <div id="themeSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#themeSelection').buttonset();
});
</script>
EOT;

      return $result;
    }
  }
?>
