<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/

  function osc_db_connect($server, $username, $password, $link = 'db_link') {
    global $$link, $db_error;

    $db_error = false;

    if (!$server) {
      $db_error = 'No Server selected.';
      return false;
    }

    $$link = @mysql_connect($server, $username, $password) or $db_error = mysql_error();

    return $$link;
  }

  function osc_db_select_db($database) {
    return mysql_select_db($database);
  }

  function osc_db_query($query, $link = 'db_link') {
    global $$link;

    return mysql_query($query, $$link);
  }

  function osc_db_num_rows($db_query) {
    return mysql_num_rows($db_query);
  }

  function osc_db_install($database, $sql_file) {
    global $db_error;

    $db_error = false;

    if (!@osc_db_select_db($database)) {
      if (@osc_db_query('create database ' . $database)) {
        osc_db_select_db($database);
      } else {
        $db_error = mysql_error();
      }
    }

    if (!$db_error) {
      if (file_exists($sql_file)) {
        $fd = fopen($sql_file, 'rb');
        $restore_query = fread($fd, filesize($sql_file));
        fclose($fd);
      } else {
        $db_error = 'SQL file does not exist: ' . $sql_file;
        return false;
      }

      $sql_array = array();
      $sql_length = strlen($restore_query);
      $pos = strpos($restore_query, ';');
      for ($i=$pos; $i<$sql_length; $i++) {
        if ($restore_query[0] == '#') {
          $restore_query = ltrim(substr($restore_query, strpos($restore_query, "\n")));
          $sql_length = strlen($restore_query);
          $i = strpos($restore_query, ';')-1;
          continue;
        }
        if ($restore_query[($i+1)] == "\n") {
          for ($j=($i+2); $j<$sql_length; $j++) {
            if (trim($restore_query[$j]) != '') {
              $next = substr($restore_query, $j, 6);
              if ($next[0] == '#') {
// find out where the break position is so we can remove this line (#comment line)
                for ($k=$j; $k<$sql_length; $k++) {
                  if ($restore_query[$k] == "\n") break;
                }
                $query = substr($restore_query, 0, $i+1);
                $restore_query = substr($restore_query, $k);
// join the query before the comment appeared, with the rest of the dump
                $restore_query = $query . $restore_query;
                $sql_length = strlen($restore_query);
                $i = strpos($restore_query, ';')-1;
                continue 2;
              }
              break;
            }
          }
          if ($next == '') { // get the last insert query
            $next = 'insert';
          }
          if ( (eregi('create', $next)) || (eregi('insert', $next)) || (eregi('drop t', $next)) ) {
            $next = '';
            $sql_array[] = substr($restore_query, 0, $i);
            $restore_query = ltrim(substr($restore_query, $i+1));
            $sql_length = strlen($restore_query);
            $i = strpos($restore_query, ';')-1;
          }
        }
      }

      osc_db_query("drop table if exists action_recorder, address_book, address_format, administrators, banners, banners_history, categories, categories_description, configuration, configuration_group, counter, counter_history, countries, currencies, customers, customers_basket, customers_basket_attributes, customers_info, geo_zones, languages, manufacturers, manufacturers_info, newsletters, orders, orders_products, orders_products_attributes, orders_products_download, orders_status, orders_status_history, orders_total, products, products_attributes, products_attributes_download, products_description, products_images, products_notifications, products_options, products_options_values, products_options_values_to_products_options, products_to_categories, reviews, reviews_description, sec_directory_whitelist, sessions, specials, tax_class, tax_rates, whos_online, zones, zones_to_geo_zones");

      for ($i=0; $i<sizeof($sql_array); $i++) {
        osc_db_query($sql_array[$i]);
      }
    } else {
      return false;
    }
  }
?>
