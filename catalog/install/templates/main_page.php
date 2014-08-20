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
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>osCommerce, Starting Your Online Business</title>
<meta name="robots" content="noindex,nofollow" />
<link rel="icon" type="image/png" href="images/oscommerce_icon.png" />

<!-- Bootstrap -->
<link href="../ext/bootstrap/css/bootstrap.min.css" rel="stylesheet">

<script src="../ext/jquery/jquery-1.11.1.min.js"></script>

<script src="../ext/bootstrap/js/bootstrap.min.js"></script>

<!-- Custom -->
<link rel="stylesheet" href="templates/main_page/stylesheet.css" />
</head>

<body>

<div class="container-fluid">
  <div class="row">
    <div id="storeLogo" class="col-sm-6">
      <a href="index.php"><img src="images/oscommerce.png" title="osCommerce Online Merchant" style="margin: 10px 10px 0 10px;" /></a>
    </div>


    <div id="headerShortcuts" class="col-sm-6 text-right">
      <ul class="list-unstyled list-inline">
        <li><a href="http://www.oscommerce.com" target="_blank">osCommerce Website</a></li>
        <li><a href="http://www.oscommerce.com/support" target="_blank">Support</a></li>
        <li><a href="http://www.oscommerce.info" target="_blank">Documentation</a></li>
      </ul>
    </div>
  </div>
  
  <hr>

  <div class="clearfix"></div>

  <?php require('templates/pages/' . $page_contents); ?>


  <footer>
    <div class="text-center well well-sm"><p>osCommerce Online Merchant Copyright &copy; 2000-<?php echo date('Y'); ?> <a href="http://www.oscommerce.com" target="_blank">osCommerce</a> (<a href="http://www.oscommerce.com/Us&amp;Legal" target="_blank">Copyright and Trademark Policy</a>)</p></div>
  </footer>


</div>

</body>

</html>
