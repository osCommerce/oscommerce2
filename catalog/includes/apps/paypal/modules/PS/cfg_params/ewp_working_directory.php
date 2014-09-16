<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_PS_Cfg_ewp_working_directory {
    var $default = '';
    var $sort_order = 1200;

    function getSetField() {
      $input = tep_draw_input_field('ewp_working_directory', OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY, 'id="inputPsEwpWorkingDirectory"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputPsEwpWorkingDirectory">Working Directory</label>

    The working directory to use for temporary files.
  </p>

  <div>
    {$input}
  </div>
</div>
EOT;

      return $result;
    }
  }
?>
