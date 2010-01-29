<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/
?>

<table border="0" width="100%" cellspacing="0" cellpadding="4">
  <tr class="dataTableHeadingRow">
    <td class="dataTableHeadingContent" width="20">&nbsp;</td>
    <td class="dataTableHeadingContent"><?php echo ADMIN_INDEX_ADMIN_LOGINS_TITLE; ?></td>
    <td class="dataTableHeadingContent" align="right"><?php echo ADMIN_INDEX_ADMIN_LOGINS_DATE; ?></td>
  </tr>
<?php
  $logins_query = tep_db_query("select id, user_name, success, date_added from " . TABLE_ACTION_RECORDER . " where module = 'ar_admin_login' order by date_added desc limit 6");
  while ($logins = tep_db_fetch_array($logins_query)) {
    echo '  <tr class="dataTableRow" onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);">' .
         '    <td class="dataTableContent" align="center">' . tep_image(DIR_WS_IMAGES . 'icons/' . (($logins['success'] == '1') ? 'tick.gif' : 'cross.gif')) . '</td>' .
         '    <td class="dataTableContent"><a href="' . tep_href_link(FILENAME_ACTION_RECORDER, 'module=ar_admin_login&aID=' . (int)$logins['id']) . '">' . tep_output_string_protected($logins['user_name']) . '</a></td>' .
         '    <td class="dataTableContent" align="right">' . tep_datetime_short($logins['date_added']) . '</td>' .
         '  </tr>';
  }
?>
</table>
