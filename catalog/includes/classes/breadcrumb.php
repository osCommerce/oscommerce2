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

      for ($i=0, $n=sizeof($this->_trail); $i<$n; $i++) {
        if (isset($this->_trail[$i]['link']) && tep_not_null($this->_trail[$i]['link'])) {
          $trail_string .= '<li><a href="' . $this->_trail[$i]['link'] . '">' . $this->_trail[$i]['title'] . '</a></li>' . "\n";
        } else {
          $trail_string .= '<li>' . $this->_trail[$i]['title'] . '</li>';
        }
      }

      $trail_string .= '</ol>';

      return $trail_string;
    }
  }
?>
