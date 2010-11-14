<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require("includes/application_top.php");

  $navigation->remove_current_page();

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_INFO_SHOPPING_CART);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>" />
<link rel="stylesheet" type="text/css" href="stylesheet.css" />
</head>
<body>
<p class="main"><strong><?php echo HEADING_TITLE; ?></strong><br /><?php echo tep_draw_separator(); ?></p>
<p class="main"><strong><i><?php echo SUB_HEADING_TITLE_1; ?></i></strong><br /><?php echo SUB_HEADING_TEXT_1; ?></p>
<p class="main"><strong><i><?php echo SUB_HEADING_TITLE_2; ?></i></strong><br /><?php echo SUB_HEADING_TEXT_2; ?></p>
<p class="main"><strong><i><?php echo SUB_HEADING_TITLE_3; ?></i></strong><br /><?php echo SUB_HEADING_TEXT_3; ?></p>
<p align="right" class="main"><a href="javascript:window.close();"><?php echo TEXT_CLOSE_WINDOW; ?></a></p>
</body>
</html>
<?php
  require("includes/counter.php");
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
