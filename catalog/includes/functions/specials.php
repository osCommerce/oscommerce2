<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

////
// Sets the status of a special product
  function osc_set_specials_status($specials_id, $status) {
    return osc_db_query("update " . TABLE_SPECIALS . " set status = '" . (int)$status . "', date_status_change = now() where specials_id = '" . (int)$specials_id . "'");
  }

////
// Auto expire products on special
  function osc_expire_specials() {
    $specials_query = osc_db_query("select specials_id from " . TABLE_SPECIALS . " where status = '1' and now() >= expires_date and expires_date > 0");
    if (osc_db_num_rows($specials_query)) {
      while ($specials = osc_db_fetch_array($specials_query)) {
        osc_set_specials_status($specials['specials_id'], '0');
      }
    }
  }
?>