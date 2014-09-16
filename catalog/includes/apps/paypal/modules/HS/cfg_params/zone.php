<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal_HS_Cfg_zone {
    var $default = '0';
    var $sort_order = 500;

    function getSetField() {
      $zone_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
      $zone_class_query = tep_db_query("select geo_zone_id, geo_zone_name from " . TABLE_GEO_ZONES . " order by geo_zone_name");
      while ($zone_class = tep_db_fetch_array($zone_class_query)) {
        $zone_class_array[] = array('id' => $zone_class['geo_zone_id'],
                                    'text' => $zone_class['geo_zone_name']);
      }

      $input = tep_draw_pull_down_menu('zone', $zone_class_array, OSCOM_APP_PAYPAL_HS_ZONE, 'id="inputHsZone"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputHsZone">Payment Zone</label>

    Enable this payment module globally or limit it to customers shipping to the selected zone only.
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
