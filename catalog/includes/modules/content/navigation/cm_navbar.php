<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class cm_navbar {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    protected $lang;

    function __construct() {
      $this->lang = Registry::get('Language');

      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = OSCOM::getDef('module_content_navbar_title');
      $this->description = OSCOM::getDef('module_content_navbar_description');

      if ( defined('MODULE_CONTENT_NAVBAR_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_NAVBAR_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_NAVBAR_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate;

      $navbar_style   = (MODULE_CONTENT_NAVBAR_STYLE == 'Inverse') ? ' navbar-inverse' : ' navbar-default';
      $navbar_corners = (MODULE_CONTENT_NAVBAR_CORNERS == 'Yes') ? '' : ' navbar-no-corners';
      $navbar_margin  = (MODULE_CONTENT_NAVBAR_MARGIN == 'Yes') ? '' : ' navbar-no-margin';

      switch(MODULE_CONTENT_NAVBAR_FIXED) {
        case 'Top':
          $navbar_fixed = ' navbar-fixed-top';
          $navbar_css   = '<style scoped>body { padding-top: 50px; }</style>';
        break;
        case 'Bottom':
          $navbar_fixed = ' navbar-fixed-bottom';
          $navbar_css   = '<style scoped>body { padding-bottom: 50px; }</style>';
        break;
        default:
          $navbar_fixed = $navbar_css = '';
      }


      if ( defined('MODULE_CONTENT_NAVBAR_INSTALLED') && tep_not_null(MODULE_CONTENT_NAVBAR_INSTALLED) ) {
        $nav_array = explode(';', MODULE_CONTENT_NAVBAR_INSTALLED);

        $navbar_modules = array();

        foreach ( $nav_array as $nbm ) {
          $class = substr($nbm, 0, strrpos($nbm, '.'));

          if ( !class_exists($class) ) {
            $this->lang->loadDefinitions('modules/navbar_modules/' . pathinfo($nbm, PATHINFO_FILENAME));
            require('includes/modules/navbar_modules/' . $class . '.php');
          }

          $nav = new $class();

          if ( $nav->isEnabled() ) {
            $navbar_modules[] = $nav->getOutput();
          }
        }

        if ( !empty($navbar_modules) ) {
      ob_start();
      include('includes/modules/content/' . $this->group . '/templates/navbar.php');
      $template = ob_get_clean();

      $oscTemplate->addContent($template, $this->group);
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_NAVBAR_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Navbar Module',
        'configuration_key' => 'MODULE_CONTENT_NAVBAR_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Should the Navbar be shown?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Navbar Style',
        'configuration_key' => 'MODULE_CONTENT_NAVBAR_STYLE',
        'configuration_value' => 'Inverse',
        'configuration_description' => 'What style should the Navbar have?  See http://getbootstrap.com/components/#navbar-inverted',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_select_option(array(\'Default\', \'Inverse\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Navbar Corners',
        'configuration_key' => 'MODULE_CONTENT_NAVBAR_CORNERS',
        'configuration_value' => 'No',
        'configuration_description' => 'Should the Navbar have Corners?',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_select_option(array(\'Yes\', \'No\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Navbar Margin',
        'configuration_key' => 'MODULE_CONTENT_NAVBAR_MARGIN',
        'configuration_value' => 'No',
        'configuration_description' => 'Should the Navbar have a bottom Margin?',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_select_option(array(\'Yes\', \'No\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Navbar Fixed Position',
        'configuration_key' => 'MODULE_CONTENT_NAVBAR_FIXED',
        'configuration_value' => 'Floating',
        'configuration_description' => 'Should the Navbar stay fixed on Top/Bottom of the page or Floating?',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_select_option(array(\'Floating\', \'Top\', \'Bottom\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_CONTENT_NAVBAR_SORT_ORDER',
        'configuration_value' => '10',
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
      return array('MODULE_CONTENT_NAVBAR_STATUS', 'MODULE_CONTENT_NAVBAR_STYLE', 'MODULE_CONTENT_NAVBAR_CORNERS', 'MODULE_CONTENT_NAVBAR_MARGIN', 'MODULE_CONTENT_NAVBAR_FIXED', 'MODULE_CONTENT_NAVBAR_SORT_ORDER');
    }
  }

