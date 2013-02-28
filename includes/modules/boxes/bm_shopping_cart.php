<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class bm_shopping_cart {
    var $code = 'bm_shopping_cart';
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function bm_shopping_cart() {
      $this->title = MODULE_BOXES_SHOPPING_CART_TITLE;
      $this->description = MODULE_BOXES_SHOPPING_CART_DESCRIPTION;

      if ( defined('MODULE_BOXES_SHOPPING_CART_STATUS') ) {
        $this->sort_order = MODULE_BOXES_SHOPPING_CART_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_SHOPPING_CART_STATUS == 'True');

        $this->group = ((MODULE_BOXES_SHOPPING_CART_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    function execute() {
      global $currencies, $oscTemplate;

      $cart_contents_string = '';

      if ($_SESSION['cart']->count_contents() > 0) {
        $cart_contents_string = '';
        $products = $_SESSION['cart']->get_products();
        for ($i=0, $n=sizeof($products); $i<$n; $i++) {

          $cart_contents_string .= '<li';

          if ((isset($_SESSION['new_products_id_in_cart'])) && ($_SESSION['new_products_id_in_cart'] == $products[$i]['id'])) {
            $cart_contents_string .= ' class="active"';

            unset($_SESSION['new_products_id_in_cart']);
          }

          $cart_contents_string .= '><a href="' . osc_href_link('products', 'id=' . $products[$i]['id']) . '">' . $products[$i]['quantity'] . '&nbsp;x&nbsp;' . $products[$i]['name'] . '</a></li>';
        }

        $cart_contents_string .= '<li class="divider"></li>' .
                                 '<li style="text-align: right;">' . $currencies->format($_SESSION['cart']->show_total()) . '</li>';
      } else {
        $cart_contents_string .= '<li>' . MODULE_BOXES_SHOPPING_CART_BOX_CART_EMPTY . '</li>';
      }

      $data = '<li class="nav-header"><a href="' . osc_href_link('cart') . '">' . MODULE_BOXES_SHOPPING_CART_BOX_TITLE . '</a></li>' .
              $cart_contents_string;

      $oscTemplate->addBlock($data, $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_BOXES_SHOPPING_CART_STATUS');
    }

    function install() {
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Shopping Cart Module', 'MODULE_BOXES_SHOPPING_CART_STATUS', 'True', 'Do you want to add the module to your shop?', '6', '1', 'osc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Placement', 'MODULE_BOXES_SHOPPING_CART_CONTENT_PLACEMENT', 'Right Column', 'Should the module be loaded in the left or right column?', '6', '1', 'osc_cfg_select_option(array(\'Left Column\', \'Right Column\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_BOXES_SHOPPING_CART_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      osc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_BOXES_SHOPPING_CART_STATUS', 'MODULE_BOXES_SHOPPING_CART_CONTENT_PLACEMENT', 'MODULE_BOXES_SHOPPING_CART_SORT_ORDER');
    }
  }
?>
