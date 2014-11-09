<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class hooks {
    var $_site;
    var $_hooks = array();

    function hooks($site) {
      $this->_site = basename($site);

      $this->register('global');
    }

    function register($group) {
      $group = basename($group);

      $directory = DIR_FS_CATALOG . 'includes/hooks/' . $this->_site . '/' . $group;

      if ( file_exists($directory) ) {
        if ( $dir = @dir($directory) ) {
          while ( $file = $dir->read() ) {
            if ( !is_dir($directory . '/' . $file) ) {
              if ( substr($file, strrpos($file, '.')) == '.php' ) {
                $code = substr($file, 0, strrpos($file, '.'));
                $class = 'hook_' . $this->_site . '_' . $group . '_' . $code;

                include($directory . '/' . $file);
                $GLOBALS[$class] = new $class();

                foreach ( get_class_methods($GLOBALS[$class]) as $method ) {
                  if ( substr($method, 0, 7) == 'listen_' ) {
                    $this->_hooks[$this->_site][$group][substr($method, 7)][] = $code;
                  }
                }
              }
            }
          }

          $dir->close();
        }
      }
    }

    function call($group, $action) {
      $result = '';

      foreach ( $this->_hooks[$this->_site][$group][$action] as $hook ) {
        $result .= call_user_func(array($GLOBALS['hook_' . $this->_site . '_' . $group . '_' . $hook], 'listen_' . $action));
      }

      if ( !empty($result) ) {
        return $result;
      }
    }
  }
?>
