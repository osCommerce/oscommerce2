<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  if ( isset($HTTP_GET_VARS['products_id']) && defined('MODULE_SOCIAL_BOOKMARKS_INSTALLED') && tep_not_null(MODULE_SOCIAL_BOOKMARKS_INSTALLED) ) {
    $sbm_array = explode(';', MODULE_SOCIAL_BOOKMARKS_INSTALLED);

    $social_bookmarks = array();

    foreach ( $sbm_array as $sbm ) {
      $class = substr($sbm, 0, strrpos($sbm, '.'));

      if ( !class_exists($class) ) {
        include(DIR_WS_LANGUAGES . $language . '/modules/social_bookmarks/' . $sbm);
        include(DIR_WS_MODULES . 'social_bookmarks/' . $class . '.php');
      }

      $sb = new $class();

      if ( $sb->isEnabled() ) {
        $social_bookmarks[] = $sb->getOutput();
      }
    }

    if ( !empty($social_bookmarks) ) {
?>
<!-- product_social_bookmarks //-->
          <tr>
            <td>
<?php
      $info_box_contents = array();
      $info_box_contents[] = array('text' => BOX_HEADING_SOCIAL_BOOKMARKS);

      new infoBoxHeading($info_box_contents, false, false);

      $info_box_contents = array();
      $info_box_contents[] = array('align' => 'center',
                                   'text' => implode(' ', $social_bookmarks));

      new infoBox($info_box_contents);
?>
            </td>
          </tr>
<!-- product_social_bookmarks_eof //-->
<?php
    }
  }
?>
