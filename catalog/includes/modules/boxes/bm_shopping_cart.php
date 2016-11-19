<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class bm_shopping_cart {
    var $code = 'bm_shopping_cart';
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_boxes_shopping_cart_title');
      $this->description = OSCOM::getDef('module_boxes_shopping_cart_description');

      if ( defined('MODULE_BOXES_SHOPPING_CART_STATUS') ) {
        $this->sort_order = MODULE_BOXES_SHOPPING_CART_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_SHOPPING_CART_STATUS == 'True');

        $this->group = ((MODULE_BOXES_SHOPPING_CART_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    function execute() {
      global $new_products_id_in_cart, $currencies, $oscTemplate;

      $cart_contents_string = '';

      if ($_SESSION['cart']->count_contents() > 0) {
        $cart_contents_string = NULL;
        $products = $_SESSION['cart']->get_products();
        for ($i=0, $n=sizeof($products); $i<$n; $i++) {

          $cart_contents_string .= '<li';
          if ((isset($_SESSION['new_products_id_in_cart'])) && ($new_products_id_in_cart == $products[$i]['id'])) {
            $cart_contents_string .= ' class="newItemInCart"';
          }
          $cart_contents_string .= '>';

          $cart_contents_string .= $products[$i]['quantity'] . '&nbsp;x&nbsp;';

          $cart_contents_string .= '<a href="' . OSCOM::link('product_info.php', 'products_id=' . $products[$i]['id']) . '">';

          $cart_contents_string .= $products[$i]['name'];

          $cart_contents_string .= '</a></li>';

          if ((isset($_SESSION['new_products_id_in_cart'])) && ($new_products_id_in_cart == $products[$i]['id'])) {
            unset($_SESSION['new_products_id_in_cart']);
          }
        }

        $cart_contents_string .= '<li class="text-right"><hr>' . $currencies->format($_SESSION['cart']->show_total()) . '</li>';

      } else {
        $cart_contents_string .= '<p>' . OSCOM::getDef('module_boxes_shopping_cart_box_cart_empty') . '</p>';
      }

      ob_start();
      include('includes/modules/boxes/templates/shopping_cart.php');
      $data = ob_get_clean();

      $oscTemplate->addBlock($data, $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_BOXES_SHOPPING_CART_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Shopping Cart Module',
        'configuration_key' => 'MODULE_BOXES_SHOPPING_CART_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to add the module to your shop?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Content Placement',
        'configuration_key' => 'MODULE_BOXES_SHOPPING_CART_CONTENT_PLACEMENT',
        'configuration_value' => 'Right Column',
        'configuration_description' => 'Should the module be loaded in the left or right column?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_BOXES_SHOPPING_CART_SORT_ORDER',
        'configuration_value' => '0',
        'configuration_description' => 'Sort order of display. Lowest is displayed first.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    function keys() {
      return array('MODULE_BOXES_SHOPPING_CART_STATUS', 'MODULE_BOXES_SHOPPING_CART_CONTENT_PLACEMENT', 'MODULE_BOXES_SHOPPING_CART_SORT_ORDER');
    }
  }

