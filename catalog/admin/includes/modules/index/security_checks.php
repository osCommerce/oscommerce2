<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $secCheck_types = array('info', 'warning', 'error');
  $secCheck_messages = array();

  $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
  $directory_array = array();
  if ($dir = @dir(DIR_FS_ADMIN . 'includes/modules/security_check/')) {
    while ($file = $dir->read()) {
      if (!is_dir(DIR_FS_ADMIN . 'includes/modules/security_check/' . $file)) {
        if (substr($file, strrpos($file, '.')) == $file_extension) {
          $directory_array[] = $file;
        }
      }
    }
    sort($directory_array);
    $dir->close();
  }

  for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
    $file = $directory_array[$i];

    include(DIR_FS_ADMIN . 'includes/modules/security_check/' . $file);

    $class = 'securityCheck_' . substr($file, 0, strrpos($file, '.'));
    if (tep_class_exists($class)) {
      $secCheck = new $class;

      if ( !$secCheck->pass() ) {
        if (!in_array($secCheck->type, $secCheck_types)) {
          $secCheck->type = 'info';
        }

        $secCheck_messages[$secCheck->type][] = $secCheck->getMessage();
      }
    }
  }
?>

<style>
.secInfo, .secSuccess, .secWarning, .secError {
  border: 1px solid;
  margin: 10px 0px;
  padding: 5px 10px 5px 50px;
  background-repeat: no-repeat;
  background-position: 10px center;
  border-radius: 10px;
  -moz-border-radius: 10px;
  -webkit-border-radius: 10px;
}

.secInfo {
  border-color: #00529B;
  background-image: url('images/ms_info.png');
  background: url('images/ms_info.png') no-repeat 10px center, url('images/ms_info_bg.png') repeat-x; /* css3 multiple backgrounds */
  background-color: #BDE5F8;
}

.secSuccess {
  border-color: #4F8A10;
  background-image: url('images/ms_success.png');
  background: url('images/ms_success.png') no-repeat 10px center, url('images/ms_success_bg.png') repeat-x; /* css3 multiple backgrounds */
  background-color: #DFF2BF;
}

.secWarning {
  border-color: #9F6000;
  background-color: #FEEFB3;
  background-image: url('images/ms_warning.png');
  background: url('images/ms_warning.png') no-repeat 10px center, url('images/ms_warning_bg.png') repeat-x; /* css3 multiple backgrounds */
  background-color: #FEEFB3;
}

.secError {
  border-color: #D8000C;
  background-image: url('images/ms_error.png');
  background: url('images/ms_error.png') no-repeat 10px center, url('images/ms_error_bg.png') repeat-x; /* css3 multiple backgrounds */
  background-color: #FFBABA;
}

.secInfo p, .secSuccess p, .secWarning p, .secError p {
  padding: 2px;
}
</style>

<?php
  if (isset($secCheck_messages['error'])) {
    echo '<div class="secError">';

    foreach ($secCheck_messages['error'] as $error) {
      echo '<p class="smallText">' . $error . '</p>';
    }

    echo '</div>';
  }

  if (isset($secCheck_messages['warning'])) {
    echo '<div class="secWarning">';

    foreach ($secCheck_messages['warning'] as $warning) {
      echo '<p class="smallText">' . $warning . '</p>';
    }

    echo '</div>';
  }

  if (isset($secCheck_messages['info'])) {
    echo '<div class="secInfo">';

    foreach ($secCheck_messages['info'] as $info) {
      echo '<p class="smallText">' . $info . '</p>';
    }

    echo '</div>';
  }

  if (empty($secCheck_messages)) {
    echo '<div class="secSuccess"><p class="smallText">' . ADMIN_INDEX_SECURITY_CHECKS_SUCCESS . '</p></div>';
  }
?>
