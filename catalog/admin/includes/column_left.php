<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  if (tep_session_is_registered('admin')) {
    $cl_box_groups = array();

    if ($dir = @dir(DIR_FS_ADMIN . 'includes/boxes')) {
      $files = array();

      while ($file = $dir->read()) {
        if (!is_dir($dir->path . '/' . $file)) {
          if (substr($file, strrpos($file, '.')) == '.php') {
            $files[] = $file;
          }
        }
      }

      $dir->close();

      natcasesort($files);

      foreach ( $files as $file ) {
        if ( file_exists(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/boxes/' . $file) ) {
          include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/boxes/' . $file);
        }

        include($dir->path . '/' . $file);
      }
    }

    function tep_sort_admin_boxes($a, $b) {
      return strcasecmp($a['heading'], $b['heading']);
    }

    usort($cl_box_groups, 'tep_sort_admin_boxes');

    function tep_sort_admin_boxes_links($a, $b) {
      return strcasecmp($a['title'], $b['title']);
    }

    foreach ( $cl_box_groups as &$group ) {
      usort($group['apps'], 'tep_sort_admin_boxes_links');
    }
?>

<div id="adminAppMenu">

<?php
    foreach ($cl_box_groups as $groups) {
      echo '<h3><a href="#">' . $groups['heading'] . '</a></h3>' .
           '<div><ul>';

      foreach ($groups['apps'] as $app) {
        echo '<li><a href="' . $app['link'] . '">' . $app['title'] . '</a></li>';
      }

      echo '</ul></div>';
    }
?>

</div>

<script type="text/javascript">
$('#adminAppMenu').accordion({
  heightStyle: 'content',
  collapsible: true,

<?php
    $counter = 0;
    foreach ($cl_box_groups as $groups) {
      foreach ($groups['apps'] as $app) {
        if ($app['code'] == $PHP_SELF) {
          break 2;
        }
      }

      $counter++;
    }

    echo 'active: ' . (isset($app) && ($app['code'] == $PHP_SELF) ? $counter : 'false');
?>

});
</script>

<?php
  }
?>
