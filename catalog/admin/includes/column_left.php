<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

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

    $directory = OSCOM::BASE_DIR . 'apps';

    if (file_exists($directory)) {
        if ($dir = new \DirectoryIterator($directory)) {
            foreach ($dir as $file) {
                if (!$file->isDot() && $file->isDir() && file_exists($directory . '/' . $file->getFilename() . '/Module/Admin/Menu')) {
                    $menu_directory = $directory . '/' . $file->getFilename() . '/Module/Admin/Menu';

                    if ($mdir = new \DirectoryIterator($menu_directory)) {
                        foreach ($mdir as $mfile) {
                            if (!$mfile->isDot() && !$mfile->isDir() && ($mfile->getExtension() == 'php')) {
                                $class = 'OSC\OM\Apps\\' . $file->getFilename() . '\Module\Admin\Menu\\' . $mfile->getBasename('.php');

                                if (is_subclass_of($class, 'OSC\OM\ModuleAdminMenuInterface')) {
                                    call_user_func([$class, 'execute']);
                                }
                            }
                        }
                    }
                }
            }
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
