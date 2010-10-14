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
<link rel="stylesheet" type="text/css" href="ext/jquery/ui/redmond/jquery-ui-1.8.4.css">
<script type="text/javascript" src="ext/jquery/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="ext/jquery/ui/jquery-ui-1.8.4.min.js"></script>
<link rel="stylesheet" type="text/css" href="ext/960gs/<?php echo ((stripos(HTML_PARAMS, 'dir="RTL"') !== false) ? 'rtl_' : ''); ?>960_24_col.css">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
<?php echo $oscTemplate->getHeaderTags(); ?>
</head>
<body>

<div id="bodyWrapper" class="container_24">

<?php require(DIR_WS_INCLUDES . 'header.php'); ?>

<div id="bodyContent" class="grid_16 push_4">
