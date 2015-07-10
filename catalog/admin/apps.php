<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

use OSC\OM\Apps;
use OSC\OM\OSCOM;

require('includes/application_top.php');

if (empty($_GET)) {
    OSCOM::redirect('index.php');
}

$app = basename(array_keys($_GET)[0]);

if (Apps::exists($app) && file_exists(OSCOM::BASE_DIR . 'Apps/' . $app . '/admin/content.php')) {
    include(OSCOM::BASE_DIR . 'Apps/' . $app . '/admin/content.php');
} else {
    OSCOM::redirect('index.php');
}

require('includes/application_bottom.php');
