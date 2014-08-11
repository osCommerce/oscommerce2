<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class breadcrumb {
    var $_trail;

    function breadcrumb() {
      $this->reset();
    }

    function reset() {
      $this->_trail = array();
    }

    function add($title, $link = '') {
      $this->_trail[] = array('title' => $title, 'link' => $link);
    }

    function trail($separator = NULL) {
      $trail_string = '<ol class="breadcrumb">';

    foreach (array_keys($this->_trail) as $key ) { 
      if (isset($this->_trail[$key]['link']) && tep_not_null($this->_trail[$key]['link'])) {
          $trail_string .= '<li><a href="' . $this->_trail[$key]['link'] . '">' . $this->_trail[$key]['title'] . '</a></li>' . "\n";
      } else {
          $trail_string .= '<li>' . $this->_trail[$key]['title'] . '</li>';
      }
    }

      $trail_string .= '</ol>';

      return $trail_string;
    }
  }
?>
