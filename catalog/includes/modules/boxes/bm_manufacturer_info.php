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

  class bm_manufacturer_info {
    var $code = 'bm_manufacturer_info';
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_boxes_manufacturer_info_title');
      $this->description = OSCOM::getDef('module_boxes_manufacturer_info_description');

      if ( defined('MODULE_BOXES_MANUFACTURER_INFO_STATUS') ) {
        $this->sort_order = MODULE_BOXES_MANUFACTURER_INFO_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_MANUFACTURER_INFO_STATUS == 'True');

        $this->group = ((MODULE_BOXES_MANUFACTURER_INFO_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    function execute() {
      global $oscTemplate;

      $OSCOM_Db = Registry::get('Db');
      $OSCOM_Language = Registry::get('Language');

      if (isset($_GET['products_id'])) {
        $Qmanufacturer = $OSCOM_Db->prepare('select m.manufacturers_id, m.manufacturers_name, m.manufacturers_image, mi.manufacturers_url from :table_manufacturers m left join :table_manufacturers_info mi on (m.manufacturers_id = mi.manufacturers_id and mi.languages_id = :languages_id), :table_products p where p.products_id = :products_id and p.manufacturers_id = m.manufacturers_id');
        $Qmanufacturer->bindInt(':languages_id', $OSCOM_Language->getId());
        $Qmanufacturer->bindInt(':products_id', $_GET['products_id']);
        $Qmanufacturer->execute();

        if ($Qmanufacturer->fetch() !== false) {
          $manufacturer_info_string = null;

          if (!empty($Qmanufacturer->value('manufacturers_image'))) {
            $manufacturer_info_string .= '<div>' . HTML::image(OSCOM::linkImage($Qmanufacturer->value('manufacturers_image')), $Qmanufacturer->value('manufacturers_name')) . '</div>';
          }

          if (!empty($Qmanufacturer->value('manufacturers_url'))) {
            $manufacturer_info_string .= '<div class="text-center"><a href="' . OSCOM::link('redirect.php', 'action=manufacturer&manufacturers_id=' . $Qmanufacturer->valueInt('manufacturers_id')) . '" target="_blank">' . OSCOM::getDef('module_boxes_manufacturer_info_box_homepage', ['manufacturers_name' => $Qmanufacturer->value('manufacturers_name')]) . '</a></div>';
          }

          ob_start();
          include('includes/modules/boxes/templates/manufacturer_info.php');
          $data = ob_get_clean();

          $oscTemplate->addBlock($data, $this->group);
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_BOXES_MANUFACTURER_INFO_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Manufacturer Info Module',
        'configuration_key' => 'MODULE_BOXES_MANUFACTURER_INFO_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to add the module to your shop?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Content Placement',
        'configuration_key' => 'MODULE_BOXES_MANUFACTURER_INFO_CONTENT_PLACEMENT',
        'configuration_value' => 'Right Column',
        'configuration_description' => 'Should the module be loaded in the left or right column?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_BOXES_MANUFACTURER_INFO_SORT_ORDER',
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
      return array('MODULE_BOXES_MANUFACTURER_INFO_STATUS', 'MODULE_BOXES_MANUFACTURER_INFO_CONTENT_PLACEMENT', 'MODULE_BOXES_MANUFACTURER_INFO_SORT_ORDER');
    }
  }

