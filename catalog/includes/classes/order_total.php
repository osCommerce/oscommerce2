<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

/**
 * Class order total
 * 
 * Loads the order total modules
 */
  class order_total {
    var $modules;

/**
 * Class constructor
 */
    function order_total() {
      if (defined('MODULE_ORDER_TOTAL_INSTALLED') && tep_not_null(MODULE_ORDER_TOTAL_INSTALLED)) {
        $this->modules = explode(';', MODULE_ORDER_TOTAL_INSTALLED);

        foreach($this->modules as $value) {
          include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/order_total/' . $value);
          include(DIR_WS_MODULES . 'order_total/' . $value);

          $class = substr($value, 0, strrpos($value, '.'));
          $GLOBALS[$class] = new $class;
        }
      }
    }

/**
 * Process the order
 * 
 * @return array The total order array
 */
    function process() {
      $order_total_array = array();
      if (is_array($this->modules)) {
        foreach($this->modules as $value) {
          $class = substr($value, 0, strrpos($value, '.'));
          if ($GLOBALS[$class]->enabled) {
            $GLOBALS[$class]->output = array();
            $GLOBALS[$class]->process();

            for ($i=0, $n=sizeof($GLOBALS[$class]->output); $i<$n; $i++) {
              if (tep_not_null($GLOBALS[$class]->output[$i]['title']) && tep_not_null($GLOBALS[$class]->output[$i]['text'])) {
                $order_total_array[] = array('code' => $GLOBALS[$class]->code,
                                             'title' => $GLOBALS[$class]->output[$i]['title'],
                                             'text' => $GLOBALS[$class]->output[$i]['text'],
                                             'value' => $GLOBALS[$class]->output[$i]['value'],
                                             'sort_order' => $GLOBALS[$class]->sort_order);
              }
            }
          }
        }
      }

      return $order_total_array;
    }

/**
 * Order total output
 * 
 * @return string
 */
    function output() {
      $output_string = '';
      if (is_array($this->modules)) {
        foreach($this->modules as $value) {
          $class = substr($value, 0, strrpos($value, '.'));
          if ($GLOBALS[$class]->enabled) {
            $size = sizeof($GLOBALS[$class]->output);
            for ($i=0; $i<$size; $i++) {
              $output_string .= '              <tr>' . "\n" .
                                '                <td align="right" class="main">' . $GLOBALS[$class]->output[$i]['title'] . '</td>' . "\n" .
                                '                <td align="right" class="main">' . $GLOBALS[$class]->output[$i]['text'] . '</td>' . "\n" .
                                '              </tr>';
            }
          }
        }
      }

      return $output_string;
    }
  }
?>
