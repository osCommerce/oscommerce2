<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license GPL; https://www.oscommerce.com/gpllicense.txt
  */

  use OSC\OM\Apps;
  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  if (OSCOM::hasSitePage()) {
    if (OSCOM::isRPC() === false) {
        $page_file = OSCOM::getSitePageFile();

        if (empty($page_file) || !is_file($page_file)) {
          $page_file = OSCOM::getConfig('dir_root', 'Shop') . 'includes/error_documents/404.php';
        }

        if (OSCOM::useSiteTemplateWithPageFile()) {
          include(OSCOM::getConfig('dir_root') . 'includes/template_top.php');
        }

        include($page_file);

        if (OSCOM::useSiteTemplateWithPageFile()) {
          include(OSCOM::getConfig('dir_root') . 'includes/template_bottom.php');
        }
    }

    goto main_sub3;
  }

  require('includes/template_top.php');
?>

<h2><i class="fa fa-home"></i> <a href="<?= OSCOM::link(FILENAME_DEFAULT); ?>"><?= STORE_NAME; ?></a></h2>

<?php
  if (defined('MODULE_ADMIN_DASHBOARD_INSTALLED') && tep_not_null(MODULE_ADMIN_DASHBOARD_INSTALLED)) {
    $adm_array = explode(';', MODULE_ADMIN_DASHBOARD_INSTALLED);

    $col = 0;

    foreach ($adm_array as $adm) {
      if (strpos($adm, '\\') !== false) {
        $class = Apps::getModuleClass($adm, 'AdminDashboard');
      } else {
        $class = substr($adm, 0, strrpos($adm, '.'));

        if ( !class_exists($class) ) {
          include('includes/languages/' . $_SESSION['language'] . '/modules/dashboard/' . $adm);
          include('includes/modules/dashboard/' . $class . '.php');
        }
      }

      $ad = new $class();

      if ($ad->isEnabled()) {
        $col += 1;

        if ($col === 1) {
          echo '<div class="row">';
        }

        echo '<div class="col-md-6">' . $ad->getOutput() . '</div>';

        if ($col === 2) {
          $col = 0;

          echo '</div>';
        }
      }
    }

    if ($col === 1) {
      echo '</div>';
    }
  }

  require('includes/template_bottom.php');

  main_sub3: // Sites and Apps skip to here

  require('includes/application_bottom.php');
?>
