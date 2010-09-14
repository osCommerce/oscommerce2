<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $oscTemplate->buildHeaderTags();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo tep_output_string_protected($oscTemplate->getTitle()); ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
<link rel="stylesheet" type="text/css" href="ext/jquery/ui/redmond/jquery-ui-1.8.4.css">
<script type="text/javascript" src="ext/jquery/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="ext/jquery/ui/jquery-ui-1.8.4.min.js"></script>
<?php echo $oscTemplate->getHeaderTags(); ?>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="3" cellpadding="3">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
    <td width="100%" valign="top">
<!-- body_text //-->
