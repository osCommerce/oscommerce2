<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

$last_update_check = LAST_UPDATE_CHECK_TIME;
$check_version = false;
if (isset($HTTP_GET_VARS['version_check'])) {
  $check_version = true;
  $upgrade_versions = array();
  $current_version = trim(file_get_contents(DIR_FS_CATALOG . '/includes/version.php'));
  $major_version = substr($current_version, 0, 1);
  $versions = @file('http://www.oscommerce.com/version/online_merchant/' . $major_version, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  if (is_array($versions) && count($versions) > 0) {
    foreach ($versions as $version) {
      $arrversion = explode('|', $version);
      if ( version_compare($current_version, $arrversion[0], '<') ) {
        $upgrade_versions[] = $arrversion;
      }
    }
  }
  $last_update_check = time();
  tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . $last_update_check . "' where configuration_key = 'LAST_UPDATE_CHECK_TIME'");
}

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
       '    <td class="dataTableContent" align="right"><a href="' . tep_href_link(FILENAME_DEFAULT, 'version_check=now') . '">' . ADMIN_INDEX_UPDATE_CHECK_NOW . '</a></td>' .
       '  </tr>';
?>
</table>

<?php
if ($check_version == true) {
?>
<table border="0" width="100%" cellspacing="0" cellpadding="4">
  <tr class="dataTableHeadingRow">
    <td class="dataTableHeadingContent"><?php echo ADMIN_INDEX_UPDATE_UPGRADES_VERSION; ?></td>
    <td class="dataTableHeadingContent"><?php echo ADMIN_INDEX_UPDATE_UPGRADES_RELEASED; ?></td>
    <td class="dataTableHeadingContent"></td>
  </tr>

<?php
  if (count($upgrade_versions) > 0 ) {
    foreach ($upgrade_versions as $upgrade) {
      echo '  <tr class="dataTableRow" onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);">' .
           '    <td class="dataTableContent">' . $upgrade[0] . '</td>' .
           '    <td class="dataTableContent" style="white-space: nowrap;">' . date("F j, Y", strtotime($upgrade[1])) . '</td>' .
           '    <td class="dataTableContent" align="right" style="white-space: nowrap;"><a href="' . $upgrade[2] . '" target="_blank">' . ADMIN_INDEX_UPDATE_UPGRADES_DOWNLOAD . '</a></td>' .
           '  </tr>';
    }
  } else {
    echo '  <tr class="dataTableRow">' .
         '    <td class="dataTableContent" colspan="3">' . ADMIN_INDEX_UPDATE_RUNNING_LATEST . '</td>' .
         '  </tr>';
  }
?>
</table>
<?php
}
?>