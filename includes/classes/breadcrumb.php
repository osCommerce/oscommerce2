<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class breadcrumb {
    protected $_trail;

    public function __construct() {
      $this->reset();
    }

    public function reset() {
      $this->_trail = array();
    }

    public function add($title, $link = null) {
      $this->_trail[] = array('title' => $title, 'link' => $link);
    }

    public function get($separator = '/') {
      $crumbs = array();

      foreach ( $this->_trail as $crumb ) {
        if ( isset($crumb['link']) && !empty($crumb['link']) ) {
          $crumbs[] = '<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a href="' . $crumb['link'] . '" itemprop="url"><span itemprop="title">' . $crumb['title'] . '</span></a></span>';
        } else {
          $crumbs[] = $crumb['title'];
        }
      }

      $result = '';

      if ( !empty($crumbs) ) {
        $result = '<ul class="breadcrumb"><li>' . implode(' <span class="divider">' . $separator . '</span></li><li>', $crumbs) . '</li></ul>';
      }

      return $result;
    }
  }
?>
