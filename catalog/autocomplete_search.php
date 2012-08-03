<?php
/*
  Copyright (c) 2012 Club osCommerce www.clubosc.com

  Released under the GNU General Public License
*/

require_once("includes/application_top.php");

$autocomplete_value = tep_db_input(strtolower($_GET["term"]));

$query = tep_db_query("SELECT products_name FROM products_description WHERE LOWER(products_name) LIKE '%$autocomplete_value%' AND language_id = '" . (int)$languages_id . "'");

for ($x = 0, $numrows = tep_db_num_rows($query); $x < $numrows; $x++) {
    $row = tep_db_fetch_assoc($query);

    $products[$x] = array("label" => $row["products_name"]);
}

echo json_encode($products);
?>
