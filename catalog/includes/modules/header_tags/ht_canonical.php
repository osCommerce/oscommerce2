<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
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

    function ht_canonical() {
      $this->title = MODULE_HEADER_TAGS_CANONICAL_TITLE;
      $this->description = MODULE_HEADER_TAGS_CANONICAL_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_CANONICAL_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_CANONICAL_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_CANONICAL_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $cPath, $oscTemplate;

      if (basename($PHP_SELF) == 'product_info.php') {
        $oscTemplate->addBlock('<link rel="canonical" href="' . OSCOM::link('product_info.php', 'products_id=' . (int)$_GET['products_id'], 'NONSSL', false) . '" />' . "\n", $this->group);
      } elseif (basename($PHP_SELF) == 'index.php') {
        if (isset($cPath) && tep_not_null($cPath)) {
          $oscTemplate->addBlock('<link rel="canonical" href="' . OSCOM::link('index.php', 'cPath=' . $cPath, 'NONSSL', false) . '" />' . "\n", $this->group);
        } elseif (isset($_GET['manufacturers_id']) && tep_not_null($_GET['manufacturers_id'])) {
          $oscTemplate->addBlock('<link rel="canonical" href="' . OSCOM::link('index.php', 'manufacturers_id=' . (int)$_GET['manufacturers_id'], 'NONSSL', false) . '" />' . "\n", $this->group);
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
