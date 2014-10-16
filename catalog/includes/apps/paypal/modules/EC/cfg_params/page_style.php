<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_EC_Cfg_page_style {
    var $default = '';
    var $title;
    var $description;
    var $sort_order = 600;

    function OSCOM_PayPal_EC_Cfg_page_style() {
      global $OSCOM_PayPal;

      $this->title = $OSCOM_PayPal->getDef('cfg_ec_page_style_title');
      $this->description = $OSCOM_PayPal->getDef('cfg_ec_page_style_desc');
    }

    function getSetField() {
      $input = tep_draw_input_field('page_style', OSCOM_APP_PAYPAL_EC_PAGE_STYLE, 'id="inputEcPageStyle"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputEcPageStyle">{$this->title}</label>

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
