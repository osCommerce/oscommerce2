<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class cm_in_new_products {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = OSCOM::getDef('module_content_in_new_products_title');
      $this->description = OSCOM::getDef('module_content_in_new_products_description');
      $this->description .= '<div class="secWarning">' . OSCOM::getDef('module_content_bootstrap_row_description') . '</div>';

      if ( defined('MODULE_CONTENT_IN_NEW_PRODUCTS_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_IN_NEW_PRODUCTS_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_IN_NEW_PRODUCTS_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate, $current_category_id, $currencies, $PHP_SELF;

      $OSCOM_Db = Registry::get('Db');
      $OSCOM_Language = Registry::get('Language');

      $content_width = MODULE_CONTENT_IN_NEW_PRODUCTS_CONTENT_WIDTH;
      $product_width = MODULE_CONTENT_IN_NEW_PRODUCTS_DISPLAY_EACH;

      $Qproducts = $OSCOM_Db->prepare('select
                                         distinct p.products_id, p.products_image, p.products_tax_class_id,
                                         pd.products_name,
                                         if (s.status, s.specials_new_products_price, p.products_price) as products_price
                                       from
                                         :table_products p left join :table_specials s on p.products_id = s.products_id,
                                         :table_products_description pd,
                                         :table_products_to_categories p2c,
                                         :table_categories c
                                       where
                                         p.products_id = p2c.products_id
                                         and p2c.categories_id = c.categories_id
                                         and c.parent_id = :parent_id
                                         and p.products_status = 1
                                         and p.products_id = pd.products_id
                                         and pd.language_id = :language_id
                                       order by
                                         p.products_date_added desc
                                       limit
                                         :limit');
      $Qproducts->bindInt(':parent_id', $current_category_id);
      $Qproducts->bindInt(':language_id', $OSCOM_Language->getId());
      $Qproducts->bindInt(':limit', MODULE_CONTENT_IN_NEW_PRODUCTS_MAX_DISPLAY);
      $Qproducts->execute();

      $new_products = $Qproducts->fetchAll();

      $num_new_products = count($new_products);

      if ($num_new_products > 0) {
        ob_start();
        include('includes/modules/content/' . $this->group . '/templates/category_new_products.php');
        $template = ob_get_clean();

        $oscTemplate->addContent($template, $this->group);
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_IN_NEW_PRODUCTS_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable New Products Module',
        'configuration_key' => 'MODULE_CONTENT_IN_NEW_PRODUCTS_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to enable this module?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Content Width',
        'configuration_key' => 'MODULE_CONTENT_IN_NEW_PRODUCTS_CONTENT_WIDTH',
        'configuration_value' => '12',
        'configuration_description' => 'What width container should the content be shown in? (12 = full width, 6 = half width).',
        'configuration_group_id' => '6',
        'sort_order' => '2',
        'set_function' => 'tep_cfg_select_option(array(\'12\', \'11\', \'10\', \'9\', \'8\', \'7\', \'6\', \'5\', \'4\', \'3\', \'2\', \'1\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Maximum Display',
        'configuration_key' => 'MODULE_CONTENT_IN_NEW_PRODUCTS_MAX_DISPLAY',
        'configuration_value' => '6',
        'configuration_description' => 'Maximum Number of products that should show in this module?',
        'configuration_group_id' => '6',
        'sort_order' => '3',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Product Width',
        'configuration_key' => 'MODULE_CONTENT_IN_NEW_PRODUCTS_DISPLAY_EACH',
        'configuration_value' => '3',
        'configuration_description' => 'What width container should each product be shown in? (12 = full width, 6 = half width).',
        'configuration_group_id' => '6',
        'sort_order' => '4',
        'set_function' => 'tep_cfg_select_option(array(\'12\', \'11\', \'10\', \'9\', \'8\', \'7\', \'6\', \'5\', \'4\', \'3\', \'2\', \'1\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_CONTENT_IN_NEW_PRODUCTS_SORT_ORDER',
        'configuration_value' => '300',
        'configuration_description' => 'Sort order of display. Lowest is displayed first.',
        'configuration_group_id' => '6',
        'sort_order' => '5',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    function keys() {
      return array('MODULE_CONTENT_IN_NEW_PRODUCTS_STATUS', 'MODULE_CONTENT_IN_NEW_PRODUCTS_CONTENT_WIDTH', 'MODULE_CONTENT_IN_NEW_PRODUCTS_MAX_DISPLAY', 'MODULE_CONTENT_IN_NEW_PRODUCTS_DISPLAY_EACH', 'MODULE_CONTENT_IN_NEW_PRODUCTS_SORT_ORDER');
    }
  }
