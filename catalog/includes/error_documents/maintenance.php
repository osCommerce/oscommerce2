<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

use OSC\OM\HTML;
use OSC\OM\OSCOM;

http_response_code(503);
header('Retry-After: 300');
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Maintenance</title>
<link href="<?= OSCOM::link('Shop/ext/bootstrap/css/bootstrap.min.css', null, false); ?>" rel="stylesheet">
<link href="<?= OSCOM::link('Shop/ext/font-awesome/4.7.0/css/font-awesome.min.css', null, false); ?>" rel="stylesheet">
<script src="<?= OSCOM::link('Shop/ext/jquery/jquery-3.1.1.min.js', null, false); ?>"></script>
</head>
<body>
<div class="container">
  <div class="jumbotron" style="margin-top: 40px;">
    <h1>We'll be back soon!</h1>

    <p>We're currently working on and improving our website. We'll be back in a few moments..</p>

    <p style="margin-top: 40px;"><?= HTML::button('Return to website', 'fa fa-refresh', OSCOM::link('index.php'), null, 'btn-primary'); ?></p>
  </div>
</div>
<script src="<?= OSCOM::link('Shop/ext/bootstrap/js/bootstrap.min.js', null, false); ?>"></script>
</body>
</html>
