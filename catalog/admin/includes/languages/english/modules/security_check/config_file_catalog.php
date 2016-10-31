<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

use OSC\OM\OSCOM;

define('WARNING_CONFIG_FILE_WRITEABLE', 'I am able to write to the configuration file: ' . OSCOM::getConfig('dir_root', 'Shop') . 'includes/configure.php. This is a potential security risk - please set the right user permissions on this file.');
?>
