<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\Registry;

  class ht_product_title {
    var $code = 'ht_product_title';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_product_title() {
      $this->title = MODULE_HEADER_TAGS_PRODUCT_TITLE_TITLE;
      $this->description = MODULE_HEADER_TAGS_PRODUCT_TITLE_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_PRODUCT_TITLE_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_PRODUCT_TITLE_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_PRODUCT_TITLE_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate;

      $OSCOM_Db = Registry::get('Db');

      if (basename($PHP_SELF) == 'product_info.php') {
        if (isset($_GET['products_id'])) {
          $Qproduct = $OSCOM_Db->prepare('select pd.products_name from :table_products p, :table_products_description pd where p.products_id = :products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id');
          $Qproduct->bindInt(':products_id', $_GET['products_id']);
          $Qproduct->bindInt(':language_id', $_SESSION['languages_id']);
          $Qproduct->execute();

          if ($Qproduct->fetch() !== false) {
            $oscTemplate->setTitle($Qproduct->value('products_name') . ', ' . $oscTemplate->getTitle());
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
      return array('MODULE_HEADER_TAGS_PRODUCT_TITLE_STATUS', 'MODULE_HEADER_TAGS_PRODUCT_TITLE_SORT_ORDER');
    }
  }
?>
