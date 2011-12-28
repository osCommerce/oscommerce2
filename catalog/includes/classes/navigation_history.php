<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class navigationHistory {
    var $path, $snapshot;

    function navigationHistory() {
      $this->reset();
    }

    function reset() {
      $this->path = array();
      $this->snapshot = array();
    }

    function add_current_page() {
      global $request_type, $cPath;

      $set = 'true';
    $n=sizeof($this->path);
      for ($i=0; $i<$n; $i++) {
        if ( ($this->path[$i]['page'] == basename($_SERVER['PHP_SELF'])) ) {
          if (isset($cPath)) {
            if (!isset($this->path[$i]['get']['cPath'])) {
              continue;
            } else {
              if ($this->path[$i]['get']['cPath'] == $cPath) {
                array_splice($this->path, ($i+1));
                $set = 'false';
                break;
              } else {
                $old_cPath = explode('_', $this->path[$i]['get']['cPath']);
                $new_cPath = explode('_', $cPath);
        $n2=sizeof($old_cPath);
                for ($j=0; $j<$n2; $j++) {
                  if ($old_cPath[$j] != $new_cPath[$j]) {
                    array_splice($this->path, ($i));
                    $set = 'true';
                    break 2;
                  }
                }
              }
            }
          } else {
            array_splice($this->path, ($i));
            $set = 'true';
            break;
          }
        }
      }

      if ($set == 'true') {
        $this->path[] = array('page' => basename($_SERVER['PHP_SELF']),
                              'mode' => $request_type,
                              'get' => $this->filter_parameters($_GET),
                              'post' => $this->filter_parameters($_POST));
      }
    }

    function remove_current_page() {

      $last_entry_position = sizeof($this->path) - 1;
      if ($this->path[$last_entry_position]['page'] == basename($_SERVER['PHP_SELF'])) {
        unset($this->path[$last_entry_position]);
      }
    }

    function set_snapshot($page = '') {
      global $request_type;

      if (is_array($page)) {
        $this->snapshot = array('page' => $page['page'],
                                'mode' => $page['mode'],
                                'get' => $this->filter_parameters($page['get']),
                                'post' => $this->filter_parameters($page['post']));
      } else {
        $this->snapshot = array('page' => basename($_SERVER['PHP_SELF']),
                                'mode' => $request_type,
                                'get' => $this->filter_parameters($_GET),
                                'post' => $this->filter_parameters($_POST));
      }
    }

    function clear_snapshot() {
      $this->snapshot = array();
    }

    function set_path_as_snapshot($history = 0) {
      $pos = (sizeof($this->path)-1-$history);
      $this->snapshot = array('page' => $this->path[$pos]['page'],
                              'mode' => $this->path[$pos]['mode'],
                              'get' => $this->path[$pos]['get'],
                              'post' => $this->path[$pos]['post']);
    }

    function debug() {
      $n=sizeof($this->path);
      for ($i=0; $i<$n; $i++) {
        echo $this->path[$i]['page'] . '?';

        foreach($this->path[$i]['get'] as $key => $value) {
          echo $key . '=' . $value . '&';
        }
        if (sizeof($this->path[$i]['post']) > 0) {
          echo '<br />';
          foreach($this->path[$i]['post'] as $key => $value) {
            echo '&nbsp;&nbsp;<strong>' . $key . '=' . $value . '</strong><br />';
          }
        }
        echo '<br />';
      }

      if (sizeof($this->snapshot) > 0) {
        echo '<br /><br />';

        echo $this->snapshot['mode'] . ' ' . $this->snapshot['page'] . '?' . tep_array_to_string($this->snapshot['get'], array(tep_session_name())) . '<br />';
      }
    }

    function filter_parameters($parameters) {
      $clean = array();

      if (is_array($parameters)) {
         foreach($parameters as $key => $value) {
          if (strpos($key, '_nh-dns') < 1) {
            $clean[$key] = $value;
          }
        }
      }

      return $clean;
    }

    function unserialize($broken) {
      for(reset($broken);$kv=each($broken);) {
        $key=$kv['key'];
        if (gettype($this->$key)!="user function")
        $this->$key=$kv['value'];
      }
    }
  }
?>
