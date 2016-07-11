<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  $cl_box_groups[] = array(
    'heading' => BOX_HEADING_TOOLS,
    'apps' => array(
      array(
        'code' => FILENAME_ACTION_RECORDER,
        'title' => BOX_TOOLS_ACTION_RECORDER,
        'link' => OSCOM::link(FILENAME_ACTION_RECORDER)
      ),
      array(
        'code' => FILENAME_BACKUP,
        'title' => BOX_TOOLS_BACKUP,
        'link' => OSCOM::link(FILENAME_BACKUP)
      ),
      array(
        'code' => FILENAME_BANNER_MANAGER,
        'title' => BOX_TOOLS_BANNER_MANAGER,
        'link' => OSCOM::link(FILENAME_BANNER_MANAGER)
      ),
      array(
        'code' => FILENAME_CACHE,
        'title' => BOX_TOOLS_CACHE,
        'link' => OSCOM::link(FILENAME_CACHE)
      ),
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
        'code' => FILENAME_SERVER_INFO,
        'title' => BOX_TOOLS_SERVER_INFO,
        'link' => OSCOM::link(FILENAME_SERVER_INFO)
      ),
      array(
        'code' => FILENAME_VERSION_CHECK,
        'title' => BOX_TOOLS_VERSION_CHECK,
        'link' => OSCOM::link(FILENAME_VERSION_CHECK)
      ),
      array(
        'code' => FILENAME_WHOS_ONLINE,
        'title' => BOX_TOOLS_WHOS_ONLINE,
        'link' => OSCOM::link(FILENAME_WHOS_ONLINE)
      )
    )
  );
?>
