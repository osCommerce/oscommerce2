<?php
use OSC\OM\HTML;
use OSC\OM\OSCOM;
?>
<li class="dropdown">
  <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo MODULE_NAVBAR_LANGUAGES_SELECTED_LANGUAGE; ?></a>
  <?php
  if (!isset($lng) || (isset($lng) && !is_object($lng))) {
    include('includes/classes/language.php');
    $lng = new language;
  }
  if (count($lng->catalog_languages) > 1) {
    ?>
    <ul class="dropdown-menu">
      <?php
      foreach ($lng->catalog_languages as $key => $value) {
        echo '<li><a href="' . OSCOM::link($PHP_SELF, tep_get_all_get_params(array('language', 'currency')) . 'language=' . $key, 'AUTO') . '">' . HTML::image('includes/languages/' .  $value['directory'] . '/images/' . $value['image'], $value['name'], null, null, null, false) . ' ' . $value['name'] . '</a></li>';
      }
      ?>
    </ul>
    <?php
    }
  ?>
</li>