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

        if (empty($page_file) || !file_exists($page_file)) {
          $page_file = DIR_FS_CATALOG . 'includes/error_documents/404.php';
        }

        include($page_file);
    }

    goto main_sub3;
  }

  $languages = tep_get_languages();
  $languages_array = array();
  $languages_selected = DEFAULT_LANGUAGE;
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    $languages_array[] = array('id' => $languages[$i]['code'],
                               'text' => $languages[$i]['name']);
    if ($languages[$i]['directory'] == $_SESSION['language']) {
      $languages_selected = $languages[$i]['code'];
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<?php
  if (count($languages_array) > 1) {
?>

<div class="pull-right">
  <?php echo HTML::form('adminlanguage', OSCOM::link(FILENAME_DEFAULT), 'get', null, ['session_id' => true]) . HTML::selectField('language', $languages_array, $languages_selected, 'onchange="this.form.submit();"') . '</form>'; ?>
</div>

<?php
  }
?>

<h2><i class="fa fa-home"></i> <a href="<?php echo OSCOM::link(FILENAME_DEFAULT); ?>"><?php echo STORE_NAME; ?></a></h2>

<?php
  if ( defined('MODULE_ADMIN_DASHBOARD_INSTALLED') && tep_not_null(MODULE_ADMIN_DASHBOARD_INSTALLED) ) {
    $adm_array = explode(';', MODULE_ADMIN_DASHBOARD_INSTALLED);

    $col = 0;

    foreach ($adm_array as $adm) {
      if (strpos($adm, '\\') !== false) {
        $class = Apps::getModuleClass($adm, 'AdminDashboard');
      } else {
        $class = substr($adm, 0, strrpos($adm, '.'));

        if ( !class_exists($class) ) {
          include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/dashboard/' . $adm);
          include(DIR_WS_MODULES . 'dashboard/' . $class . '.php');
        }
      }

      $ad = new $class();

      if ( $ad->isEnabled() ) {
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

  require(DIR_WS_INCLUDES . 'template_bottom.php');

  main_sub3: // Sites and Apps skip to here

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
