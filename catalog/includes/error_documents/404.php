<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

use OSC\OM\HTML;
use OSC\OM\OSCOM;

http_response_code(404);
?>

<h1>Error - Page Not Found (404)</h1>

<div>
  <?php echo HTML::button('Continue', 'glyphicon glyphicon-chevron-right', OSCOM::link('index.php', null, 'AUTO')); ?>
</div>
