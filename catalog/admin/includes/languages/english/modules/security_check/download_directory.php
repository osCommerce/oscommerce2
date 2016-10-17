<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

use OSC\OM\OSCOM;

define('WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT', 'The downloadable products directory does not exist: ' . OSCOM::getConfig('dir_root', 'Shop') . 'download/. Downloadable products will not work until this directory is valid.');
?>
