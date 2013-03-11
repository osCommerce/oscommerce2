<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class messageStack {
    protected $_data = array();

    public function __construct() {
      register_shutdown_function(array($this, 'saveInSession'));

      if ( isset($_SESSION['MessageStack']) && !empty($_SESSION['MessageStack']) ) {
        $this->_data = $_SESSION['MessageStack'];

        unset($_SESSION['MessageStack']);
      }
    }

    public function saveInSession() {
      if ( !empty($this->_data) ) {
        $_SESSION['MessageStack'] = $this->_data;
      }
    }

    public function addError($group, $message) {
      $this->add($group, $message, 'error');
    }

    public function addWarning($group, $message) {
      $this->add($group, $message, 'warning');
    }

    public function addSuccess($group, $message) {
      $this->add($group, $message, 'success');
    }

    public function addInfo($group, $message) {
      $this->add($group, $message, 'info');
    }

    protected function add($group = null, $message, $type) {
      global $OSCOM_APP;

      if ( !isset($group) ) {
        $group = $OSCOM_APP->getCode();
      }

      $types = array('error', 'warning', 'success', 'info');

      if ( !in_array($type, $types) ) {
        $type = 'error';
      }

      if ( !$this->exists($group) || !in_array($message, $this->_data[$group][$type]) ) {
        $this->_data[$group][$type][] = $message;
      }
    }

    public function reset() {
      $this->_data = array();
    }

    public function exists($group = null, $type = null) {
      global $OSCOM_APP;

      if ( !isset($group) ) {
        $group = $OSCOM_APP->getCode();
      }

      if ( isset($type) ) {
        return array_key_exists($type, $this->_data[$group]);
      } else {
        return array_key_exists($group, $this->_data);
      }
    }

    public function hasContent() {
      return !empty($this->_data);
    }

    public function get($group = null, $type = null) {
      global $OSCOM_APP;

      if ( !isset($group) ) {
        $group = $OSCOM_APP->getCode();
      }

      $result = '';

      if ( $this->exists($group) ) {
        $messages = isset($type) ? array($type => $this->_data[$group][$type]) : $this->_data[$group];

        foreach ( array_keys($messages) as $key ) {
          $result .= '<div class="alert';

          if ( $key != 'warning' ) {
            $result .= ' alert-' . $key;
          }

          if ( count($messages[$key]) > 1 ) {
            $result .= ' alert-block';
          }

          $result .= '"><button type="button" class="close" data-dismiss="alert">&times;</button>';

          $result .= implode('<br /><br />', $messages[$key]);

          $result .= '</div>';
        }

        if ( isset($type) ) {
          unset($this->_data[$group][$type]);
        } else {
          unset($this->_data[$group]);
        }
      }

      return $result;
    }

    public function getAll($group = null, $type = null) {
      if ( isset($group) ) {
        if ( $this->exists($group) ) {
          if ( isset($type) ) {
            return $this->_data[$group][$type];
          } else {
            return $this->_data[$group];
          }
        } else {
          return array();
        }
      }

      return $this->_data;
    }

    public function size($group = null, $type = null) {
      global $OSCOM_APP;

      if ( !isset($group) ) {
        $group = $OSCOM_APP->getCode();
      }

      $size = 0;

      if ( $this->exists($group) ) {
        if ( isset($type) ) {
          $size = count($this->_data[$group][$type]);
        } else {
          $size = count($this->_data[$group]);
        }
      }

      return $size;
    }
  }
?>
