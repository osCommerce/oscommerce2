<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class bm_best_sellers {
    var $code = 'bm_best_sellers';
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_boxes_best_sellers_title');
      $this->description = OSCOM::getDef('module_boxes_best_sellers_description');

      if ( defined('MODULE_BOXES_BEST_SELLERS_STATUS') ) {
        $this->sort_order = MODULE_BOXES_BEST_SELLERS_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_BEST_SELLERS_STATUS == 'True');

        $this->group = ((MODULE_BOXES_BEST_SELLERS_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    function execute() {
      global $current_category_id, $oscTemplate;

      $OSCOM_Db = Registry::get('Db');
      $OSCOM_Language = Registry::get('Language');

      if (!isset($_GET['products_id'])) {
        if (isset($current_category_id) && ($current_category_id > 0)) {
          $sql = 'select distinct p.products_id, pd.products_name from :table_products p, :table_products_description pd, :table_products_to_categories p2c, :table_categories c where p.products_status = 1 and p.products_ordered > 0 and p.products_id = pd.products_id and pd.language_id = :language_id and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and :category_id in (c.categories_id, c.parent_id) order by p.products_ordered desc, pd.products_name limit :limit';
        } else {
          $sql = 'select distinct p.products_id, pd.products_name from :table_products p, :table_products_description pd where p.products_status = 1 and p.products_ordered > 0 and p.products_id = pd.products_id and pd.language_id = :language_id order by p.products_ordered desc, pd.products_name limit :limit';
        }

        $Qbest = $OSCOM_Db->prepare($sql);
        $Qbest->bindInt(':language_id', $OSCOM_Language->getId());

        if (isset($current_category_id) && ($current_category_id > 0)) {
          $Qbest->bindInt(':category_id', $current_category_id);
        }

        $Qbest->bindInt(':limit', MAX_DISPLAY_BESTSELLERS);
        $Qbest->execute();

        $best = $Qbest->fetchAll();

        if (count($best) >= MIN_DISPLAY_BESTSELLERS) {
          $bestsellers_list = '';

          foreach ($best as $b) {
            $bestsellers_list .= '<li><a href="' . OSCOM::link('product_info.php', 'products_id=' . $b['products_id']) . '"><span itemprop="itemListElement">' . $b['products_name'] . '</span></a></li>';
          }

          $num_best_sellers = count($best);

          ob_start();
          include('includes/modules/boxes/templates/best_sellers.php');
          $data = ob_get_clean();

          $oscTemplate->addBlock($data, $this->group);
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_BOXES_BEST_SELLERS_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Best Sellers Module',
        'configuration_key' => 'MODULE_BOXES_BEST_SELLERS_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to add the module to your shop?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Content Placement',
        'configuration_key' => 'MODULE_BOXES_BEST_SELLERS_CONTENT_PLACEMENT',
        'configuration_value' => 'Right Column',
        'configuration_description' => 'Should the module be loaded in the left or right column?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_BOXES_BEST_SELLERS_SORT_ORDER',
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
      return array('MODULE_BOXES_BEST_SELLERS_STATUS', 'MODULE_BOXES_BEST_SELLERS_CONTENT_PLACEMENT', 'MODULE_BOXES_BEST_SELLERS_SORT_ORDER');
    }
  }

