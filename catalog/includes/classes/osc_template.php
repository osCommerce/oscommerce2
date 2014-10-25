<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

/**
 * Class oscTemplate
 * 
 * The main template class
 */
  class oscTemplate {
    var $_title;
    var $_blocks = array();
    var $_content = array();
    var $_grid_container_width = 12;
    var $_grid_content_width = 8;
    var $_grid_column_width = 2;
    var $_data = array();

/**
* Class constructor 
*/
    function oscTemplate() {
      $this->_title = TITLE;
    }

/**
 * Sets the container width
 * 
 * @param int $width
 */
    function setGridContainerWidth($width) {
      $this->_grid_container_width = $width;
    }
    
/**
 * Gets the container width
 * 
 * @return int 
 */
    function getGridContainerWidth() {
      return $this->_grid_container_width;
    }

/**
 * Sets the content width
 * 
 * @param int $width
 */    
    function setGridContentWidth($width) {
      $this->_grid_content_width = $width;
    }

/**
 * Gets the content width
 * 
 * @return int
 */
    function getGridContentWidth() {
      return $this->_grid_content_width;
    }

/**
 * Sets the column width
 * 
 * @param int $width
 */
    function setGridColumnWidth($width) {
      $this->_grid_column_width = $width;
    }

/**
 * Gets the column content width
 * 
 * @return int
 */    
    function getGridColumnWidth() {
      return $this->_grid_column_width;
    }

/**
 * Sets the title
 * 
 * @param string $title
 */    
    function setTitle($title) {
      $this->_title = $title;
    }

/**
 * Gets the title
 * 
 * @return string
 */    
    function getTitle() {
      return $this->_title;
    }

/**
 * Adds a new block
 * 
 * @param string $block
 * @param string $group
 */    
    function addBlock($block, $group) {
      $this->_blocks[$group][] = $block;
    }

/**
 * Checks if group has blocks
 * 
 * @param string $group
 * @return boolean
 */    
    function hasBlocks($group) {
      return (isset($this->_blocks[$group]) && !empty($this->_blocks[$group]));
    }

/**
 * Gets template blocks
 * 
 * @param type $group
 * @return string
 */    
    function getBlocks($group) {
      if ($this->hasBlocks($group)) {
        return implode("\n", $this->_blocks[$group]);
      }
    }

/**
 * Builds the template blocks
 */    
    function buildBlocks() {
      if ( defined('TEMPLATE_BLOCK_GROUPS') && tep_not_null(TEMPLATE_BLOCK_GROUPS) ) {
        $tbgroups_array = explode(';', TEMPLATE_BLOCK_GROUPS);

        foreach ($tbgroups_array as $group) {
          $module_key = 'MODULE_' . strtoupper($group) . '_INSTALLED';

          if ( defined($module_key) && tep_not_null(constant($module_key)) ) {
            $modules_array = explode(';', constant($module_key));

            foreach ( $modules_array as $module ) {
              $class = basename($module, '.php');

              if ( !class_exists($class) ) {
                if ( file_exists(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/' . $group . '/' . $module) ) {
                  include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/' . $group . '/' . $module);
                }

                if ( file_exists(DIR_WS_MODULES . $group . '/' . $class . '.php') ) {
                  include(DIR_WS_MODULES . $group . '/' . $class . '.php');
                }
              }

              if ( class_exists($class) ) {
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

/**
 * Adds content to the group
 * 
 * @param string $content
 * @param string $group
 */    
    function addContent($content, $group) {
      $this->_content[$group][] = $content;
    }

/**
 * Checks if group has content
 * 
 * @param string $group
 * @return string
 */
    function hasContent($group) {
      return (isset($this->_content[$group]) && !empty($this->_content[$group]));
    }

 /**
  * Gets the content of the group
  * 
  * @param string $group
  * @return string
  */
    function getContent($group) {
      if ( !class_exists('tp_' . $group) && file_exists(DIR_WS_MODULES . 'pages/tp_' . $group . '.php') ) {
        include(DIR_WS_MODULES . 'pages/tp_' . $group . '.php');
      }

      if ( class_exists('tp_' . $group) ) {
        $template_page_class = 'tp_' . $group;
        $template_page = new $template_page_class();
        $template_page->prepare();
      }

      foreach ( $this->getContentModules($group) as $module ) {
        if ( !class_exists($module) ) {
          if ( file_exists(DIR_WS_MODULES . 'content/' . $group . '/' . $module . '.php') ) {
            if ( file_exists(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/content/' . $group . '/' . $module . '.php') ) {
              include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/content/' . $group . '/' . $module . '.php');
            }

            include(DIR_WS_MODULES . 'content/' . $group . '/' . $module . '.php');
          }
        }

        if ( class_exists($module) ) {
          $mb = new $module();

          if ( $mb->isEnabled() ) {
            $mb->execute();
          }
        }
      }

      if ( class_exists('tp_' . $group) ) {
        $template_page->build();
      }

      if ($this->hasContent($group)) {
        return implode("\n", $this->_content[$group]);
      }
    }

/**
 * Gets the content modules
 * 
 * @param string $group
 * @return array
 */    
    function getContentModules($group) {
      $result = array();

      foreach ( explode(';', MODULE_CONTENT_INSTALLED) as $m ) {
        $module = explode('/', $m, 2);

        if ( $module[0] == $group ) {
          $result[] = $module[1];
        }
      }

      return $result;
    }
  }
?>
