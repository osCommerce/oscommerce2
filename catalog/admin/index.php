<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

use OSC\OM\OSCOM;

require('includes/application_top.php');

if (OSCOM::isRPC() === false) {
    $page_file = OSCOM::getSitePageFile();

    if (empty($page_file) || !file_exists($page_file)) {
        $page_file = DIR_FS_CATALOG . 'includes/error_documents/404.php';
    }

    include($page_file);
}

require('includes/application_bottom.php');
