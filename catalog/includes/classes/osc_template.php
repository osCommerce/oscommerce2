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
    var $_blocks = array();

    function oscTemplate() {
      $this->_title = TITLE;
    }

    function setTitle($title) {
      $this->_title = $title;
    }

    function getTitle() {
      return $this->_title;
    }

    function addBlock($block, $group) {
      $this->_blocks[$group][] = $block;
    }

    function hasBlocks($group) {
      return (isset($this->_blocks[$group]) && !empty($this->_blocks[$group]));
    }

    function getBlocks($group) {
      if ($this->hasBlocks($group)) {
        return implode("\n", $this->_blocks[$group]);
      }
    }

    function buildBlocks() {
      global $language;

      if ( defined('TEMPLATE_BLOCK_GROUPS') && tep_not_null(TEMPLATE_BLOCK_GROUPS) ) {
        $tbgroups_array = explode(';', TEMPLATE_BLOCK_GROUPS);

        foreach ($tbgroups_array as $group) {
          $module_key = 'MODULE_' . strtoupper($group) . '_INSTALLED';

          if ( defined($module_key) && tep_not_null($module_key) ) {
            $modules_array = explode(';', constant($module_key));

            foreach ( $modules_array as $module ) {
              $class = substr($module, 0, strrpos($module, '.'));

              if ( !class_exists($class) ) {
                include(DIR_WS_LANGUAGES . $language . '/modules/' . $group . '/' . $module);
                include(DIR_WS_MODULES . $group . '/' . $class . '.php');
              }

              $mb = new $class();

              if ( $mb->isEnabled() ) {
                $mb->execute();
              }
            }
          }
        }
      }
    }
  }
?>
