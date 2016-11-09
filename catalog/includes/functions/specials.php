<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Registry;

////
// Sets the status of a special product
  function tep_set_specials_status($specials_id, $status) {
    $OSCOM_Db = Registry::get('Db');

    return $OSCOM_Db->save('specials', ['status' => $status, 'date_status_change' => 'now()'], ['specials_id' => $specials_id]);
  }

////
// Auto expire products on special
  function tep_expire_specials() {
    $OSCOM_Db = Registry::get('Db');

    $Qspecials = $OSCOM_Db->query('select specials_id from :table_specials where status = 1 and now() >= expires_date and expires_date > 0');

    if ($Qspecials->fetch() !== false) {
      do {
        tep_set_specials_status($Qspecials->valueInt('specials_id'), 0);
      } while ($Qspecials->fetch());
    }
  }
?>
