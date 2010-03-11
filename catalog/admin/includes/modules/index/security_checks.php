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
  $secmodules_array = array();
  if ($secdir = @dir(DIR_FS_ADMIN . 'includes/modules/security_check/')) {
    while ($file = $secdir->read()) {
      if (!is_dir(DIR_FS_ADMIN . 'includes/modules/security_check/' . $file)) {
        if (substr($file, strrpos($file, '.')) == $file_extension) {
          $secmodules_array[] = $file;
        }
      }
    }
    sort($secmodules_array);
    $secdir->close();
  }

  foreach ($secmodules_array as $secmodule) {
    include(DIR_FS_ADMIN . 'includes/modules/security_check/' . $secmodule);

    $secclass = 'securityCheck_' . substr($secmodule, 0, strrpos($secmodule, '.'));
    if (tep_class_exists($secclass)) {
      $secCheck = new $secclass;

      if ( !$secCheck->pass() ) {
        if (!in_array($secCheck->type, $secCheck_types)) {
          $secCheck->type = 'info';
        }

        $secCheck_messages[$secCheck->type][] = $secCheck->getMessage();
      }
    }
  }

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
