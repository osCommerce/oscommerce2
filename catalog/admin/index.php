<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $languages = tep_get_languages();
  $languages_array = array();
  $languages_selected = DEFAULT_LANGUAGE;
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    $languages_array[] = array('id' => $languages[$i]['code'],
                               'text' => $languages[$i]['name']);
    if ($languages[$i]['directory'] == $language) {
      $languages_selected = $languages[$i]['code'];
    }
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<meta name="robots" content="noindex,nofollow">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetFocus();">
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
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2" height="40">
          <tr>
            <td class="pageHeading"><?php echo STORE_NAME; ?></td>

<?php
  if (sizeof($languages_array) > 1) {
?>

            <td class="pageHeading" align="right"><?php echo tep_draw_form('adminlanguage', FILENAME_DEFAULT, '', 'get') . tep_draw_pull_down_menu('language', $languages_array, $languages_selected, 'onChange="this.form.submit();"') . tep_hide_session_id() . '</form>'; ?></td>

<?php
  }
?>

          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
  $files = array();
  if ($dir = @dir(DIR_FS_ADMIN . 'includes/modules/index')) {
    while ($file = $dir->read()) {
      if (!is_dir($module_directory . $file)) {
        if (substr($file, strrpos($file, '.')) == $file_extension) {
          $files[] = $file;
        }
      }
    }
    sort($files);
    $dir->close();
  }

  $col = 0;

  for ($i=0, $n=sizeof($files); $i<$n; $i++) {
    if (file_exists(DIR_WS_LANGUAGES . $language . '/modules/index/' . $files[$i])) {
      include(DIR_WS_LANGUAGES . $language . '/modules/index/' . $files[$i]);
    }

    if ($col < 1) {
      echo '          <tr>' . "\n";
    }

    $col++;

    if ($col <= 2) {
      echo '            <td width="50%" valign="top">' . "\n";
    }

    include('includes/modules/index/' . $files[$i]);

    if ($col <= 2) {
      echo '            </td>' . "\n";
    }

    if ( !isset($files[$i+1]) || ($col == 2) ) {
      if ( !isset($files[$i+1]) && ($col == 1) ) {
        echo '            <td width="50%" valign="top">&nbsp;</td>' . "\n";
      }

      $col = 0;

      echo '  </tr>' . "\n";
    }
  }
?>
        </table></td>
      </tr>
    </table></td>
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
