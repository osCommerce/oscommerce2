<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  function app_braintree_get_admin_box_links() {
    $menu = array(
      array('code' => 'braintree.php',
            'title' => MODULES_ADMIN_MENU_BRAINTREE_CONFIGURE,
            'link' => tep_href_link('braintree.php', 'action=configure')),
      array('code' => 'braintree.php',
            'title' => MODULES_ADMIN_MENU_BRAINTREE_MANAGE_CREDENTIALS,
            'link' => tep_href_link('braintree.php', 'action=credentials'))
    );

    return $menu;
  }
?>
