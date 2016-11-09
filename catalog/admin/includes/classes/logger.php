<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  class logger {
    var $timer_start, $timer_stop, $timer_total;

// class constructor
    function logger() {
      $this->timer_start();
    }

    function timer_start() {
      if (defined("PAGE_PARSE_START_TIME")) {
        $this->timer_start = PAGE_PARSE_START_TIME;
      } else {
        $this->timer_start = microtime();
      }
    }

    function timer_stop($display = 'false') {
      $this->timer_stop = microtime();

      $time_start = explode(' ', $this->timer_start);
      $time_end = explode(' ', $this->timer_stop);

      $this->timer_total = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);

      $this->write($_SERVER['REQUEST_URI'], $this->timer_total . 's');

      if ($display == 'true') {
        return $this->timer_display();
      }
    }

    function timer_display() {
      return '<span class="smallText">Parse Time: ' . $this->timer_total . 's</span>';
    }

    function write($message, $type) {
      error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $type . '] ' . $message . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
    }
  }
?>
