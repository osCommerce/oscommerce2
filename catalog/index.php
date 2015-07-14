<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

use OSC\OM\OSCOM;

require('includes/application_top.php');

$page_file = OSCOM::getSitePageFile();

if (!empty($page_file) && file_exists($page_file)) {
    include($page_file);
} else {
    http_response_code(404);
}

require('includes/application_bottom.php');
