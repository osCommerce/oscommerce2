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

    function trail($separator = NULL) { ?>
      <ol class="breadcrumb">
<?php
      foreach ($this->_trail as $trail ) { 
      if (isset($trail['link']) && tep_not_null($trail['link'])) { ?>

          <li><a href="<?php echo $trail['link'] . '">' . $trail['title']; ?></a></li>
<?php      } else { ?>
           <li><?php echo $trail['title']; ?></li>
<?php       }
    }
?>
       </ol>
<?php
    }
  }
?>
