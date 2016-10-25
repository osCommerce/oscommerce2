<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

use OSC\OM\OSCOM;

$admin_menu['shop']['tools']['action_recorder'] = OSCOM::link('action_recorder.php');
$admin_menu['shop']['tools']['backup'] = OSCOM::link('backup.php');
$admin_menu['shop']['tools']['banner_manager'] = OSCOM::link('banner_manager.php');
$admin_menu['shop']['tools']['cache'] = OSCOM::link('cache.php');
$admin_menu['shop']['tools']['online_update'] = OSCOM::link('online_update.php');
$admin_menu['shop']['tools']['server_info'] = OSCOM::link('server_info.php');

  $cl_box_groups[] = array(
    'heading' => BOX_HEADING_TOOLS,
    'apps' => array(
      array(
        'code' => FILENAME_DEFINE_LANGUAGE,
        'title' => BOX_TOOLS_DEFINE_LANGUAGE,
        'link' => OSCOM::link(FILENAME_DEFINE_LANGUAGE)
      ),
      array(
        'code' => FILENAME_MAIL,
        'title' => BOX_TOOLS_MAIL,
        'link' => OSCOM::link(FILENAME_MAIL)
      ),
      array(
        'code' => FILENAME_NEWSLETTERS,
        'title' => BOX_TOOLS_NEWSLETTER_MANAGER,
        'link' => OSCOM::link(FILENAME_NEWSLETTERS)
      ),
      array(
        'code' => FILENAME_SEC_DIR_PERMISSIONS,
        'title' => BOX_TOOLS_SEC_DIR_PERMISSIONS,
        'link' => OSCOM::link(FILENAME_SEC_DIR_PERMISSIONS)
      ),
      array(
        'code' => FILENAME_WHOS_ONLINE,
        'title' => BOX_TOOLS_WHOS_ONLINE,
        'link' => OSCOM::link(FILENAME_WHOS_ONLINE)
      )
    )
  );
?>
