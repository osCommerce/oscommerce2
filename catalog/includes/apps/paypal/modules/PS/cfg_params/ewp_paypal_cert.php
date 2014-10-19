<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_PS_Cfg_ewp_paypal_cert {
    var $default = '';
    var $title;
    var $description;
    var $sort_order = 1100;

    function OSCOM_PayPal_PS_Cfg_ewp_paypal_cert() {
      global $OSCOM_PayPal;

      $this->title = $OSCOM_PayPal->getDef('cfg_ps_ewp_paypal_cert_title');
      $this->description = $OSCOM_PayPal->getDef('cfg_ps_ewp_paypal_cert_desc');
    }

    function getSetField() {
      $input = tep_draw_input_field('ewp_paypal_cert', OSCOM_APP_PAYPAL_PS_EWP_PAYPAL_CERT, 'id="inputPsEwpPayPalCert"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputPsEwpPayPalCert">{$this->title}</label>

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
