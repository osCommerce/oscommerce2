<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  function tep_opendir($path) {
    $path = rtrim($path, '/') . '/';

    $exclude_array = array('.', '..', '.DS_Store', 'Thumbs.db');

    $result = array();

    if ($handle = opendir($path)) {
      while (false !== ($filename = readdir($handle))) {
        if (!in_array($filename, $exclude_array)) {
          $file = array('name' => $path . $filename,
                        'is_dir' => is_dir($path . $filename),
                        'writable' => tep_is_writable($path . $filename));

          $result[] = $file;

          if ($file['is_dir'] == true) {
            $result = array_merge($result, tep_opendir($path . $filename));
          }
        }
      }

      closedir($handle);
    }

    return $result;
  }

  $whitelist_array = array();

  $whitelist_query = tep_db_query("select directory from " . TABLE_SEC_DIRECTORY_WHITELIST);
  while ($whitelist = tep_db_fetch_array($whitelist_query)) {
    $whitelist_array[] = $whitelist['directory'];
  }

  $admin_dir = basename(DIR_FS_ADMIN);

  if ($admin_dir != 'admin') {
    for ($i=0, $n=sizeof($whitelist_array); $i<$n; $i++) {
      if (substr($whitelist_array[$i], 0, 6) == 'admin/') {
        $whitelist_array[$i] = $admin_dir . substr($whitelist_array[$i], 5);
      }
    }
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
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
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_DIRECTORIES; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_WRITABLE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_RECOMMENDED; ?></td>
              </tr>
<?php
  foreach (tep_opendir(DIR_FS_CATALOG) as $file) {
    if ($file['is_dir']) {
?>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">
                <td class="dataTableContent"><?php echo substr($file['name'], strlen(DIR_FS_CATALOG)); ?></td>
                <td class="dataTableContent" align="center"><?php echo tep_image(DIR_WS_IMAGES . 'icons/' . (($file['writable'] == true) ? 'tick.gif' : 'cross.gif')); ?></td>
                <td class="dataTableContent" align="center"><?php echo tep_image(DIR_WS_IMAGES . 'icons/' . (in_array(substr($file['name'], strlen(DIR_FS_CATALOG)), $whitelist_array) ? 'tick.gif' : 'cross.gif')); ?></td>
              </tr>
<?php
    }
  }
?>
              <tr>
                <td colspan="3" class="smallText"><?php echo TEXT_DIRECTORY . ' ' . DIR_FS_CATALOG; ?></td>
              </tr>
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
