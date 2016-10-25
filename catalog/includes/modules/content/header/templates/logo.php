<?php
use OSC\OM\HTML;
use OSC\OM\OSCOM;
?>
<div id="storeLogo" class="col-sm-<?php echo $content_width; ?> storeLogo">
  <?php echo '<a href="' . OSCOM::link('index.php') . '">' . HTML::image(OSCOM::linkImage(STORE_LOGO), STORE_NAME) . '</a>'; ?>
</div>

