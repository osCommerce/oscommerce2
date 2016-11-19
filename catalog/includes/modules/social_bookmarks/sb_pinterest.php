<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class sb_pinterest {
    var $code = 'sb_pinterest';
    var $title;
    var $description;
    var $sort_order;
    var $icon;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_social_bookmarks_pinterest_title');
      $this->public_title = OSCOM::getDef('module_social_bookmarks_pinterest_public_title');
      $this->description = OSCOM::getDef('module_social_bookmarks_pinterest_description');

      if ( defined('MODULE_SOCIAL_BOOKMARKS_PINTEREST_STATUS') ) {
        $this->sort_order = MODULE_SOCIAL_BOOKMARKS_PINTEREST_SORT_ORDER;
        $this->enabled = (MODULE_SOCIAL_BOOKMARKS_PINTEREST_STATUS == 'True');
      }
    }

    function getOutput() {
      global $oscTemplate;

      $OSCOM_Db = Registry::get('Db');

// add the js in the footer
      $oscTemplate->addBlock('<script src="//assets.pinterest.com/js/pinit.js"></script>', 'footer_scripts');

      $params = array();

// grab the product name (used for description)
      $params['description'] = tep_get_products_name($_GET['products_id']);

// and image (used for media)
      $Qimage = $OSCOM_Db->get('products', 'products_image', ['products_id' => (int)$_GET['products_id']]);

      if (!empty($Qimage->value('products_image'))) {
        $image_file = $Qimage->value('products_image');

        $Qimage = $OSCOM_Db->get('products_images', 'image', ['products_id' => (int)$_GET['products_id']], 'sort_order');

        if ($Qimage->fetch() !== false) {
          do {
            if (!empty($Qimage->value('image'))) {
              $image_file = $Qimage->value('image'); // overwrite image with first multiple product image
              break;
            }
          } while ($Qimage->fetch());
        }

        $params['media'] = OSCOM::linkImage($image_file);
      }

// url
      $params['url'] = OSCOM::link('product_info.php', 'products_id=' . $_GET['products_id'], false);

      $output = '<a href="http://pinterest.com/pin/create/button/?';

      foreach ($params as $key => $value) {
        $output .= $key . '=' . urlencode($value) . '&';
      }

      $output = substr($output, 0, -1); //remove last & from the url

      $output .= '" class="pin-it-button" count-layout="' . strtolower(MODULE_SOCIAL_BOOKMARKS_PINTEREST_BUTTON_COUNT_POSITION) . '"><img border="0" src="//assets.pinterest.com/images/PinExt.png" title="' . $this->public_title . '" /></a>';

      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function getPublicTitle() {
      return $this->public_title;
    }

    function check() {
      return defined('MODULE_SOCIAL_BOOKMARKS_PINTEREST_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Pinterest Module',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_PINTEREST_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to allow Pinterest Button?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Layout Position',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_PINTEREST_BUTTON_COUNT_POSITION',
        'configuration_value' => 'None',
        'configuration_description' => 'Horizontal or Vertical or None',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'Horizontal\', \'Vertical\', \'None\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_SOCIAL_BOOKMARKS_PINTEREST_SORT_ORDER',
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
      return array('MODULE_SOCIAL_BOOKMARKS_PINTEREST_STATUS', 'MODULE_SOCIAL_BOOKMARKS_PINTEREST_BUTTON_COUNT_POSITION', 'MODULE_SOCIAL_BOOKMARKS_PINTEREST_SORT_ORDER');
    }
  }
?>
