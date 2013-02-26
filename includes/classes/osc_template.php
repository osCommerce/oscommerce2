<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class oscTemplate {
    var $_title;
    var $_blocks = array();
    var $_grid_container_width = 12;
    var $_grid_content_width = 8;
    var $_grid_column_width = 2;

    function oscTemplate() {
      $this->_title = TITLE;
    }

    function setGridContainerWidth($width) {
      $this->_grid_container_width = $width;
    }

    function getGridContainerWidth() {
      return $this->_grid_container_width;
    }

    function setGridContentWidth($width) {
      $this->_grid_content_width = $width;
    }

    function getGridContentWidth() {
      return $this->_grid_content_width;
    }

    function setGridColumnWidth($width) {
      $this->_grid_column_width = $width;
    }

    function getGridColumnWidth() {
      return $this->_grid_column_width;
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
      if ( defined('TEMPLATE_BLOCK_GROUPS') && osc_not_null(TEMPLATE_BLOCK_GROUPS) ) {
        $tbgroups_array = explode(';', TEMPLATE_BLOCK_GROUPS);

        foreach ($tbgroups_array as $group) {
          $module_key = 'MODULE_' . strtoupper($group) . '_INSTALLED';

          if ( defined($module_key) && osc_not_null(constant($module_key)) ) {
            $modules_array = explode(';', constant($module_key));

            foreach ( $modules_array as $module ) {
              $class = substr($module, 0, strrpos($module, '.'));

              if ( !class_exists($class) ) {
                include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/' . $group . '/' . $module);
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

    public function getTemplateFilename() {
      return DIR_WS_MODULES . 'templates/gosling/content/template_top.php';
    }
  }
?>
