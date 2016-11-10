<?php
use OSC\OM\HTML;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

$OSCOM_Language = Registry::get('Language');
?>

<li class="dropdown">
  <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="fa fa-fw fa-language"></span> <?php echo $OSCOM_Language->get('name') . ' <span class="caret"></span>'; ?></a>

  <ul class="dropdown-menu">

<?php
foreach ($OSCOM_Language->getAll() as $code => $value) {
  echo '<li><a href="' . OSCOM::link($PHP_SELF, tep_get_all_get_params(array('language', 'currency')) . 'language=' . $code) . '">' . $OSCOM_Language->getImage($value['code']) . '&nbsp;' . $value['name'] . '</a></li>';
}
?>

  </ul>
</li>
