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

    foreach ($this->_trail as $trail ) { 
      if (isset($trail['link']) && tep_not_null($trail['link'])) {
          $trail_string .= '<li><a href="' . $trail['link'] . '">' . $trail['title'] . '</a></li>' . "\n";
      } else {
          $trail_string .= '<li>' . $trail['title'] . '</li>';
      }
    }

      $trail_string .= '</ol>';

      return $trail_string;
    }
  }
?>
