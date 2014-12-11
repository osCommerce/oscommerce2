<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_PS_Cfg_ewp_public_cert_id {
    var $default = '';
    var $title;
    var $description;
    var $sort_order = 1000;

    function OSCOM_PayPal_PS_Cfg_ewp_public_cert_id() {
      global $OSCOM_PayPal;

      $this->title = $OSCOM_PayPal->getDef('cfg_ps_ewp_public_cert_id_title');
      $this->description = $OSCOM_PayPal->getDef('cfg_ps_ewp_public_cert_id_desc');
    }

    function getSetField() {
      $input = tep_draw_input_field('ewp_public_cert_id', OSCOM_APP_PAYPAL_PS_EWP_PUBLIC_CERT_ID, 'id="inputPsEwpPublicCertId"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputPsEwpPublicCertId">{$this->title}</label>

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
