<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

  // Version: 1.0
  // Usage: edit mysql_connect parameters first!
  // Note: default sets works on basic local installations only


  $db_server          = 'localhost';
  $db_server_username = 'root';
  $db_server_password = '';

  $link = mysql_connect($db_server, $db_server_username, $db_server_password) or die('Can\'t Connect to SQL server! Give correct parameter in the file.');

  $default_charset = 'utf8';
  $charset = mysql_client_encoding($link);
  $sqlversion = mysql_get_server_info();

  echo '<font size="5">' . $sqlversion . ' SQL charset analysis tool</font>' . '<br /><br />';
  if ($charset !== $default_charset) {
    echo 'The default character set is <font color="red"><b>' . $charset . '</b></font> and not <b>' . $default_charset . '</b> in SQL server!';
    echo '<br />';
    echo '<font color="red"> If you use default charset (' . $charset . ') may be conflict with several cases.</font><br />';
  }

  $query = "SHOW CHARACTER SET LIKE '" . $charset . "%'";
  $result = mysql_query($query);

  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    if ($row["Charset"] == $charset) {
      echo "<b>";
      printf("<font color='red'>Charset: %s<br />Description: %s<br />Default collation: %s<br /></font>", $row["Charset"], $row["Description"], $row["Default collation"]);
      echo "</b>";
    } else {
      printf("<font color='green'>Charset: %s<br />Description: %s<br />Default collation: %s<br /></font>", $row["Charset"], $row["Description"], $row["Default collation"]);
    }
  }

  echo "<br />";
  echo "Recommended to use <strong>" . $default_charset . "</strong> charset for osCommerce!";
  echo "<br />";
  echo "If you choose " . $default_charset . " charset for osCommerce database the collations are listed in the table below. Bolded are as default SQL commands";
  echo "<br />";
  echo "<br />";

  echo '<table border="1">';
  echo '<tr>' . '<td align="center">' . 'ID' . '</td>'  . '<td align="center">' . 'Charset' . '</td>' . '<td align="center">' . 'Collation' . '</td>' . '<td align="center">' . 'SQL command' . '</td>' . '</tr>';

    $query_collation = "SHOW COLLATION";
    $collation_result = mysql_query($query_collation);

    while ($collation_row = mysql_fetch_array($collation_result, MYSQL_ASSOC)) {
      echo '<tr ' . (($collation_row["Default"] == "Yes") ? 'style="font-weight:bold;"' : '') . '>';
      printf("<td>%s</td>", isset($collation_row["ID"]) ? $collation_row["ID"] : $collation_row["Id"]);
      printf("<td>%s</td>", $collation_row["Charset"]);
      printf("<td>%s</td>", $collation_row["Collation"]);
      echo '<td ' . (($collation_row["Charset"] == $default_charset) ? 'style="color:green;"' : 'style="color:red;"') . '>&nbsp;&nbsp;' . '<code>' . "CREATE DATABASE `oscommerce_example_database` DEFAULT CHARACTER SET " . $collation_row["Charset"] . " COLLATE " . $collation_row["Collation"] . "</code>" . '</td>';
      echo '</tr>';
    }
  echo '</table>';
?>
