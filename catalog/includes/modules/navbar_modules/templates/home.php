<?php
// in a template so that shopowners
// don't have to change the main file!

use OSC\OM\OSCOM;
?>

<?=
  OSCOM::getDef('module_navbar_home_public_text', [
    'store_url' => OSCOM::link('index.php')
  ]);
?>
