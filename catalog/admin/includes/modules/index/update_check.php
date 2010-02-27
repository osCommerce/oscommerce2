<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $last_update_check = LAST_UPDATE_CHECK_TIME;
  $cache_file = DIR_FS_CACHE . '/versions.cache';
  if (!empty($last_update_check)) {
    $date_last_checked = date('F j, Y, H:i:s', $last_update_check);
  } else {
    $date_last_checked = ADMIN_INDEX_UPDATE_CHECK_NEVER;
  }
?>
<table border="0" width="100%" cellspacing="0" cellpadding="4">
  <tr class="dataTableHeadingRow">
    <td class="dataTableHeadingContent"><?php echo ADMIN_INDEX_UPDATE_CHECK_TITLE; ?></td>
    <td class="dataTableHeadingContent"></td>
  </tr>
<?php
  echo '  <tr class="dataTableRow" onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);">' .
       '    <td class="dataTableContent" align="left">' . ADMIN_INDEX_UPDATE_CHECK_DATE . ': ' . $date_last_checked . '</td>' .
       '    <td class="dataTableContent" align="right"><a href="' . tep_href_link(FILENAME_VERSION_CHECK) . '">' . ADMIN_INDEX_UPDATE_CHECK_NOW . '</a></td>' .
       '  </tr>';

if (file_exists($cache_file)) {
    $result = unserialize(join('', file($cache_file)));
    if (count($result) > 0) {
      echo '  <tr class="dataTableRow" onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);">' .
           '    <td class="dataTableContent" align="left" colspan="2"><strong>' . TEXT_UPGRADE_AVAILABLE . '</strong></td>' .
           '  </tr>';
    }
}
?>
</table>