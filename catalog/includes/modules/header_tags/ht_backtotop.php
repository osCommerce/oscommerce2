<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\Registry;

  class ht_backtotop {
    var $code = 'ht_backtotop';
    var $group = 'footer_scripts';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_backtotop() {
      $this->title = MODULE_HEADER_TAGS_BACKTOTOP_TITLE;
      $this->description = MODULE_HEADER_TAGS_BACKTOTOP_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_BACKTOTOP_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_BACKTOTOP_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_BACKTOTOP_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate;
      
      $oscTemplate->addBlock('<style>.back-to-top {cursor: pointer; position: fixed; bottom: 40px; right: 40px; display:none;}</style>',  $this->group);
      $oscTemplate->addBlock('<script>$(document).ready(function(){$(window).scroll(function () {if ($(this).scrollTop() > 50) {$(\'#back-to-top\').fadeIn();} else {$(\'#back-to-top\').fadeOut();}});$(\'#back-to-top\').click(function () {$(\'#back-to-top\').tooltip(\'hide\');$(\'body,html\').animate({scrollTop: 0}, 800); return false; }); $(\'#back-to-top\').tooltip(\'show\');});</script>',  $this->group);
      $oscTemplate->addBlock('<a id="back-to-top" href="#" class="btn btn-primary btn-lg back-to-top" role="button" title="' . MODULE_HEADER_TAGS_BACKTOTOP_LINK . '" data-toggle="tooltip" data-placement="left"><span class="glyphicon glyphicon-chevron-up"></span></a>',  $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_BACKTOTOP_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Back To Top Module',
        'configuration_key' => 'MODULE_HEADER_TAGS_BACKTOTOP_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Add a Back To Top link for long pages?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_HEADER_TAGS_BACKTOTOP_SORT_ORDER',
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
      return array('MODULE_HEADER_TAGS_BACKTOTOP_STATUS', 'MODULE_HEADER_TAGS_BACKTOTOP_SORT_ORDER');
    }
  }

