<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

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

    $serialized = serialize($upgrade_versions);
    if ($f = @fopen(DIR_FS_CACHE . '/versions.cache', 'w')) {
      fwrite ($f, $serialized, strlen($serialized));
      fclose($f);
    }

    if (count($upgrade_versions) > 0) {
      $messageStack->add(VERSION_UPGRADES_AVAILABLE, 'error');
    } else {
      $messageStack->add(VERSION_RUNNING_LATEST, 'success');
    }
  } else {
    $messageStack->add(ERROR_COULD_NOT_CONNECT, 'error');
  }

  $last_update_check = time();
  tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . $last_update_check . "' where configuration_key = 'LAST_UPDATE_CHECK_TIME'");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body onload="SetFocus();">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td><table border="0" cellspacing="0" cellpadding="3">
              <tr>
                <td class="smallText"><b><?php echo TITLE_CURRENT_VERSION; ?></b></td>
                <td class="smallText"><?php echo $current_version; ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_VERSION; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_RELEASED; ?></td>
                <td class="dataTableHeadingContent"></td>
              </tr>
              
<?php
  if (count($upgrade_versions) > 0 ) {
    foreach ($upgrade_versions as $upgrade) {
?>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">
                <td class="dataTableContent"><?php echo $upgrade[0]; ?></td>
                <td class="dataTableContent"><?php echo date('F j, Y', mktime(0, 0, 0, substr($upgrade[1], 4, 2), substr($upgrade[1], 6, 2), substr($upgrade[1], 0, 4))); ?></td>
                <td class="dataTableContent"><?php echo '<a href="' . $upgrade[2] . '" target="_blank">' . TEXT_RELEASE_NOTES . '</a>'; ?></td>
              </tr>
<?php
    }
  }
?>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
