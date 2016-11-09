<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_Braintree_CC_Cfg_zone {
    var $default = '0';
    var $title;
    var $description;
    var $sort_order = 600;

    function OSCOM_Braintree_CC_Cfg_zone() {
      global $OSCOM_Braintree;

      $this->title = $OSCOM_Braintree->getDef('cfg_cc_zone_title');
      $this->description = $OSCOM_Braintree->getDef('cfg_cc_zone_desc');
    }

    function getSetField() {
      global $OSCOM_Braintree;

      $zone_class_array = array(array('id' => '0', 'text' => $OSCOM_Braintree->getDef('cfg_cc_zone_global')));

      $zone_class_query = tep_db_query("select geo_zone_id, geo_zone_name from " . TABLE_GEO_ZONES . " order by geo_zone_name");
      while ($zone_class = tep_db_fetch_array($zone_class_query)) {
        $zone_class_array[] = array('id' => $zone_class['geo_zone_id'],
                                    'text' => $zone_class['geo_zone_name']);
      }

      $input = tep_draw_pull_down_menu('zone', $zone_class_array, OSCOM_APP_PAYPAL_BRAINTREE_CC_ZONE, 'id="inputCcZone"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputCcZone">{$this->title}</label>

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
