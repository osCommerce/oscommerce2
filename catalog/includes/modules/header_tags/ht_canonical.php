<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class ht_canonical {
    var $code = 'ht_canonical';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_header_tags_canonical_title');
      $this->description = OSCOM::getDef('module_header_tags_canonical_description');

      if ( defined('MODULE_HEADER_TAGS_CANONICAL_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_CANONICAL_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_CANONICAL_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $cPath, $oscTemplate, $category_depth;

      if (basename($PHP_SELF) == 'product_info.php') {
        $oscTemplate->addBlock('<link rel="canonical" href="' . OSCOM::link('product_info.php', 'products_id=' . (int)$_GET['products_id'], false) . '" />' . "\n", $this->group);
      } elseif (basename($PHP_SELF) == 'index.php') {
        if (isset($cPath) && tep_not_null($cPath) && ($category_depth == 'products')) {
          $oscTemplate->addBlock('<link rel="canonical" href="' . OSCOM::link('index.php', 'view=all&cPath=' . $cPath, false) . '" />' . "\n", $this->group);
        } elseif (isset($_GET['manufacturers_id']) && tep_not_null($_GET['manufacturers_id'])) {
          $oscTemplate->addBlock('<link rel="canonical" href="' . OSCOM::link('index.php', 'view=all&manufacturers_id=' . (int)$_GET['manufacturers_id'], false) . '" />' . "\n", $this->group);
        }
      }
      else {
        $view_all_pages = array('products_new.php', 'specials.php');
        if (in_array(basename($PHP_SELF), $view_all_pages)) {
          $oscTemplate->addBlock('<link rel="canonical" href="' . OSCOM::link($PHP_SELF, 'view=all', false) . '" />' . "\n", $this->group);
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_CANONICAL_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Canonical Module',
        'configuration_key' => 'MODULE_HEADER_TAGS_CANONICAL_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to enable the Canonical module?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_HEADER_TAGS_CANONICAL_SORT_ORDER',
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
      return array('MODULE_HEADER_TAGS_CANONICAL_STATUS', 'MODULE_HEADER_TAGS_CANONICAL_SORT_ORDER');
    }
  }
?>
