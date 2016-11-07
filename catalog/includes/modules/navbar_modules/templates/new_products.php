<?php
// in a template so that shopowners
// don't have to change the main file!

use OSC\OM\OSCOM;
?>

<?=
  OSCOM::getDef('module_navbar_new_products_public_text', [
    'new_products_url' => OSCOM::link('products_new.php')
  ]);
?>
