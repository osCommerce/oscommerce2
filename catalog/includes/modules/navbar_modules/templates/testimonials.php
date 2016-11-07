<?php
// in a template so that shopowners
// don't have to change the main file!

use OSC\OM\OSCOM;
?>

<?=
  OSCOM::getDef('module_navbar_testimonials_public_text', [
    'testimonials_url' => OSCOM::link('testimonials.php')
  ]);
?>
