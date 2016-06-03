<nav class="navbar<?php echo $navbar_style . $navbar_corners . $navbar_margin . $navbar_fixed; ?> navbar-custom" role="navigation">
  <div class="<?php echo BOOTSTRAP_CONTAINER; ?>">
    <?php
    if ($oscTemplate->hasBlocks('navbar_modules_home')) {
      echo '<div class="navbar-header">' . PHP_EOL;
      echo $oscTemplate->getBlocks('navbar_modules_home');
      echo '</div>' . PHP_EOL;
    }
    ?>      
    <div class="collapse navbar-collapse" id="bs-navbar-collapse-core-nav">
      <?php
      if ($oscTemplate->hasBlocks('navbar_modules_left')) {
        echo '<ul class="nav navbar-nav">' . PHP_EOL;
        echo $oscTemplate->getBlocks('navbar_modules_left');
        echo '</ul>' . PHP_EOL;
      }
      if ($oscTemplate->hasBlocks('navbar_modules_right')) {
        echo '<ul class="nav navbar-nav navbar-right">' . PHP_EOL;
        echo $oscTemplate->getBlocks('navbar_modules_right');
        echo '</ul>' . PHP_EOL;
      }
      ?>
    </div>
  </div>
</nav>
<?php echo $navbar_css; ?>
