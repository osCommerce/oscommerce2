<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class mysql_standard_statement extends db_statement {
    protected $_pdo;

    protected function __construct($pdo) {
      $this->_pdo = $pdo;
    }

    public function execute($input_parameters = array()) {
      $query_action = strtolower(substr($this->queryString, 0, strpos($this->queryString, ' ')));

      $db_table_prefix = defined('DB_TABLE_PREFIX') ? DB_TABLE_PREFIX : '';

      if ($query_action == 'delete') {
        $query_data = explode(' ', $this->queryString, 4);
        $query_table = substr($query_data[2], strlen($db_table_prefix));

        if ( $this->_pdo->hasForeignKey($query_table) ) {
// check for RESTRICT constraints first
          foreach ( $this->_pdo->getForeignKeys($query_table) as $fk ) {
            if ( $fk['on_delete'] == 'restrict' ) {
              $Qchild = $this->_pdo->prepare('select ' . $fk['to_field'] . ' from ' . $query_data[2] . ' ' . $query_data[3]);

              foreach ( $this->_binded_params as $key => $value ) {
                $Qchild->bindValue($key, $value['value'], $value['data_type']);
              }

              $Qchild->execute();

              while ( $Qchild->fetch() ) {
                $Qcheck = $this->_pdo->prepare('select ' . $fk['from_field'] . ' from ' . $db_table_prefix .  $fk['from_table'] . ' where ' . $fk['from_field'] . ' = "' . $Qchild->value($fk['to_field']) . '" limit 1');
                $Qcheck->execute();

                if ( count($Qcheck->fetchAll()) === 1 ) {
                  trigger_error('RESTRICT constraint condition from table ' . $db_table_prefix .  $fk['from_table']);

                  return false;
                }
              }
            }
          }

          foreach ( $this->_pdo->getForeignKeys($query_table) as $fk ) {
            $Qparent = $this->_pdo->prepare('select * from ' . $query_data[2] . ' ' . $query_data[3]);

            foreach ( $this->_binded_params as $key => $value ) {
              $Qparent->bindValue($key, $value['value'], $value['data_type']);
            }

            $Qparent->execute();

            while ( $Qparent->fetch() ) {
              if ( $fk['on_delete'] == 'cascade' ) {
                $Qdel = $this->_pdo->prepare('delete from ' . $db_table_prefix . $fk['from_table'] . ' where ' . $fk['from_field'] . ' = :' . $fk['from_field']);
                $Qdel->bindValue(':' . $fk['from_field'], $Qparent->value($fk['to_field']));
                $Qdel->execute();
              } elseif ( $fk['on_delete'] == 'set_null' ) {
                $Qupdate = $this->_pdo->prepare('update ' . $db_table_prefix . $fk['from_table'] . ' set ' . $fk['from_field'] . ' = null where ' . $fk['from_field'] . ' = :' . $fk['from_field']);
                $Qupdate->bindValue(':' . $fk['from_field'], $Qparent->value($fk['to_field']));
                $Qupdate->execute();
              }
            }
          }
        }
      } elseif ($query_action == 'update') {
        $query_data = explode(' ', $this->queryString, 3);
        $query_table = substr($query_data[1], strlen($db_table_prefix));

        if ( $this->_pdo->hasForeignKey($query_table) ) {
// check for RESTRICT constraints first
          foreach ( $this->_pdo->getForeignKeys($query_table) as $fk ) {
            if ( $fk['on_update'] == 'restrict' ) {
              $Qchild = $this->_pdo->prepare('select ' . $fk['to_field'] . ' from ' . $query_data[2] . ' ' . $query_data[3]);

              foreach ( $this->_binded_params as $key => $value ) {
                $Qchild->bindValue($key, $value['value'], $value['data_type']);
              }

              $Qchild->execute();

              while ( $Qchild->fetch() ) {
                $Qcheck = $this->_pdo->prepare('select ' . $fk['from_field'] . ' from ' . $db_table_prefix .  $fk['from_table'] . ' where ' . $fk['from_field'] . ' = "' . $Qchild->value($fk['to_field']) . '" limit 1');
                $Qcheck->execute();

                if ( count($Qcheck->fetchAll()) === 1 ) {
                  trigger_error('RESTRICT constraint condition from table ' . $db_table_prefix .  $fk['from_table']);

                  return false;
                }
              }
            }
          }

          foreach ( $this->_pdo->getForeignKeys($query_table) as $fk ) {
// check to see if foreign key column value is being changed
            if ( strpos(substr($this->queryString, strpos($this->queryString, ' set ')+4, strpos($this->queryString, ' where ') - strpos($this->queryString, ' set ') - 4), ' ' . $fk['to_field'] . ' ') !== false ) {
              $Qparent = $this->_pdo->prepare('select * from ' . $query_data[1] . substr($this->queryString, strrpos($this->queryString, ' where ')));

              foreach ( $this->_binded_params as $key => $value ) {
                if ( preg_match('/:\b' . substr($key, 1) . '\b/', $Qparent->queryString) ) {
                  $Qparent->bindValue($key, $value['value'], $value['data_type']);
                }
              }

              $Qparent->execute();

              while ( $Qparent->fetch() ) {
                if ( ($fk['on_update'] == 'cascade') || ($fk['on_update'] == 'set_null') ) {
                  $on_update_value = '';

                  if ( $fk['on_update'] == 'cascade' ) {
                    $on_update_value = $this->_binded_params[':' . $fk['to_field']]['value'];
                  }

                  $Qupdate = $this->_pdo->prepare('update ' . $db_table_prefix . $fk['from_table'] . ' set ' . $fk['from_field'] . ' = :' . $fk['from_field'] . ' where ' . $fk['from_field'] . ' = :' . $fk['from_field'] . '_orig');

                  if ( empty($on_update_value) ) {
                    $Qupdate->bindNull(':' . $fk['from_field']);
                  } else {
                    $Qupdate->bindValue(':' . $fk['from_field'], $on_update_value);
                  }

                  $Qupdate->bindValue(':' . $fk['from_field'] . '_orig', $Qparent->value($fk['to_field']));
                  $Qupdate->execute();
                }
              }
            }
          }
        }
      }

      return parent::execute($input_parameters);
    }
  }
?>
