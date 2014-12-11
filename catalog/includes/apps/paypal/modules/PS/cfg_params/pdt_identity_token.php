<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_PS_Cfg_pdt_identity_token {
    var $default = '';
    var $title;
    var $description;
    var $sort_order = 650;

    function OSCOM_PayPal_PS_Cfg_pdt_identity_token() {
      global $OSCOM_PayPal;

      $this->title = $OSCOM_PayPal->getDef('cfg_ps_pdt_identity_token_title');
      $this->description = $OSCOM_PayPal->getDef('cfg_ps_pdt_identity_token_desc');
    }

    function getSetField() {
      $input = tep_draw_input_field('pdt_identity_token', OSCOM_APP_PAYPAL_PS_PDT_IDENTITY_TOKEN, 'id="inputPsPdtIdentityToken"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputPsPdtIdentityToken">{$this->title}</label>

    {$this->description}
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
