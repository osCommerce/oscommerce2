<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

use OSC\OM\Db;
use OSC\OM\HTML;
use OSC\OM\Registry;

class DbStatement extends \PDOStatement
{
    protected $is_error = false;
    protected $binded_params = [];
    protected $cache_key;
    protected $cache_expire;
    protected $cache_data;
    protected $cache_read = false;
    protected $cache_empty = false;
    protected $query_call;

    public function bindValue($parameter, $value, $data_type = \PDO::PARAM_STR)
    {
        $this->binded_params[$parameter] = [
            'value' => $value,
            'data_type' => $data_type
        ];

        return parent::bindValue($parameter, $value, $data_type);
    }

    public function bindInt($parameter, $value)
    {
// force type to int (see http://bugs.php.net/bug.php?id=44639)
        return $this->bindValue($parameter, (int)$value, \PDO::PARAM_INT);
    }

    public function bindBool($parameter, $value)
    {
// force type to bool (see http://bugs.php.net/bug.php?id=44639)
        return $this->bindValue($parameter, (bool)$value, \PDO::PARAM_BOOL);
    }

    public function bindNull($parameter)
    {
        return $this->bindValue($parameter, null, \PDO::PARAM_NULL);
    }

    public function execute($input_parameters = null)
    {
        if (isset($this->cache_key)) {
            if (Registry::get('Cache')->read($this->cache_key, $this->cache_expire)) {
                $this->cache_data = Registry::get('Cache')->getCache();

                $this->cache_read = true;
            }
        }

        if ($this->cache_read === false) {
            if (empty($input_parameters)) {
                $input_parameters = null;
            }

            $this->is_error = !parent::execute($input_parameters);

            if ($this->is_error === true) {
                trigger_error($this->queryString);
            }
        }
    }

    public function fetch(
        $fetch_style = \PDO::FETCH_ASSOC,
        $cursor_orientation = \PDO::FETCH_ORI_NEXT,
        $cursor_offset = 0
    ) {
        if ($this->cache_read === true) {
            list(, $this->result) = each($this->cache_data);
        } else {
            $this->result = parent::fetch($fetch_style, $cursor_orientation, $cursor_offset);

            if (isset($this->cache_key) && ($this->result !== false)) {
                if (!isset($this->cache_data)) {
                    $this->cache_data = [];
                }

                $this->cache_data[] = $this->result;
            }
        }

        return $this->result;
    }

    public function fetchAll($fetch_style = \PDO::FETCH_ASSOC, $fetch_argument = null, $ctor_args = [])
    {
        if ($this->cache_read === true) {
            $this->result = $this->cache_data;
        } else {
// fetchAll() fails if second argument is passed in a fetch style that does not
// use the optional argument
            if (in_array($fetch_style, array(\PDO::FETCH_COLUMN, \PDO::FETCH_CLASS, \PDO::FETCH_FUNC))) {
                $this->result = parent::fetchAll($fetch_style, $fetch_argument, $ctor_args);
            } else {
                $this->result = parent::fetchAll($fetch_style);
            }

            if (isset($this->cache_key) && ($this->result !== false)) {
                $this->cache_data = $this->result;
            }
        }

        return $this->result;
    }

    public function toArray()
    {
        if (!isset($this->result)) {
            $this->fetch();
        }

        return $this->result;
    }

    public function setCache($key, $expire = 0, $cache_empty = false)
    {
        $this->cache_key = basename($key);
        $this->cache_expire = $expire;
        $this->cache_empty = $cache_empty;

        if ($this->query_call != 'prepare') {
            trigger_error('OSCOM_DbStatement::setCache(): Cannot set cache (\'' . $this->cache_key . '\') on a non-prepare query. Please change the query to a prepare() query.');
        }
    }

    protected function valueMixed($column, $type = 'string')
    {
        if (!isset($this->result)) {
            $this->fetch();
        }

        switch ($type) {
            case 'protected':
                return HTML::outputProtected($this->result[$column]);
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

    public function value($column)
    {
        return $this->valueMixed($column, 'string');
    }

    public function valueProtected($column)
    {
        return $this->valueMixed($column, 'protected');
    }

    public function valueInt($column)
    {
        return $this->valueMixed($column, 'int');
    }

    public function valueDecimal($column)
    {
        return $this->valueMixed($column, 'decimal');
    }

    public function isError()
    {
        return $this->is_error;
    }

    public function getQuery()
    {
        return $this->queryString;
    }

    public function setQueryCall($type)
    {
        $this->query_call = $type;
    }

    public function getQueryCall()
    {
        return $this->query_call;
    }

    public function __destruct()
    {
        if (($this->cache_read === false) && isset($this->cache_key) && is_array($this->cache_data)) {
            if ($this->cache_empty || ($this->cache_data[0] !== false)) {
                Registry::get('Cache')->write($this->cache_data, $this->cache_key);
            }
        }
    }
}
