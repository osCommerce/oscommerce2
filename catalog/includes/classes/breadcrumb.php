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

    function trail($separator = ' - ') {
      $trail_string = ' ';
$n=sizeof($this->_trail);
      for ($i=0;  $i<$n; $i++) {
        if (isset($this->_trail[$i]['link']) && tep_not_null($this->_trail[$i]['link'])) {
          $trail_string .= '<div itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb" style="float:left;"><a href="' . $this->_trail[$i]['link'] . '" itemprop="url" class="headerNavigation"><span itemprop="title">' . $this->_trail[$i]['title'] . '</span></a>&nbsp;&raquo;&nbsp;</div>';
        } else {
          $trail_string .= $this->_trail[$i]['title'];
        }

        if (($i+1) < $n) $trail_string .= $separator;
   $trail_string .= '</div>';
      }
   
      return $trail_string;
    }
  } 
?>