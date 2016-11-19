<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

use OSC\OM\OSCOM;

$admin_menu['shop']['tools']['action_recorder'] = OSCOM::link('action_recorder.php');
$admin_menu['shop']['tools']['backup'] = OSCOM::link('backup.php');
$admin_menu['shop']['tools']['banner_manager'] = OSCOM::link('banner_manager.php');
$admin_menu['shop']['tools']['cache'] = OSCOM::link('cache.php');
$admin_menu['shop']['tools']['online_update'] = OSCOM::link('online_update.php');
$admin_menu['shop']['tools']['server_info'] = OSCOM::link('server_info.php');

  $cl_box_groups[] = array(
    'heading' => OSCOM::getDef('box_heading_tools'),
    'apps' => array(
      array(
        'code' => FILENAME_DEFINE_LANGUAGE,
        'title' => OSCOM::getDef('box_tools_define_language'),
        'link' => OSCOM::link(FILENAME_DEFINE_LANGUAGE)
      ),
      array(
        'code' => FILENAME_MAIL,
        'title' => OSCOM::getDef('box_tools_mail'),
        'link' => OSCOM::link(FILENAME_MAIL)
      ),
      array(
        'code' => FILENAME_NEWSLETTERS,
        'title' => OSCOM::getDef('box_tools_newsletter_manager'),
        'link' => OSCOM::link(FILENAME_NEWSLETTERS)
      ),
      array(
        'code' => FILENAME_SEC_DIR_PERMISSIONS,
        'title' => OSCOM::getDef('box_tools_sec_dir_permissions'),
        'link' => OSCOM::link(FILENAME_SEC_DIR_PERMISSIONS)
      ),
      array(
        'code' => FILENAME_WHOS_ONLINE,
        'title' => OSCOM::getDef('box_tools_whos_online'),
        'link' => OSCOM::link(FILENAME_WHOS_ONLINE)
      )
    )
  );
?>
