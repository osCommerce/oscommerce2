<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  $counter_query = osc_db_query("select startdate, counter from " . TABLE_COUNTER);

  if (!osc_db_num_rows($counter_query)) {
    $date_now = date('Ymd');
    osc_db_query("insert into " . TABLE_COUNTER . " (startdate, counter) values ('" . $date_now . "', '1')");
    $counter_startdate = $date_now;
    $counter_now = 1;
  } else {
    $counter = osc_db_fetch_array($counter_query);
    $counter_startdate = $counter['startdate'];
    $counter_now = ($counter['counter'] + 1);
    osc_db_query("update " . TABLE_COUNTER . " set counter = '" . $counter_now . "'");
  }

  $counter_startdate_formatted = strftime(DATE_FORMAT_LONG, mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));
?>
