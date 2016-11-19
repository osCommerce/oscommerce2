<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class ht_product_title {
    var $code = 'ht_product_title';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_header_tags_product_title_title');
      $this->description = OSCOM::getDef('module_header_tags_product_title_description');

      if ( defined('MODULE_HEADER_TAGS_PRODUCT_TITLE_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_PRODUCT_TITLE_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_PRODUCT_TITLE_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate;

      $OSCOM_Db = Registry::get('Db');
      $OSCOM_Language = Registry::get('Language');

      if ( (basename($PHP_SELF) == 'product_info.php') || (basename($PHP_SELF) == 'product_reviews.php') ) {
        if (isset($_GET['products_id'])) {
          $Qproduct = $OSCOM_Db->prepare('select pd.products_name, pd.products_seo_title from :table_products p, :table_products_description pd where p.products_id = :products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id');
          $Qproduct->bindInt(':products_id', $_GET['products_id']);
          $Qproduct->bindInt(':language_id', $OSCOM_Language->getId());
          $Qproduct->execute();

          if ($Qproduct->fetch() !== false) {
            if ( tep_not_null($Qproduct->value('products_seo_title')) && (MODULE_HEADER_TAGS_PRODUCT_TITLE_SEO_TITLE_OVERRIDE == 'True') ) {
              $oscTemplate->setTitle($Qproduct->value('products_seo_title') . OSCOM::getDef('module_header_tags_product_seo_separator') . $oscTemplate->getTitle());
            }
            else {
              $oscTemplate->setTitle($Qproduct->value('products_name') . OSCOM::getDef('module_header_tags_product_seo_separator') . $oscTemplate->getTitle());
            }
          }
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_PRODUCT_TITLE_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Product Title Module',
        'configuration_key' => 'MODULE_HEADER_TAGS_PRODUCT_TITLE_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to allow product titles to be added to the page title?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'SEO Title Override?',
        'configuration_key' => 'MODULE_HEADER_TAGS_PRODUCT_TITLE_SEO_TITLE_OVERRIDE',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to allow product titles to be over-ridden by your SEO Titles (if set)?',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'SEO Breadcrumb Override?',
        'configuration_key' => 'MODULE_HEADER_TAGS_PRODUCT_TITLE_SEO_BREADCRUMB_OVERRIDE',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to allow product names in the breadcrumb to be over-ridden by your SEO Titles (if set)?',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_HEADER_TAGS_PRODUCT_TITLE_SORT_ORDER',
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
      return array('MODULE_HEADER_TAGS_PRODUCT_TITLE_STATUS', 'MODULE_HEADER_TAGS_PRODUCT_TITLE_SEO_TITLE_OVERRIDE', 'MODULE_HEADER_TAGS_PRODUCT_TITLE_SEO_BREADCRUMB_OVERRIDE', 'MODULE_HEADER_TAGS_PRODUCT_TITLE_SORT_ORDER');
    }
  }
?>
