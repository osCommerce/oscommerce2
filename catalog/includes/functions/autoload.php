<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  function autoload($class) {
    $prefix = 'osCommerce\\OM\\';

// only auto load related classes
    $len = strlen($prefix);

    if ( strncmp($prefix, $class, $len) !== 0 ) {
      return false;
    }

    $class = substr($class, $len);

    $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    if ( file_exists($file) ) {
      include($file);
    }
  }
?>
