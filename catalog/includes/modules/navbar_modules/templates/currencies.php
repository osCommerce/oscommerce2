<?php
use OSC\OM\OSCOM;
?>
<li class="dropdown">
  <a class="dropdown-toggle" data-toggle="dropdown" href="#">
  <?php echo sprintf(MODULE_NAVBAR_CURRENCIES_SELECTED_CURRENCY, $_SESSION['currency']); ?>
  </a>
  <?php
  if (isset($currencies) && is_object($currencies) && (count($currencies->currencies) > 1)) {
    ?>
    <ul class="dropdown-menu">
      <?php
      $currencies_array = array();
      foreach ($currencies->currencies as $key => $value) {
        $currencies_array[] = array('id' => $key, 'text' => $value['title']);
        echo '<li><a href="' . OSCOM::link($PHP_SELF, tep_get_all_get_params(array('language', 'currency')) . 'currency=' . $key, 'AUTO') . '">' . $value['title'] . '</a></li>';
      }
      ?>
    </ul>
    <?php
  }
  ?>
</li>