<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class navigation_history {
    protected $_data = array();
    protected $_snapshot = array();

    public function __construct($add_current_page = false) {
      if ( isset($_SESSION['NavigationHistory']['data']) && is_array($_SESSION['NavigationHistory']['data']) && !empty($_SESSION['NavigationHistory']['data']) ) {
        $this->_data =& $_SESSION['NavigationHistory']['data'];
      }

      if ( isset($_SESSION['NavigationHistory']['snapshot']) && is_array($_SESSION['NavigationHistory']['snapshot']) && !empty($_SESSION['NavigationHistory']['snapshot']) ) {
        $this->_snapshot =& $_SESSION['NavigationHistory']['snapshot'];
      }

      if ( $add_current_page === true ) {
        $this->addCurrentPage();
      }
    }

    public function addCurrentPage() {
      global $OSCOM_APP, $request_type;

      $app = isset($OSCOM_APP) ? $OSCOM_APP->getCode() : 'index';

      $action_counter = 0;
      $application_key = null;
      $action = array();

      foreach ( $_GET as $key => $value ) {
        if ( !isset($application_key) && ($key == $app) ) {
          $application_key = $action_counter;

          $action_counter++;

          continue;
        }

        $action[$key] = $value;

        if ( $this->applicationActionExists(implode('/', array_keys($action))) === false ) {
          array_pop($action);

          break;
        }

        $action_counter++;
      }

      $action_get = http_build_query($action);

      for ( $i=0, $n=sizeof($this->_data); $i<$n; $i++ ) {
        if ( ($this->_data[$i]['application'] == $app) && ($this->_data[$i]['action'] == $action_get) ) {
          array_splice($this->_data, $i);
          break;
        }
      }

      $this->_data[] = array('page' => null,
                             'application' => $app,
                             'action' => $action_get,
                             'mode' => $request_type,
                             'get' => array_slice($_GET, $action_counter),
                             'post' => $_POST);

      if ( !isset($_SESSION['NavigationHistory']['data']) ) {
        $_SESSION['NavigationHistory']['data'] = $this->_data;
      }
    }

    public function removeCurrentPage() {
      array_pop($this->_data);

      if ( empty($this->_data) ) {
        $this->resetPath();
      }
    }

    public function hasPath($back = 1) {
      if ( (is_numeric($back) === false) || (is_numeric($back) && ($back < 1)) ) {
        $back = 1;
      }

      return isset($this->_data[count($this->_data) - $back]);
    }

    public function getPathURL($back = 1, $exclude = array()) {
      if ( (is_numeric($back) === false) || (is_numeric($back) && ($back < 1)) ) {
        $back = 1;
      }

      $back = count($this->_data) - $back;

      if ( isset($this->_data[$back]['page']) ) {
        return osc_href_link(null, $this->parseParameters($this->_data[$back]['get'], $exclude), $this->_data[$back]['mode'], true, true, $this->_data[$back]['page']);
      } else {
        return osc_href_link($this->_data[$back]['application'], $this->_data[$back]['action'] . '&' . $this->parseParameters($this->_data[$back]['get'], $exclude), $this->_data[$back]['mode']);
      }
    }

    public function setSnapshot($page = null) {
      if ( isset($page) && is_array($page) ) {
        $this->_snapshot = array('page' => isset($page['page']) ? $page['page'] : null,
                                 'application' => isset($page['application']) ? $page['application'] : null,
                                 'action' => isset($page['action']) ? $page['action'] : null,
                                 'mode' => isset($page['mode']) ? $page['mode'] : 'NONSSL',
                                 'get' => isset($page['get']) ? $page['get'] : array(),
                                 'post' => isset($page['post']) ? $page['post'] : array());
      } else {
        $this->_snapshot = $this->_data[count($this->_data) - 1];
      }

      if ( !isset($_SESSION['NavigationHistory']['snapshot']) ) {
        $_SESSION['NavigationHistory']['snapshot'] = $this->_snapshot;
      }
    }

    public function hasSnapshot() {
      return !empty($this->_snapshot);
    }

    public function getSnapshot($key) {
      if ( isset($this->_snapshot[$key]) ) {
        return $this->_snapshot[$key];
      }
    }

    public function getSnapshotURL($auto_mode = false) {
      if ( $this->hasSnapshot() ) {
        if ( isset($this->_snapshot['page']) ) {
          $target = osc_href_link(null, $this->parseParameters($this->_snapshot['get']), ($auto_mode === true) ? 'NONSSL' : $this->_snapshot['mode'], true, true, $this->_snapshot['page']);
        } else {
          $target = osc_href_link($this->_snapshot['application'], $this->_snapshot['action'] . '&' . $this->parseParameters($this->_snapshot['get']), ($auto_mode === true) ? 'NONSSL' : $this->_snapshot['mode']);
        }
      } else {
        $target = osc_href_link(null, null, ($auto_mode === true) ? 'NONSSL' : $this->_snapshot['mode']);
      }

      return $target;
    }

    public function redirectToSnapshot() {
      $target = $this->getSnapshotURL(true);

      $this->resetSnapshot();

      osc_redirect($target);
    }

    public function resetPath() {
      $this->_data = array();

      if ( isset($_SESSION['NavigationHistory']['data']) ) {
        unset($_SESSION['NavigationHistory']['data']);
      }
    }

    public function resetSnapshot() {
      $this->_snapshot = array();

      if ( isset($_SESSION['NavigationHistory']['snapshot']) ) {
        unset($_SESSION['NavigationHistory']['snapshot']);
      }
    }

    public function reset() {
      $this->resetPath();
      $this->resetSnapshot();

      if ( isset($_SESSION['NavigationHistory']) ) {
        unset($_SESSION['NavigationHistory']);
      }
    }

    protected function parseParameters($array, $additional_exclude = array()) {
      $exclude = array('x', 'y', session_name());

      if ( is_array($additional_exclude) && !empty($additional_exclude) ) {
        $exclude = array_merge($exclude, $additional_exclude);
      }

      $string = '';

      if ( is_array($array) && !empty($array) ) {
        foreach ( $array as $key => $value ) {
          if ( !in_array($key, $exclude) ) {
            $string .= $key;

            if ( !empty($value) ) {
              $string .= '=' . $value;
            }

            $string .= '&';
          }
        }

        $string = substr($string, 0, -1);
      }

      return $string;
    }

    protected function applicationActionExists($action) {
      global $OSCOM_APP;

      $app = isset($OSCOM_APP) ? $OSCOM_APP->getCode() : 'index';

      return file_exists(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'apps/' . $app . '/actions/' . $action . '.php');
    }
  }
?>
