<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  class shipping {
    var $modules;

// class constructor
    function shipping($module = '') {
      global $PHP_SELF;

      if (defined('MODULE_SHIPPING_INSTALLED') && tep_not_null(MODULE_SHIPPING_INSTALLED)) {
        $this->modules = explode(';', MODULE_SHIPPING_INSTALLED);

        $include_modules = array();

        if ( (tep_not_null($module)) && (in_array(substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)), $this->modules)) ) {
          $include_modules[] = array('class' => substr($module['id'], 0, strpos($module['id'], '_')), 'file' => substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)));
          $this->selected_module = $module;
        } else {
          foreach ($this->modules as $value) {
            $class = substr($value, 0, strrpos($value, '.'));
            $include_modules[] = array('class' => $class, 'file' => $value);
          }
        }

        for ($i=0, $n=sizeof($include_modules); $i<$n; $i++) {
          include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/shipping/' . $include_modules[$i]['file']);
          include(DIR_WS_MODULES . 'shipping/' . $include_modules[$i]['file']);

          $GLOBALS[$include_modules[$i]['class']] = new $include_modules[$i]['class'];
        }
      }
    }

    function quote($method = '', $module = '') {
      global $total_weight, $shipping_weight, $shipping_quoted, $shipping_num_boxes;

      $quotes_array = array();

      if (is_array($this->modules)) {
        $shipping_quoted = '';
        $shipping_num_boxes = 1;
        $shipping_weight = $total_weight;

        if (SHIPPING_BOX_WEIGHT >= $shipping_weight*SHIPPING_BOX_PADDING/100) {
          $shipping_weight = $shipping_weight+SHIPPING_BOX_WEIGHT;
        } else {
          $shipping_weight = $shipping_weight + ($shipping_weight*SHIPPING_BOX_PADDING/100);
        }

        if ($shipping_weight > SHIPPING_MAX_WEIGHT) { // Split into many boxes
          $shipping_num_boxes = ceil($shipping_weight/SHIPPING_MAX_WEIGHT);
          $shipping_weight = $shipping_weight/$shipping_num_boxes;
        }

        $include_quotes = array();

        foreach($this->modules as $value) {
          $class = substr($value, 0, strrpos($value, '.'));
          if (tep_not_null($module)) {
            if ( ($module == $class) && ($GLOBALS[$class]->enabled) ) {
              $include_quotes[] = $class;
            }
          } elseif ($GLOBALS[$class]->enabled) {
            $include_quotes[] = $class;
          }
        }

        $size = sizeof($include_quotes);
        for ($i=0; $i<$size; $i++) {
          $quotes = $GLOBALS[$include_quotes[$i]]->quote($method);
          if (is_array($quotes)) $quotes_array[] = $quotes;
        }
      }

      return $quotes_array;
    }

    function after_process()
    {
      global $order;

      if(empty($this->selected_module)) {
        // TODO: handle error here
        return;
      }

      // obtain selected module name and method id
      $id_arr = explode('_', $this->selected_module['id']);
      $module = $GLOBALS[$id_arr[0]];

      // check is for backwards compatibility
      if (!empty($module) && method_exists($module, 'after_process')) {
        $module->after_process();
      }
    }

    function cheapest() {
      if (is_array($this->modules)) {
        $rates = array();

        foreach($this->modules as $value) {
          $class = substr($value, 0, strrpos($value, '.'));
          if ($GLOBALS[$class]->enabled) {
            $quotes = $GLOBALS[$class]->quotes;
            for ($i=0, $n=sizeof($quotes['methods']); $i<$n; $i++) {
              if (isset($quotes['methods'][$i]['cost']) && tep_not_null($quotes['methods'][$i]['cost'])) {
                $rates[] = array('id' => $quotes['id'] . '_' . $quotes['methods'][$i]['id'],
                                 'title' => $quotes['module'] . ' (' . $quotes['methods'][$i]['title'] . ')',
                                 'cost' => $quotes['methods'][$i]['cost']);
              }
            }
          }
        }

        $cheapest = false;
        for ($i=0, $n=sizeof($rates); $i<$n; $i++) {
          if (is_array($cheapest)) {
            if ($rates[$i]['cost'] < $cheapest['cost']) {
              $cheapest = $rates[$i];
            }
          } else {
            $cheapest = $rates[$i];
          }
        }

        return $cheapest;
      }
    }
  }
?>
