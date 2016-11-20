<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Apps;
  use OSC\OM\Registry;

  class shipping {
    var $modules;

    protected $lang;

// class constructor
    function __construct($module = '') {
      global $PHP_SELF;

      $this->lang = Registry::get('Language');

      if (defined('MODULE_SHIPPING_INSTALLED') && tep_not_null(MODULE_SHIPPING_INSTALLED)) {
        $this->modules = explode(';', MODULE_SHIPPING_INSTALLED);

        $include_modules = array();

        $code = null;

        if (isset($module) && is_array($module) && isset($module['id'])) {
          if (strpos($module['id'], '\\') !== false) {
            list($vendor, $app, $module) = explode('\\', $module['id']);
            list($module, $method) = explode('_', $module);

            $code = $vendor . '\\' . $app . '\\' . $module;
          } elseif (strpos($module['id'], '_') !== false) {
            $code = substr($module['id'], 0, strpos($module['id'], '_'));
          }
        }

        if (isset($code) && (in_array($code . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)), $this->modules) || in_array($code, $this->modules))) {
          if (strpos($code, '\\') !== false) {
            $class = Apps::getModuleClass($code, 'Shipping');

            $include_modules[] = [
              'class' => $code,
              'file' => $class
            ];
          } else {
            $include_modules[] = [
                'class' => $code,
                'file' => $code . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1))
            ];
          }
        } else {
          foreach ($this->modules as $value) {
            if (strpos($value, '\\') !== false) {
              $class = Apps::getModuleClass($value, 'Shipping');

              $include_modules[] = [
                'class' => $value,
                'file' => $class
              ];
            } else {
              $class = substr($value, 0, strrpos($value, '.'));

              $include_modules[] = [
                'class' => $class,
                'file' => $value
              ];
            }
          }
        }

        for ($i=0, $n=sizeof($include_modules); $i<$n; $i++) {
          if (strpos($include_modules[$i]['class'], '\\') !== false) {
            Registry::set('Shipping_' . str_replace('\\', '_', $include_modules[$i]['class']), new $include_modules[$i]['file']);
          } else {
            $this->lang->loadDefinitions('modules/shipping/' . pathinfo($include_modules[$i]['file'], PATHINFO_FILENAME));
            include('includes/modules/shipping/' . $include_modules[$i]['file']);

            $GLOBALS[$include_modules[$i]['class']] = new $include_modules[$i]['class'];
          }
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
          if (strpos($value, '\\') !== false) {
            $obj = Registry::get('Shipping_' . str_replace('\\', '_', $value));

            if (tep_not_null($module)) {
              if ( ($module == $value) && ($obj->enabled) ) {
                $include_quotes[] = $value;
              }
            } elseif ($obj->enabled) {
              $include_quotes[] = $value;
            }
          } else {
            $class = substr($value, 0, strrpos($value, '.'));

            if (tep_not_null($module)) {
              if ( ($module == $class) && ($GLOBALS[$class]->enabled) ) {
                $include_quotes[] = $class;
              }
            } elseif ($GLOBALS[$class]->enabled) {
              $include_quotes[] = $class;
            }
          }
        }

        $size = sizeof($include_quotes);
        for ($i=0; $i<$size; $i++) {
          if (strpos($include_quotes[$i], '\\') !== false) {
            $quotes = Registry::get('Shipping_' . str_replace('\\', '_', $include_quotes[$i]))->quote($method);
          } else {
            $quotes = $GLOBALS[$include_quotes[$i]]->quote($method);
          }

          if (is_array($quotes)) {
            $quotes_array[] = $quotes;
          }
        }
      }

      return $quotes_array;
    }

    function get_first() {
      foreach ( $this->modules as $value ) {
        if (strpos($value, '\\') !== false) {
          $obj = Registry::get('Shipping_' . str_replace('\\', '_', $value));
        } else {
          $class = substr($value, 0, strrpos($value, '.'));

          $obj = $GLOBALS[$class];
        }

        if ( $obj->enabled ) {
          foreach ( $obj->quotes['methods'] as $method ) {
            if ( isset($method['cost']) && tep_not_null($method['cost']) ) {
              return [
                'id' => $obj->quotes['id'] . '_' . $method['id'],
                'title' => $obj->quotes['module'] . (isset($method['title']) && !empty($method['title']) ? ' (' . $method['title'] . ')' : ''),
                'cost' => $method['cost']
              ];
            }
          }
        }
      }
    }

    function cheapest() {
      if (is_array($this->modules)) {
        $rates = array();

        foreach($this->modules as $value) {
          if (strpos($value, '\\') !== false) {
            $obj = Registry::get('Shipping_' . str_replace('\\', '_', $value));
          } else {
            $class = substr($value, 0, strrpos($value, '.'));

            $obj = $GLOBALS[$class];
          }

          if ($obj->enabled) {
            $quotes = $obj->quotes;

            for ($i=0, $n=sizeof($quotes['methods']); $i<$n; $i++) {
              if (isset($quotes['methods'][$i]['cost']) && tep_not_null($quotes['methods'][$i]['cost'])) {
                $rates[] = [
                  'id' => $quotes['id'] . '_' . $quotes['methods'][$i]['id'],
                  'title' => $quotes['module'] . (isset($quotes['methods'][$i]['title']) && !empty($quotes['methods'][$i]['title']) ? ' (' . $quotes['methods'][$i]['title'] . ')' : ''),
                  'cost' => $quotes['methods'][$i]['cost']
                ];
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
