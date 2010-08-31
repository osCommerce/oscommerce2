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
      global $language;

      if ( defined('MODULE_HEADER_TAGS_INSTALLED') && tep_not_null(MODULE_HEADER_TAGS_INSTALLED) ) {
        $htm_array = explode(';', MODULE_HEADER_TAGS_INSTALLED);

        foreach ( $htm_array as $htm ) {
          $class = substr($htm, 0, strrpos($htm, '.'));

          if ( !class_exists($class) ) {
            include(DIR_WS_LANGUAGES . $language . '/modules/header_tags/' . $htm);
            include(DIR_WS_MODULES . 'header_tags/' . $class . '.php');
          }

          $ht = new $class();

          if ( $ht->isEnabled() ) {
            $ht->execute();
          }
        }
      }
    }
  }
?>
