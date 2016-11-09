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

  class ht_twitter_product_card {
    var $code = 'ht_twitter_product_card';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_header_tags_twitter_product_card_title');
      $this->description = OSCOM::getDef('module_header_tags_twitter_product_card_description');

      if ( defined('MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate;

      $OSCOM_Db = Registry::get('Db');
      $OSCOM_Language = Registry::get('Language');

      if ( ($PHP_SELF == 'product_info.php') && isset($_GET['products_id']) ) {
        $Qproduct = $OSCOM_Db->prepare('select p.products_id, pd.products_name, pd.products_description, p.products_image from :table_products p, :table_products_description pd where p.products_id = :products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id');
        $Qproduct->bindInt(':products_id', $_GET['products_id']);
        $Qproduct->bindInt(':language_id', $OSCOM_Language->getId());
        $Qproduct->execute();

        if ($Qproduct->fetch() !== false) {
          $data = array('card' => MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_TYPE,
                        'title' => $Qproduct->value('products_name'));

          if ( tep_not_null(MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_SITE_ID) ) {
            $data['site'] = MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_SITE_ID;
          }

          if ( tep_not_null(MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_USER_ID) ) {
            $data['creator'] = MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_USER_ID;
          }

          $product_description = substr(trim(preg_replace('/\s\s+/', ' ', strip_tags($Qproduct->value('products_description')))), 0, 197);

          if ( strlen($product_description) == 197 ) {
            $product_description .= ' ..';
          }

          $data['description'] = $product_description;

          $products_image = $Qproduct->value('products_image');

          $Qimage = $OSCOM_Db->get('products_images', 'image', ['products_id' => $Qproduct->valueInt('products_id')], 'sort_order', 1);

          if ($Qimage->fetch() !== false) {
            $products_image = $Qimage->value('image');
          }

          $data['image'] = OSCOM::linkImage($products_image);

          $result = '';

          foreach ( $data as $key => $value ) {
            $result .= '<meta name="twitter:' . HTML::outputProtected($key) . '" content="' . HTML::outputProtected($value) . '" />' . "\n";
          }

          $oscTemplate->addBlock($result, $this->group);
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Twitter Card Module',
        'configuration_key' => 'MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to allow Twitter Card tags to be added to your product information pages?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Choose Twitter Card Type',
        'configuration_key' => 'MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_TYPE',
        'configuration_value' => 'summary_large_image',
        'configuration_description' => 'Choose Summary or Summary Large Image.  Note that your product images MUST be at least h120px by w120px (Summary) or h150px x w280px (Summary Large Image).',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'summary\', \'summary_large_image\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Twitter Author @username',
        'configuration_key' => 'MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_USER_ID',
        'configuration_value' => '',
        'configuration_description' => 'Your @username at Twitter',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Twitter Shop @username',
        'configuration_key' => 'MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_SITE_ID',
        'configuration_value' => '',
        'configuration_description' => 'Your shops @username at Twitter (or leave blank if it is the same as your @username above).',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_SORT_ORDER',
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
      return array('MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_STATUS', 'MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_TYPE', 'MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_USER_ID', 'MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_SITE_ID', 'MODULE_HEADER_TAGS_TWITTER_PRODUCT_CARD_SORT_ORDER');
    }
  }
?>
