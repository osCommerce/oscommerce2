<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Apps;
  use OSC\OM\Registry;

  class order_total {
    var $modules;

    protected $lang;

// class constructor
    function __construct() {
      $this->lang = Registry::get('Language');

      if (defined('MODULE_ORDER_TOTAL_INSTALLED') && tep_not_null(MODULE_ORDER_TOTAL_INSTALLED)) {
        $this->modules = explode(';', MODULE_ORDER_TOTAL_INSTALLED);

        foreach($this->modules as $value) {
          if (strpos($value, '\\') !== false) {
            $class = Apps::getModuleClass($value, 'OrderTotal');

            Registry::set('OrderTotal_' . str_replace('\\', '_', $value), new $class);
          } else {
            $this->lang->loadDefinitions('modules/order_total/' . pathinfo($value, PATHINFO_FILENAME));
            include('includes/modules/order_total/' . $value);

            $class = substr($value, 0, strrpos($value, '.'));
            $GLOBALS[$class] = new $class;
          }
        }
      }
    }

    function process() {
      $order_total_array = array();
      if (is_array($this->modules)) {
        foreach($this->modules as $value) {
          if (strpos($value, '\\') !== false) {
            $OSCOM_OTM = Registry::get('OrderTotal_' . str_replace('\\', '_', $value));
          } else {
            $class = substr($value, 0, strrpos($value, '.'));

            $OSCOM_OTM = $GLOBALS[$class];
          }

          if ($OSCOM_OTM->enabled) {
            $OSCOM_OTM->output = array();
            $OSCOM_OTM->process();

            for ($i=0, $n=sizeof($OSCOM_OTM->output); $i<$n; $i++) {
              if (tep_not_null($OSCOM_OTM->output[$i]['title']) && tep_not_null($OSCOM_OTM->output[$i]['text'])) {
                $order_total_array[] = [
                  'code' => $OSCOM_OTM->code,
                  'title' => $OSCOM_OTM->output[$i]['title'],
                  'text' => $OSCOM_OTM->output[$i]['text'],
                  'value' => $OSCOM_OTM->output[$i]['value'],
                  'sort_order' => $OSCOM_OTM->sort_order
                ];
              }
            }
          }
        }
      }

      return $order_total_array;
    }

    function output() {
      $output_string = '';
      if (is_array($this->modules)) {
        foreach($this->modules as $value) {
          if (strpos($value, '\\') !== false) {
            $OSCOM_OTM = Registry::get('OrderTotal_' . str_replace('\\', '_', $value));
          } else {
            $class = substr($value, 0, strrpos($value, '.'));

            $OSCOM_OTM = $GLOBALS[$class];
          }

          if ($OSCOM_OTM->enabled) {
            $size = sizeof($OSCOM_OTM->output);
            for ($i=0; $i<$size; $i++) {
              $output_string .= '              <tr>' . "\n" .
                                '                <td align="right" class="main">' . $OSCOM_OTM->output[$i]['title'] . '</td>' . "\n" .
                                '                <td align="right" class="main">' . $OSCOM_OTM->output[$i]['text'] . '</td>' . "\n" .
                                '              </tr>';
            }
          }
        }
      }

      return $output_string;
    }
  }
?>
