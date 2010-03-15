<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class oscTemplate {
    var $_title;
    var $_header_tags = array();

    function oscTemplate() {
      $this->_title = TITLE;
    }

    function setTitle($title) {
      $this->_title = $title;
    }

    function getTitle() {
      return $this->_title;
    }

    function addHeaderTag($tag) {
      $this->_header_tags[] = $tag;
    }

    function getHeaderTags() {
      if (!empty($this->_header_tags)) {
        return implode("\n", $this->_header_tags);
      }
    }

    function buildHeaderTags() {
      global $PHP_SELF;

      $req_module = substr(basename($PHP_SELF), 0, strpos(basename($PHP_SELF), '.'));
      $directory = realpath(DIR_FS_CATALOG . DIR_WS_MODULES . 'header_tags/' . $req_module);

      if (substr($directory, 0, strlen(realpath(DIR_FS_CATALOG . DIR_WS_MODULES . 'header_tags'))) == realpath(DIR_FS_CATALOG . DIR_WS_MODULES . 'header_tags')) {
        if (file_exists($directory)) {
          $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
          $directory_array = array();
          if ($dir = @dir($directory)) {
            while ($file = $dir->read()) {
              if (!is_dir(DIR_FS_CATALOG . DIR_WS_MODULES . 'header_tags/' . $file)) {
                if (substr($file, strrpos($file, '.')) == $file_extension) {
                  $directory_array[] = $file;
                }
              }
            }
            sort($directory_array);
            $dir->close();
          }

          foreach ($directory_array as $file) {
            $module = 'ht_' . $this->_toCamel($req_module) . '_' . substr($file, 0, strpos($file, '.'));

            if (!class_exists($module)) {
              include($directory . '/' . $file);
            }

            call_user_func(array($module, 'parse'));
          }
        }
      }
    }

    function _toCamel($string) {
      $parts = explode('_', $string);
      $parts = $parts ? array_map('ucfirst', $parts) : array($string);
      $parts[0] = strtolower(substr($parts[0], 0, 1)) . substr($parts[0], 1);

      return implode('', $parts);
    }
  }
?>
