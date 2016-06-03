<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  class breadcrumb {
    var $_trail;

    function __construct() {
      $this->reset();
    }

    function reset() {
      $this->_trail = array();
    }

    function add($title, $link = '') {
      $this->_trail[] = array('title' => $title, 'link' => $link);
    }

    function trail($separator = NULL) {
      $breadcrumb_count = 1;

      $trail_string = '<ol itemscope itemtype="http://schema.org/BreadcrumbList" class="breadcrumb">';

      for ($i=0, $n=sizeof($this->_trail); $i<$n; $i++) {
        if (isset($this->_trail[$i]['link']) && tep_not_null($this->_trail[$i]['link'])) {
          $trail_string .= '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="' . $this->_trail[$i]['link'] . '" itemprop="item"><span itemprop="name">' . $this->_trail[$i]['title'] . '</span></a>';
        } else {
          $trail_string .= '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span itemprop="name">' . $this->_trail[$i]['title'] . '</span>';
        }
        $trail_string .= '<meta itemprop="position" content="' . $breadcrumb_count . '" /></li>' . PHP_EOL;
        $breadcrumb_count++;
      }

      $trail_string .= '</ol>';

      return $trail_string;
    }
  }
?>
