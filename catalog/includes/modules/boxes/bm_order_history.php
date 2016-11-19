<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class bm_order_history {
    var $code = 'bm_order_history';
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_boxes_order_history_title');
      $this->description = OSCOM::getDef('module_boxes_order_history_description');

      if ( defined('MODULE_BOXES_ORDER_HISTORY_STATUS') ) {
        $this->sort_order = MODULE_BOXES_ORDER_HISTORY_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_ORDER_HISTORY_STATUS == 'True');

        $this->group = ((MODULE_BOXES_ORDER_HISTORY_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate;

      $OSCOM_Db = Registry::get('Db');
      $OSCOM_Language = Registry::get('Language');

      if (isset($_SESSION['customer_id'])) {
// retreive the last x products purchased
        $Qorders = $OSCOM_Db->prepare('select distinct op.products_id from :table_orders o, :table_orders_products op, :table_products p where o.customers_id = :customers_id and o.orders_id = op.orders_id and op.products_id = p.products_id and p.products_status = 1 group by op.products_id order by o.date_purchased desc limit :limit');
        $Qorders->bindInt(':customers_id', $_SESSION['customer_id']);
        $Qorders->bindInt(':limit', MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX);
        $Qorders->execute();

        if ($Qorders->fetch() !== false) {
          $product_ids = [];

          do {
            $product_ids[] = $Qorders->valueInt('products_id');
          } while ($Qorders->fetch());

          $customer_orders_string = null;

          $Qproducts = $OSCOM_Db->prepare('select products_id, products_name from :table_products_description where products_id in (' . implode(', ', $product_ids) . ') and language_id = :language_id order by products_name');
          $Qproducts->bindInt(':language_id', $OSCOM_Language->getId());
          $Qproducts->execute();

          while ($Qproducts->fetch()) {
            $customer_orders_string .= '<li><span class="pull-right"><a href="' . OSCOM::link(basename($PHP_SELF), tep_get_all_get_params(array('action')) . 'action=cust_order&pid=' . $Qproducts->valueInt('products_id')) . '"><span class="fa fa-shopping-cart"></span></a></span><a href="' . OSCOM::link('product_info.php', 'products_id=' . $Qproducts->valueInt('products_id')) . '">' . $Qproducts->value('products_name') . '</a></li>';
          }

          ob_start();
          include('includes/modules/boxes/templates/order_history.php');
          $data = ob_get_clean();

          $oscTemplate->addBlock($data, $this->group);
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_BOXES_ORDER_HISTORY_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Order History Module',
        'configuration_key' => 'MODULE_BOXES_ORDER_HISTORY_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to add the module to your shop?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Content Placement',
        'configuration_key' => 'MODULE_BOXES_ORDER_HISTORY_CONTENT_PLACEMENT',
        'configuration_value' => 'Right Column',
        'configuration_description' => 'Should the module be loaded in the left or right column?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_BOXES_ORDER_HISTORY_SORT_ORDER',
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
      return array('MODULE_BOXES_ORDER_HISTORY_STATUS', 'MODULE_BOXES_ORDER_HISTORY_CONTENT_PLACEMENT', 'MODULE_BOXES_ORDER_HISTORY_SORT_ORDER');
    }
  }

