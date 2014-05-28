<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/
?>
<!DOCTYPE html>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>osCommerce, Open Source E-Commerce Solutions</title>
<meta name="robots" content="noindex,nofollow" />
<link rel="icon" type="image/png" href="images/oscommerce_icon.png" />

<link rel="stylesheet" type="text/css" href="../ext/jquery/ui/redmond/jquery-ui-1.10.4.min.css" />
<script src="../ext/jquery/jquery-1.11.1.min.js"></script>
<script src="../ext/jquery/ui/jquery-ui-1.10.4.min.js"></script>

<link rel="stylesheet" type="text/css" href="templates/main_page/stylesheet.css" />
</head>

<body>

<div id="pageHeader">
  <div>
    <div style="float: right; padding-top: 40px; padding-right: 15px; color: #000000; font-weight: bold;"><a href="http://www.oscommerce.com" target="_blank">osCommerce Website</a> &nbsp;|&nbsp; <a href="http://www.oscommerce.com/support" target="_blank">Support</a> &nbsp;|&nbsp; <a href="http://www.oscommerce.info" target="_blank">Documentation</a></div>

    <a href="index.php"><img src="images/oscommerce.png" title="osCommerce Online Merchant" style="margin: 10px 10px 0 10px;" /></a>
  </div>
</div>

<div id="pageContent">
<?php require('templates/pages/' . $page_contents); ?>
</div>

<div id="pageFooter">
  <p>Copyright &copy; <?php echo date('Y'); ?> <a href="http://www.oscommerce.com" target="_blank">osCommerce</a>. All rights reserved. osCommerce is a registered trademark of Harald Ponce de Leon.</p>
</div>

</body>

</html>
