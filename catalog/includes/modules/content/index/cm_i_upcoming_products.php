<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class cm_i_upcoming_products {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = OSCOM::getDef('module_content_upcoming_products_title');
      $this->description = OSCOM::getDef('module_content_upcoming_products_description');
      $this->description .= '<div class="secWarning">' . OSCOM::getDef('module_content_bootstrap_row_description') . '</div>';

      if ( defined('MODULE_CONTENT_UPCOMING_PRODUCTS_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_UPCOMING_PRODUCTS_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_UPCOMING_PRODUCTS_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate;

      $OSCOM_Db = Registry::get('Db');
      $OSCOM_Language = Registry::get('Language');

      $content_width = MODULE_CONTENT_UPCOMING_PRODUCTS_CONTENT_WIDTH;

      $sort_field = (MODULE_CONTENT_UPCOMING_PRODUCTS_EXPECTED_FIELD == 'date_expected') ? 'date_expected' : 'products_name';
      $sort_order = (MODULE_CONTENT_UPCOMING_PRODUCTS_EXPECTED_SORT == 'desc') ? 'desc' : '';

      $sql = 'select
                p.products_id,
                pd.products_name,
                products_date_available as date_expected
              from
                :table_products p,
                :table_products_description pd
              where
                to_days(products_date_available) >= to_days(now())
                and p.products_id = pd.products_id
                and pd.language_id = :language_id
              order by
                ' . $sort_field . ' ' . $sort_order . '
              limit
                :limit';

      $Qproducts = $OSCOM_Db->prepare($sql);
      $Qproducts->bindInt(':language_id', $OSCOM_Language->getId());
      $Qproducts->bindInt(':limit', MODULE_CONTENT_UPCOMING_PRODUCTS_MAX_DISPLAY);
      $Qproducts->execute();

      $products = $Qproducts->fetchAll();
      $products_total = count($products);

      if ($products_total > 0) {
        ob_start();
        include('includes/modules/content/' . $this->group . '/templates/upcoming_products.php');
        $template = ob_get_clean();

        $oscTemplate->addContent($template, $this->group);
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_UPCOMING_PRODUCTS_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable New Products Module',
        'configuration_key' => 'MODULE_CONTENT_UPCOMING_PRODUCTS_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to enable this module?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Content Width',
        'configuration_key' => 'MODULE_CONTENT_UPCOMING_PRODUCTS_CONTENT_WIDTH',
        'configuration_value' => '12',
        'configuration_description' => 'What width container should the content be shown in? (12 = full width, 6 = half width).',
        'configuration_group_id' => '6',
        'sort_order' => '2',
        'set_function' => 'tep_cfg_select_option(array(\'12\', \'11\', \'10\', \'9\', \'8\', \'7\', \'6\', \'5\', \'4\', \'3\', \'2\', \'1\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Maximum Display',
        'configuration_key' => 'MODULE_CONTENT_UPCOMING_PRODUCTS_MAX_DISPLAY',
        'configuration_value' => '6',
        'configuration_description' => 'Maximum Number of products that should show in this module?',
        'configuration_group_id' => '6',
        'sort_order' => '3',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_CONTENT_UPCOMING_PRODUCTS_EXPECTED_SORT',
        'configuration_value' => 'desc',
        'configuration_description' => 'This is the sort order used in the output.',
        'configuration_group_id' => '1',
        'sort_order' => '4',
        'set_function' => 'tep_cfg_select_option(array(\'asc\', \'desc\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Field',
        'configuration_key' => 'MODULE_CONTENT_UPCOMING_PRODUCTS_EXPECTED_FIELD',
        'configuration_value' => 'date_expected',
        'configuration_description' => 'The column to sort by in the output.',
        'configuration_group_id' => '1',
        'sort_order' => '5',
        'set_function' => 'tep_cfg_select_option(array(\'products_name\', \'date_expected\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_CONTENT_UPCOMING_PRODUCTS_SORT_ORDER',
        'configuration_value' => '400',
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
      return array('MODULE_CONTENT_UPCOMING_PRODUCTS_STATUS', 'MODULE_CONTENT_UPCOMING_PRODUCTS_CONTENT_WIDTH', 'MODULE_CONTENT_UPCOMING_PRODUCTS_MAX_DISPLAY', 'MODULE_CONTENT_UPCOMING_PRODUCTS_EXPECTED_SORT', 'MODULE_CONTENT_UPCOMING_PRODUCTS_EXPECTED_FIELD', 'MODULE_CONTENT_UPCOMING_PRODUCTS_SORT_ORDER');
    }
  }