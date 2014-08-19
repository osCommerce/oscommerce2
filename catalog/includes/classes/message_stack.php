<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License

  Example usage:

  $messageStack = new messageStack();
  $messageStack->add('general', 'Error: Error 1', 'error');
  $messageStack->add('general', 'Error: Error 2', 'warning');
  if ($messageStack->size('general') > 0) echo $messageStack->output('general');
*/

  class messageStack extends alertBlock {

// class constructor
    function messageStack() {
      global $messageToStack;

      $this->messages = array();

      if (isset($_SESSION['messageToStack'])) {
        for ($i=0, $n=sizeof($_SESSION['messageToStack']); $i<$n; $i++) {
          $this->add($_SESSION['messageToStack'][$i]['class'], $_SESSION['messageToStack'][$i]['text'], $_SESSION['messageToStack'][$i]['type']);
        }
        unset($_SESSION['messageToStack']);
      }
    }

// class methods
    function add($class, $message, $type = 'error') {
      if ($type == 'error') {
        $this->messages[] = array('params' => 'class="alert alert-danger"', 'class' => $class, 'text' => $message);
      } elseif ($type == 'warning') {
        $this->messages[] = array('params' => 'class="alert alert-warning"', 'class' => $class, 'text' => $message);
      } elseif ($type == 'success') {
        $this->messages[] = array('params' => 'class="alert alert-success"', 'class' => $class, 'text' => $message);
      } else {
        $this->messages[] = array('params' => 'class="alert alert-info"', 'class' => $class, 'text' => $message);
      }
    }

    function add_session($class, $message, $type = 'error') {


      if (!isset($_SESSION['messageToStack'])) {
        $_SESSION['messageToStack'] = array();

      }

      $_SESSION['messageToStack'][] = array('class' => $class, 'text' => $message, 'type' => $type);
    }

    function reset() {
      $this->messages = array();
    }

    function output($class) {

      $output = array();
      for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
        if ($this->messages[$i]['class'] == $class) {
          $output[] = $this->messages[$i];
        }
      }

      return $this->alertBlock($output);
    }

    function size($class) {
      $count = 0;

      for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
        if ($this->messages[$i]['class'] == $class) {
          $count++;
        }
      }

      return $count;
    }
  }
