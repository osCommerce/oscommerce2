<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app {
    protected $_content_file = 'main.php';
    protected $_current_action;
    protected $_ignored_actions = array();

    public static function initialize() {
      $app = 'index';

      if ( !empty($_GET) ) {
        $requested_app = tep_sanitize_string(basename(key(array_slice($_GET, 0, 1, true))));

        if ( preg_match('/^[A-Za-z0-9-_]*$/', $requested_app) && file_exists(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'apps/' . $requested_app . '/controller.php') ) {
          $app = $requested_app;
        }
      }

      include(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'apps/' . $app . '/controller.php');

      if ( file_exists(DIR_FS_CATALOG . DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . $app . '.php') ) {
        include(DIR_FS_CATALOG . DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . $app . '.php');
      }
      
      $app_class = 'app_' . $app;

      return new $app_class();
    }

    public function getCode() {
      return substr(get_class($this), 4);
    }

    public function getContentFile($with_full_path = false) {
      if ( $with_full_path === true ) {
        return DIR_FS_CATALOG . DIR_WS_INCLUDES . 'apps/' . $this->getCode() . '/content/' . $this->_content_file;
      }

      return $this->_content_file;
    }

    public function setContentFile($filename) {
      $this->_content_file = $filename;
    }

    public function runActions() {
      if ( !in_array(session_name(), $this->_ignored_actions) ) {
        $this->_ignored_actions[] = session_name();
      }

      $action = null;
      $action_index = 1;

      if ( count($_GET) > 1 ) {
        $requested_action = tep_sanitize_string(basename(key(array_slice($_GET, 1, 1, true))));

        if ( preg_match('/^[A-Za-z0-9-_]*$/', $requested_action) && !in_array($requested_action, $this->_ignored_actions) && file_exists(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'apps/' . $this->getCode() . '/actions/' . $requested_action . '.php') ) {
          $this->_current_action = $action = $requested_action;
        }
      }

      if ( isset($action) ) {
        include(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'apps/' . $this->getCode() . '/actions/' . $action . '.php');

        call_user_func(array('app_' . $this->getCode() . '_action_' . $action, 'execute'), $this);

        $action_index++;

        if ( $action_index < count($_GET) ) {
          $action = array($action);

          for ( $i = $action_index, $n = count($_GET); $i < $n; $i++ ) {
            $subaction = tep_sanitize_string(basename(key(array_slice($_GET, $i, 1, true))));

            if ( preg_match('/^[A-Za-z0-9-_]*$/', $subaction) && !in_array($subaction, $this->_ignored_actions) && file_exists(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'apps/' . $this->getCode() . '/actions/' . implode('/', $action) . '/' . $subaction . '.php') ) {
              include(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'apps/' . $this->getCode() . '/actions/' . implode('/', $action) . '/' . $subaction . '.php');

              call_user_func(array('app_' . $this->getCode() . '_action_' . implode('_', $action) . '_' . $subaction, 'execute'), $this);

              $action[] = $subaction;

              $this->_current_action = $subaction;
            } else {
              break;
            }
          }
        }
      }
    }

    public function getCurrentAction() {
      return $this->_current_action;
    }

    public function ignoreAction($key) {
      $this->_ignored_actions[] = $key;
    }
  }
?>
