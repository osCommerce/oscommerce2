<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class cm_in_category_listing {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = OSCOM::getDef('module_content_in_category_listing_title');
      $this->description = OSCOM::getDef('module_content_in_category_listing_description');
      $this->description .= '<div class="secWarning">' . OSCOM::getDef('module_content_bootstrap_row_description') . '</div>';

      if ( defined('MODULE_CONTENT_IN_CATEGORY_LISTING_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_IN_CATEGORY_LISTING_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_IN_CATEGORY_LISTING_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate, $category, $cPath_array, $cPath, $current_category_id;

      $OSCOM_Db = Registry::get('Db');
      $OSCOM_Language = Registry::get('Language');

      $content_width  = MODULE_CONTENT_IN_CATEGORY_LISTING_CONTENT_WIDTH;
      $category_width = MODULE_CONTENT_IN_CATEGORY_LISTING_CONTENT_WIDTH_EACH;

      if (isset($cPath) && strpos('_', $cPath)) {
// check to see if there are deeper categories within the current category
        $category_links = array_reverse($cPath_array);
        for($i=0, $n=sizeof($category_links); $i<$n; $i++) {
          $Qcategories = $OSCOM_Db->prepare('select categories_id from :table_categories c, :table_categories_description cd where c.parent_id = :parent_id and c.categories_id = cd.categories_id and cd.language_id = :language_id limit 1');
          $Qcategories->bindInt(':parent_id', $category_links[$i]);
          $Qcategories->bindInt(':language_id', $OSCOM_Language->getId());
          $Qcategories->execute();

          if (count($Qcategories->fetchAll()) < 1) {
            // do nothing, go through the loop
          } else {
            $Qcategories = $OSCOM_Db->prepare('select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from :table_categories c, :table_categories_description cd where c.parent_id = :parent_id and c.categories_id = cd.categories_id and cd.language_id = :language_id order by sort_order, cd.categories_name');
            $Qcategories->bindInt(':parent_id', $category_links[$i]);
            $Qcategories->bindInt(':language_id', $OSCOM_Language->getId());
            $Qcategories->execute();

            break; // we've found the deepest category the customer is in
          }
        }
      } else {
        $Qcategories = $OSCOM_Db->prepare('select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from :table_categories c, :table_categories_description cd where c.parent_id = :parent_id and c.categories_id = cd.categories_id and cd.language_id = :language_id order by sort_order, cd.categories_name');
        $Qcategories->bindInt(':parent_id', $current_category_id);
        $Qcategories->bindInt(':language_id', $OSCOM_Language->getId());
        $Qcategories->execute();
      }

      $categories = $Qcategories->fetchAll();

      ob_start();
      include('includes/modules/content/' . $this->group . '/templates/category_listing.php');
      $template = ob_get_clean();

      $oscTemplate->addContent($template, $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_IN_CATEGORY_LISTING_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Category Listing Module',
        'configuration_key' => 'MODULE_CONTENT_IN_CATEGORY_LISTING_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Should this module be enabled?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Content Width',
        'configuration_key' => 'MODULE_CONTENT_IN_CATEGORY_LISTING_CONTENT_WIDTH',
        'configuration_value' => '12',
        'configuration_description' => 'What width container should the content be shown in?',
        'configuration_group_id' => '6',
        'sort_order' => '2',
        'set_function' => 'tep_cfg_select_option(array(\'12\', \'11\', \'10\', \'9\', \'8\', \'7\', \'6\', \'5\', \'4\', \'3\', \'2\', \'1\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Category Width',
        'configuration_key' => 'MODULE_CONTENT_IN_CATEGORY_LISTING_CONTENT_WIDTH_EACH',
        'configuration_value' => '4',
        'configuration_description' => 'What width container should each Category be shown in?',
        'configuration_group_id' => '6',
        'sort_order' => '3',
        'set_function' => 'tep_cfg_select_option(array(\'12\', \'11\', \'10\', \'9\', \'8\', \'7\', \'6\', \'5\', \'4\', \'3\', \'2\', \'1\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_CONTENT_IN_CATEGORY_LISTING_SORT_ORDER',
        'configuration_value' => '200',
        'configuration_description' => 'Sort order of display. Lowest is displayed first.',
        'configuration_group_id' => '6',
        'sort_order' => '4',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    function keys() {
      return array('MODULE_CONTENT_IN_CATEGORY_LISTING_STATUS', 'MODULE_CONTENT_IN_CATEGORY_LISTING_CONTENT_WIDTH', 'MODULE_CONTENT_IN_CATEGORY_LISTING_CONTENT_WIDTH_EACH', 'MODULE_CONTENT_IN_CATEGORY_LISTING_SORT_ORDER');
    }
  }
