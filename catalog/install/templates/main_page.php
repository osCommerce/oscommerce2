<?php
/*
  $Id: main_page.php,v 1.4 2003/07/09 10:49:48 dgw_ Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>

<head>

<title>osCommerce :// Open Source E-Commerce Solutions</title>

<meta name="ROBOTS" content="NOFOLLOW">

<link rel="stylesheet" type="text/css" href="templates/main_page/stylesheet.css">

<script language="javascript" src="templates/main_page/javascript.js"></script>

</head>

<body text="#000000" bgcolor="#ffffff" leftmargin="0" topmargin="0" marginheight="0" marginwidth="0">

<?php require('templates/main_page/header.php'); ?>

<table cellspacing="0" cellpadding="0" width="100%" border="0" align="center">
  <tr>
    <td width="5%" class="leftColumn" valign="top" background="images/layout/left_column_background.gif"><img src="images/layout/left_column_top.gif"></td>
    <td width="85%" valign="top"><?php require('templates/pages/' . $page_contents); ?></td>
    <td width="5%" class="rightColumn" valign="top"><img src="images/layout/right_column_upper_curve.gif" width="47"></td>
  </tr>
</table>

<?php require('templates/main_page/footer.php'); ?>

</body>

</html>
