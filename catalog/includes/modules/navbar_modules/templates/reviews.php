<?php
// in a template so that shopowners
// don't have to change the main file!

use OSC\OM\OSCOM;
?>

<?=
  OSCOM::getDef('module_navbar_reviews_public_text', [
    'reviews_url' => OSCOM::link('reviews.php')
  ]);
?>
