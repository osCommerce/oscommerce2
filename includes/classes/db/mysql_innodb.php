<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  include('mysql_standard.php');

  class mysql_innodb extends mysql_standard {
    protected $_has_native_fk = true;
    protected $_driver_parent = 'mysql_standard';

    public function connect() {
// STRICT_ALL_TABLES introduced in MySQL v5.0.2
// Only one init command can be issued (see http://bugs.php.net/bug.php?id=48859)
      $this->_driver_options[self::MYSQL_ATTR_INIT_COMMAND] = 'set session sql_mode="STRICT_ALL_TABLES", names utf8';

      parent::connect();
    }
  }
?>
