<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

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
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Back To Top Module', 'MODULE_HEADER_TAGS_BACKTOTOP_STATUS', 'True', 'Add a Back To Top link for long pages?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_BACKTOTOP_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_BACKTOTOP_STATUS', 'MODULE_HEADER_TAGS_BACKTOTOP_SORT_ORDER');
    }
  }

