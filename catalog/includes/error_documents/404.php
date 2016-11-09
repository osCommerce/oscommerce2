<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

use OSC\OM\HTML;
use OSC\OM\OSCOM;

http_response_code(404);
?>

<h1>Error - Page Not Found (404)</h1>

<div>
  <?php echo HTML::button('Continue', 'glyphicon glyphicon-chevron-right', OSCOM::link('index.php')); ?>
</div>
