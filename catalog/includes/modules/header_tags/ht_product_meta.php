<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class ht_product_meta {
    var $code = 'ht_product_meta';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_header_tags_product_meta_title');
      $this->description = OSCOM::getDef('module_header_tags_product_meta_description');

      if ( defined('MODULE_HEADER_TAGS_PRODUCT_META_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_PRODUCT_META_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_PRODUCT_META_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate, $Qproduct, $product_exists;

      $OSCOM_Db = Registry::get('Db');
      $OSCOM_Language = Registry::get('Language');

      if (isset($_GET['products_id'])) {
        if (isset($Qproduct) && ($product_exists === true)) {
          $Qmeta = $OSCOM_Db->prepare('select
                                         pd.products_seo_description,
                                         pd.products_seo_keywords
                                       from
                                         :table_products p,
                                         :table_products_description pd
                                       where
                                         p.products_status = :products_status
                                         and p.products_id = :products_id
                                         and pd.products_id = p.products_id
                                         and pd.language_id = :language_id');
          $Qmeta->bindInt(':products_status', 1);
          $Qmeta->bindInt(':products_id', $_GET['products_id']);
          $Qmeta->bindInt(':language_id', $OSCOM_Language->getId());
          $Qmeta->execute();

          $meta = $Qmeta->fetch();

          if (tep_not_null($meta['products_seo_description'])) {
            $oscTemplate->addBlock('<meta name="description" content="' . HTML::output($meta['products_seo_description']) . '" />' . PHP_EOL, $this->group);
          }
          if ((tep_not_null($meta['products_seo_keywords'])) && (MODULE_HEADER_TAGS_PRODUCT_META_KEYWORDS_STATUS != 'Search') ) {
            $oscTemplate->addBlock('<meta name="keywords" content="' . HTML::output($meta['products_seo_keywords']) . '" />' . PHP_EOL, $this->group);
          }
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_PRODUCT_META_STATUS');
    }

    function install() {
     $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Product Meta Module',
        'configuration_key' => 'MODULE_HEADER_TAGS_PRODUCT_META_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to allow product meta tags to be added to the page header?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Product Meta Module - Keywords',
        'configuration_key' => 'MODULE_HEADER_TAGS_PRODUCT_META_KEYWORDS_STATUS',
        'configuration_value' => 'Search',
        'configuration_description' => 'Keywords can be used for META, for SEARCH, or for BOTH.  If you are into the Chinese Market select Both (for Baidu Search Engine) otherwise select Search.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'Meta\', \'Search\', \'Both\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_HEADER_TAGS_PRODUCT_META_SORT_ORDER',
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
      return array('MODULE_HEADER_TAGS_PRODUCT_META_STATUS', 'MODULE_HEADER_TAGS_PRODUCT_META_KEYWORDS_STATUS', 'MODULE_HEADER_TAGS_PRODUCT_META_SORT_ORDER');
    }
  }
