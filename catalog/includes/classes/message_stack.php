<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License

*/

/**
* Class messagestack 
* 
* Return a new message stack  
* 
* @example Example usage: $messageStack = new messageStack(); $messageStack->add('general', 'Error: Error 1', 'error'); $messageStack->add('general', 'Error: Error 2', 'warning'); if ($messageStack->size('general') > 0); echo $messageStack->output('general');
*/
  class messageStack extends alertBlock {

/**
 * Class constructor
 * 
 * @global type $messageToStack
 */
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

/**
 * Adds a new message
 * 
 * @param string $class The class of the message
 * @param string $message The text of the message
 * @param string $type The type of the message
 */
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

/**
 * Adds the message to the session
 * 
 * @param string $class The class of the message
 * @param string $message The class of the message
 * @param string $type The class of the message
 */
    function add_session($class, $message, $type = 'error') {


      if (!isset($_SESSION['messageToStack'])) {
        $_SESSION['messageToStack'] = array();

      }

      $_SESSION['messageToStack'][] = array('class' => $class, 'text' => $message, 'type' => $type);
    }
    
/**
 * Message reset
 */
    function reset() {
      $this->messages = array();
    }
    
/**
 * Message output
 * 
 * @param string $class
 * @return functon alertbock()
 */
    function output($class) {

      $output = array();
      for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
        if ($this->messages[$i]['class'] == $class) {
          $output[] = $this->messages[$i];
        }
      }

      return $this->alertBlock($output);
    }

/**
 * Count the numbers of messages
 * 
 * @param string $class
 * @return int
 */
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
