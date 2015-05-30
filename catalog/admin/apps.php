<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

use OSC\OM\OSCOM;

require('includes/application_top.php');

if (empty($_GET)) {
    OSCOM::redirect('admin/index.php');
}

$app = basename(array_keys($_GET)[0]);

if (file_exists(OSCOM::BASE_DIR . 'apps/' . $app . '/OSCOM_' . $app . '.php') && file_exists(OSCOM::BASE_DIR . 'apps/' . $app . '/admin/content.php')) {
     include(OSCOM::BASE_DIR . 'apps/' . $app . '/admin/content.php');
} else {
    OSCOM::redirect('admin/index.php');
}

require('includes/application_bottom.php');
