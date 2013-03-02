<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License

  Example usage:

  $messageStack = new messageStack();
  $messageStack->add('Error: Error 1', 'error');
  $messageStack->add('Error: Error 2', 'warning');
  if ($messageStack->size > 0) echo $messageStack->output();
*/

  class messageStack extends alertBlock {
    var $size = 0;

    function messageStack() {
      $this->errors = array();

      if (isset($_SESSION['messageToStack'])) {
        for ($i = 0, $n = sizeof($_SESSION['messageToStack']); $i < $n; $i++) {
          $this->add($_SESSION['messageToStack'][$i]['text'], $_SESSION['messageToStack'][$i]['type']);
        }
        unset($_SESSION['messageToStack']);
      }
    }

    function add($message, $type = 'error') {
      if ($type == 'error') {
        $this->errors[] = array('params' => 'class="alert alert-error"', 'text' => $message);
      } elseif ($type == 'warning') {
        $this->errors[] = array('params' => 'class="alert alert-block"', 'text' => '<p>' . $message . '</p>');
      } elseif ($type == 'success') {
        $this->errors[] = array('params' => 'class="alert alert-success"', 'text' => '<p>' . $message . '</p>');
      } else {
        $this->errors[] = array('params' => 'class="alert alert-info"', 'text' => '<p>' . $message . '</p>');
      }

      $this->size++;
    }

    function add_session($message, $type = 'error') {
      if (!isset($_SESSION['messageToStack'])) {
        $_SESSION['messageToStack'] = array();
      }

      $_SESSION['messageToStack'][] = array('text' => $message, 'type' => $type);
    }

    function reset() {
      $this->errors = array();
      $this->size = 0;
    }

    function output() {
     // $this->table_data_parameters = 'class="messageBox"';
      return $this->alertBlock($this->errors);
    }
  }
?>
