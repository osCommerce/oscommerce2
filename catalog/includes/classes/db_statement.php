<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class db_statement extends PDOStatement {
    protected $_is_error = false;
    protected $_binded_params = array();
    protected $_cache_key;
    protected $_cache_expire;
    protected $_cache_data;
    protected $_cache_read = false;

    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR) {
      $this->_binded_params[$parameter] = array('value' => $value,
                                                'data_type' => $data_type);

      return parent::bindValue($parameter, $value, $data_type);
    }

    public function bindInt($parameter, $value) {
// force type to int (see http://bugs.php.net/bug.php?id=44639)
      return $this->bindValue($parameter, (int)$value, PDO::PARAM_INT);
    }

    public function bindBool($parameter, $value) {
// force type to bool (see http://bugs.php.net/bug.php?id=44639)
      return $this->bindValue($parameter, (bool)$value, PDO::PARAM_BOOL);
    }

    public function bindNull($parameter) {
      return $this->bindValue($parameter, null, PDO::PARAM_NULL);
    }

    public function execute($input_parameters = array()) {
      global $OSCOM_Cache;

      if ( isset($this->_cache_key) ) {
        if ( $OSCOM_Cache->read($this->_cache_key, $this->_cache_expire) ) {
          $this->_cache_data = $OSCOM_Cache->getCache();

          $this->_cache_read = true;
        }
      }

      if ($this->_cache_read === false) {
        if ( empty($input_parameters) ) {
          $input_parameters = null;
        }

        $this->_is_error = !parent::execute($input_parameters);

        if ( $this->_is_error === true ) {
          trigger_error($this->queryString);
        }
      }
    }

    public function fetch($fetch_style = PDO::FETCH_ASSOC, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0) {
      if ( $this->_cache_read === true ) {
        list(, $this->result) = each($this->_cache_data);
      } else {
        $this->result = parent::fetch($fetch_style, $cursor_orientation, $cursor_offset);

        if ( isset($this->_cache_key) ) {
          $this->_cache_data[] = $this->result;
        }
      }

      return $this->result;
    }

    public function fetchAll($fetch_style = PDO::FETCH_ASSOC, $fetch_argument = null, $ctor_args = array()) {
      if ( $this->_cache_read === true ) {
        $this->result = $this->_cache_data;
      } else {
// fetchAll() fails if second argument is passed in a fetch style that does not
// use the optional argument
        if ( in_array($fetch_style, array(PDO::FETCH_COLUMN, PDO::FETCH_CLASS, PDO::FETCH_FUNC)) ) {
          $this->result = parent::fetchAll($fetch_style, $fetch_argument, $ctor_args);
        } else {
          $this->result = parent::fetchAll($fetch_style);
        }

        if ( isset($this->_cache_key) ) {
          $this->_cache_data = $this->result;
        }
      }

      return $this->result;
    }

    public function toArray() {
      if ( !isset($this->result) ) {
        $this->fetch();
      }

      return $this->result;
    }

    public function setCache($key, $expire = 0) {
      $this->_cache_key = $key;
      $this->_cache_expire = $expire;
    }

    protected function valueMixed($column, $type = 'string') {
      if ( !isset($this->result) ) {
        $this->fetch();
      }

      switch ($type) {
        case 'protected':
          return tep_output_string_protected($this->result[$column]);
          break;
        case 'int':
          return (int)$this->result[$column];
          break;
        case 'decimal':
          return (float)$this->result[$column];
          break;
        case 'string':
        default:
          return $this->result[$column];
      }
    }

    public function value($column) {
      return $this->valueMixed($column, 'string');
    }

    public function valueProtected($column) {
      return $this->valueMixed($column, 'protected');
    }

    public function valueInt($column) {
      return $this->valueMixed($column, 'int');
    }

    public function valueDecimal($column) {
      return $this->valueMixed($column, 'decimal');
    }

    public function isError() {
      return $this->_is_error;
    }

    public function getQuery() {
      return $this->queryString;
    }

    public function __destruct() {
      global $OSCOM_Cache;

      if ( $this->_cache_read === false ) {
        if ( isset($this->_cache_key) ) {
          $OSCOM_Cache->write($this->_cache_data, $this->_cache_key);
        }
      }
    }
  }
?>
