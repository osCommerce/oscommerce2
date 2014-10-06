<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/
?>
<footer>
  <div class="footer">
    <div class="container-fluid">
      <div class="row">
        <?php
        echo $oscTemplate->getContent('footer');
        ?>
      </div>
    </div>
  </div>
  <div class="footer-extra">
    <div class="container-fluid">
      <div class="row">
        <?php
        echo $oscTemplate->getContent('footer_extra');
        ?>
      </div>
    </div>
  </div>
</footer>
