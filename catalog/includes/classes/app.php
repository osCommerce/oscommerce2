<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app {
    protected $_content_file = 'main.php';

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
  }
?>
