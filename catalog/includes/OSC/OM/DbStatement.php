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
use OSC\OM\OSCOM;
use OSC\OM\Registry;

class DbStatement extends \PDOStatement
{
    protected $pdo;
    protected $is_error = false;
    protected $page_set_keyword = 'page';
    protected $page_set;
    protected $page_set_results_per_page;
    protected $cache_key;
    protected $cache_expire;
    protected $cache_data;
    protected $cache_read = false;
    protected $cache_empty_results = false;
    protected $query_call;

    public function bindValue($parameter, $value, $data_type = \PDO::PARAM_STR)
    {
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

    public function bindDecimal($parameter, $value) {
        return $this->bindValue($parameter, (float)$value); // there is no \PDO::PARAM_FLOAT
    }

    public function bindNull($parameter)
    {
        return $this->bindValue($parameter, null, \PDO::PARAM_NULL);
    }

    public function setPageSet($max_results, $page_set_keyword = null, $placeholder_offset = 'page_set_offset', $placeholder_max_results = 'page_set_max_results')
    {
        if (!empty($page_set_keyword)) {
            $this->page_set_keyword = $page_set_keyword;
        }

        $this->page_set = (isset($_GET[$this->page_set_keyword]) && is_numeric($_GET[$this->page_set_keyword]) && ($_GET[$this->page_set_keyword] > 0)) ? $_GET[$this->page_set_keyword] : 1;
        $this->page_set_results_per_page = $max_results;

        $offset = max(($this->page_set * $max_results) - $max_results, 0);

        $this->bindInt(':' . $placeholder_offset, $offset);
        $this->bindInt(':' . $placeholder_max_results, $max_results);
    }

    public function execute($input_parameters = null)
    {
        if (isset($this->cache_key)) {
            if (isset($this->page_set)) {
                $this->cache_key = $this->cache_key . '-pageset' . $this->page_set;
            }

            if (Registry::get('Cache')->read($this->cache_key, $this->cache_expire)) {
                $this->cache_data = Registry::get('Cache')->getCache();

                if (isset($this->cache_data['data']) && isset($this->cache_data['total'])) {
                    $this->page_set_total_rows = $this->cache_data['total'];
                    $this->cache_data = $this->cache_data['data'];
                }

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

            if (strpos($this->queryString, ' SQL_CALC_FOUND_ROWS ') !== false) {
                $this->page_set_total_rows = $this->pdo->query('select found_rows()')->fetchColumn();
            } elseif (isset($this->page_set)) {
                trigger_error('OSC\OM\DbStatement::execute(): Page Set query does not contain SQL_CALC_FOUND_ROWS. Please add it to the query: ' . $this->queryString);
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

    public function check()
    {
        if (!isset($this->result)) {
            $this->fetch();
        }

        return $this->result !== false;
    }

    public function toArray()
    {
        if (!isset($this->result)) {
            $this->fetch();
        }

        return $this->result;
    }

    public function setCache($key, $expire = null, $cache_empty_results = false)
    {
        if (!is_numeric($expire)) {
            $expire = 0;
        }

        if (!is_bool($cache_empty_results)) {
            $cache_empty_results = false;
        }

        $this->cache_key = basename($key);
        $this->cache_expire = $expire;
        $this->cache_empty_results = $cache_empty_results;

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

    public function hasValue($column) {
        if (!isset($this->result)) {
            $this->fetch();
        }

        return isset($this->result[$column]);
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

    public function getCurrentPageSet() {
        return $this->page_set;
    }

    public function getPageSetResultsPerPage()
    {
        return $this->page_set_results_per_page;
    }

    public function getPageSetTotalRows()
    {
        return $this->page_set_total_rows;
    }

    public function setPDO(\PDO $instance)
    {
        $this->pdo = $instance;
    }

    public function getPageSetLabel($text)
    {
        if ($this->page_set_total_rows < 1) {
            $from = 0;
        } else {
            $from = max(($this->page_set * $this->page_set_results_per_page) - $this->page_set_results_per_page, 1);
        }

        $to = min($this->page_set * $this->page_set_results_per_page, $this->page_set_total_rows);

        if ($to > $this->page_set_results_per_page) {
            $from++;
        }

        return sprintf($text, $from, $to, $this->page_set_total_rows);
    }

    public function getPageSetLinks($parameters = null)
    {
        global $PHP_SELF;

        $number_of_pages = ceil($this->page_set_total_rows / $this->page_set_results_per_page);

        if (empty($parameters)) {
            $parameters = '';
        }

        if (!empty($parameters)) {
            parse_str($parameters, $p);

            if (isset($p[$this->page_set_keyword])) {
                unset($p[$this->page_set_keyword]);
            }

            $parameters = http_build_query($p) . '&';
        }

        $output = '<ul style="margin-top: 0;" class="pagination">';

// previous button - not displayed on first page
        if ($this->page_set > 1) {
            $output .= '<li><a href="' . OSCOM::link($PHP_SELF, $parameters . $this->page_set_keyword . '=' . ($this->page_set - 1)) . '" title=" ' . PREVNEXT_TITLE_PREVIOUS_PAGE . ' ">&laquo;</a></li>';
        } else {
            $output .= '<li class="disabled"><span>&laquo;</span></li>';
        }

// check if number_of_pages > $max_page_links
        $cur_window_num = 0;
        $max_window_num = 0;

        if ($this->page_set_total_rows > 0) {
            $cur_window_num = (int)($this->page_set / $this->page_set_total_rows);

            if ($this->page_set % $this->page_set_total_rows) {
                $cur_window_num++;
            }

            $max_window_num = (int)($number_of_pages / $this->page_set_total_rows);

            if ($number_of_pages % $this->page_set_total_rows) {
                $max_window_num++;
            }
        }

// previous window of pages
        if ($cur_window_num > 1) {
            $output .= '<li><a href="' . OSCOM::link($PHP_SELF, $parameters . $this->page_set_keyword . '=' . (($cur_window_num - 1) * $this->page_set_total_rows)) . '" title=" ' . sprintf(PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE, $this->page_set_total_rows) . ' ">...</a></li>';
        }

// page nn button
        for ($jump_to_page = 1 + (($cur_window_num - 1) * $this->page_set_total_rows); ($jump_to_page <= ($cur_window_num * $this->page_set_total_rows)) && ($jump_to_page <= $number_of_pages); $jump_to_page++) {
            if ($jump_to_page == $this->page_set) {
                $output .= '<li class="active"><a href="' . OSCOM::link($PHP_SELF, $parameters . $this->page_set_keyword . '=' . $jump_to_page) . '" title=" ' . sprintf(PREVNEXT_TITLE_PAGE_NO, $jump_to_page) . ' ">' . $jump_to_page . '<span class="sr-only">(current)</span></a></li>';
            } else {
                $output .= '<li><a href="' . OSCOM::link($PHP_SELF, $parameters . $this->page_set_keyword . '=' . $jump_to_page) . '" title=" ' . sprintf(PREVNEXT_TITLE_PAGE_NO, $jump_to_page) . ' ">' . $jump_to_page . '</a></li>';
            }
        }

// next window of pages
        if ($cur_window_num < $max_window_num) {
            $output .= '<a href="' . OSCOM::link($PHP_SELF, $parameters . $this->page_set_keyword . '=' . (($cur_window_num) * $this->page_set_total_rows + 1)) . '" class="pageResults" title=" ' . sprintf(PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE, $this->page_set_total_rows) . ' ">...</a>&nbsp;';
        }

// next button
        if (($this->page_set < $number_of_pages) && ($number_of_pages != 1)) {
            $output .= '<li><a href="' . OSCOM::link($PHP_SELF, $parameters . $this->page_set_keyword . '=' . ($this->page_set + 1)) . '" title=" ' . PREVNEXT_TITLE_NEXT_PAGE . ' ">&raquo;</a></li>';
        } else {
            $output .= '<li class="disabled"><span>&raquo;</span></li>';
        }

        $output .= '</ul>';

        return $output;
    }

    public function __destruct()
    {
        if (($this->cache_read === false) && isset($this->cache_key) && is_array($this->cache_data)) {
            if ($this->cache_empty_results || (isset($this->cache_data[0]) && ($this->cache_data[0] !== false))) {
                $cache_data = $this->cache_data;

                if (isset($this->page_set_total_rows)) {
                    $cache_data = [
                        'data' => $cache_data,
                        'total' => $this->page_set_total_rows
                    ];
                }

                Registry::get('Cache')->write($cache_data, $this->cache_key);
            }
        }
    }
}
