<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_Braintree_Cfg_proxy {
    var $default = '';
    var $title;
    var $description;
    var $sort_order = 400;

    function OSCOM_Braintree_Cfg_proxy() {
      global $OSCOM_Braintree;

      $this->title = $OSCOM_Braintree->getDef('cfg_proxy_title');
      $this->description = $OSCOM_Braintree->getDef('cfg_proxy_desc');
    }

    function getSetField() {
      $input = tep_draw_input_field('proxy', OSCOM_APP_PAYPAL_BRAINTREE_PROXY, 'id="inputProxy"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputProxy">{$this->title}</label>

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
