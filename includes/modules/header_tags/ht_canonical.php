<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

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
      global $OSCOM_APP, $OSCOM_Template, $cPath;

      if ( ($OSCOM_APP->getCode() == 'products') && ($OSCOM_APP->getCurrentAction() == null) && isset($_GET['id']) ) {
        $OSCOM_Template->addBlock('<link rel="canonical" href="' . osc_href_link('products', 'id=' . $_GET['id'], 'NONSSL', false) . '" />' . "\n", $this->group);
      } elseif ( $OSCOM_APP->getCode() == 'index' ) {
        if (isset($cPath) && osc_not_null($cPath)) {
          $OSCOM_Template->addBlock('<link rel="canonical" href="' . osc_href_link(null, 'cPath=' . $cPath, 'NONSSL', false) . '" />' . "\n", $this->group);
        } elseif (isset($_GET['manufacturers_id']) && osc_not_null($_GET['manufacturers_id'])) {
          $OSCOM_Template->addBlock('<link rel="canonical" href="' . osc_href_link(null, 'manufacturers_id=' . $_GET['manufacturers_id'], 'NONSSL', false) . '" />' . "\n", $this->group);
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
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Canonical Module', 'MODULE_HEADER_TAGS_CANONICAL_STATUS', 'True', 'Do you want to enable the Canonical module?', '6', '1', 'osc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_CANONICAL_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      osc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_CANONICAL_STATUS', 'MODULE_HEADER_TAGS_CANONICAL_SORT_ORDER');
    }
  }
?>
