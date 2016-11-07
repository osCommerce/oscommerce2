<?php
use OSC\OM\OSCOM;
?>
<div class="col-sm-<?php echo $content_width; ?> text-center-xs copyright">
  <?=
    OSCOM::getDef('footer_text_body', [
      'year' => date('Y'),
      'store_url' => OSCOM::link('index.php'),
      'store_name' => STORE_NAME
    ]);
  ?>
</div>
