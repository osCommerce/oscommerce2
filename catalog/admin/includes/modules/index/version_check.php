<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $cache_file = DIR_FS_CACHE . 'oscommerce_version_check.cache';
  $current_version = tep_get_version();
  $new_version = false;

  if (file_exists($cache_file)) {
    $date_last_checked = tep_datetime_short(date('Y-m-d H:i:s', filemtime($cache_file)));

    $releases = unserialize(implode('', file($cache_file)));

    foreach ($releases as $version) {
      $version_array = explode('|', $version);

      if (version_compare($current_version, $version_array[0], '<')) {
        $new_version = true;
        break;
      }
    }
  } else {
    $date_last_checked = ADMIN_INDEX_UPDATE_CHECK_NEVER;
  }
?>
<table border="0" width="100%" cellspacing="0" cellpadding="4">
  <tr class="dataTableHeadingRow">
    <td class="dataTableHeadingContent"><?php echo ADMIN_INDEX_UPDATE_CHECK_TITLE; ?></td>
    <td class="dataTableHeadingContent" align="right"><?php echo ADMIN_INDEX_UPDATE_CHECK_LAST_DATE_CHECK; ?></td>
  </tr>
<?php
  if ($new_version == true) {
    echo '  <tr>' .
         '    <td class="messageStackWarning" colspan="2">' . tep_image(DIR_WS_ICONS . 'warning.gif', ICON_WARNING) . '&nbsp;<strong>' . TEXT_UPGRADE_AVAILABLE . '</strong></td>' .
         '  </tr>';
  }
?>
  <tr class="dataTableRow" onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);">
    <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_VERSION_CHECK) . '">' . ADMIN_INDEX_UPDATE_CHECK_NOW . '</a>'; ?></td>
    <td class="dataTableContent" align="right"><?php echo $date_last_checked; ?></td>
  </tr>
</table>