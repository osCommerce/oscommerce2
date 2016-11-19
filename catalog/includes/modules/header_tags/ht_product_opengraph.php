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

  class ht_product_opengraph {
    var $code = 'ht_product_opengraph';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_header_tags_product_opengraph_title');
      $this->description = OSCOM::getDef('module_header_tags_product_opengraph_description');

      if ( defined('MODULE_HEADER_TAGS_PRODUCT_OPENGRAPH_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_PRODUCT_OPENGRAPH_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_PRODUCT_OPENGRAPH_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate;

      $OSCOM_Db = Registry::get('Db');
      $OSCOM_Language = Registry::get('Language');

      if (basename($PHP_SELF) == 'product_info.php') {
        $Qproduct = $OSCOM_Db->prepare('select
                                          p.products_id,
                                          pd.products_name,
                                          pd.products_description,
                                          p.products_image,
                                          p.products_price,
                                          p.products_quantity,
                                          p.products_tax_class_id,
                                          p.products_date_available
                                        from
                                          :table_products p,
                                          :table_products_description pd
                                        where
                                          p.products_id = :products_id
                                          and p.products_status = 1
                                          and p.products_id = pd.products_id
                                          and pd.language_id = :language_id');
        $Qproduct->bindInt(':products_id', $_GET['products_id']);
        $Qproduct->bindInt(':language_id', $OSCOM_Language->getId());
        $Qproduct->execute();

        if ($Qproduct->fetch() !== false) {
          $data = array('og:type' => 'product',
                        'og:title' => $Qproduct->value('products_name'),
                        'og:site_name' => STORE_NAME);

          $product_description = substr(trim(preg_replace('/\s\s+/', ' ', strip_tags($Qproduct->value('products_description')))), 0, 197) . '...';
          $data['og:description'] = $product_description;

          $products_image = $Qproduct->value('products_image');

          $Qimage = $OSCOM_Db->get('products_images', 'image', ['products_id' => $Qproduct->valueInt('products_id')], 'sort_order', 1);

          if ($Qimage->fetch() !== false) {
            $products_image = $Qimage->value('image');
          }

          $data['og:image'] = OSCOM::linkImage($products_image);

          if ($new_price = tep_get_products_special_price($Qproduct->valueInt('products_id'))) {
            $products_price = $this->format_raw($new_price);
          } else {
            $products_price = $this->format_raw($Qproduct->value('products_price'));
          }
          $data['product:price:amount'] = $products_price;
          $data['product:price:currency'] = $_SESSION['currency'];

          $data['og:url'] = OSCOM::link('product_info.php', 'products_id=' . $Qproduct->valueInt('products_id'), false);

          $data['product:availability'] = ( $Qproduct->valueInt('products_quantity') > 0 ) ? OSCOM::getDef('module_header_tags_product_opengraph_text_in_stock') : OSCOM::getDef('module_header_tags_product_opengraph_text_out_of_stock');

          $result = '';
          foreach ( $data as $key => $value ) {
            $result .= '<meta property="' . HTML::outputProtected($key) . '" content="' . HTML::outputProtected($value) . '" />' . PHP_EOL;
          }

          $oscTemplate->addBlock($result, $this->group);
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_PRODUCT_OPENGRAPH_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Product OpenGraph Module',
        'configuration_key' => 'MODULE_HEADER_TAGS_PRODUCT_OPENGRAPH_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to allow Open Graph Meta Tags (good for Facebook and Pinterest and other sites) to be added to your product page?  Note that your product thumbnails MUST be at least 200px by 200px.',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_HEADER_TAGS_PRODUCT_OPENGRAPH_SORT_ORDER',
        'configuration_value' => '900',
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
      return array('MODULE_HEADER_TAGS_PRODUCT_OPENGRAPH_STATUS', 'MODULE_HEADER_TAGS_PRODUCT_OPENGRAPH_SORT_ORDER');
    }

    function format_raw($number, $currency_code = '', $currency_value = '') {
      global $currencies;

      if (empty($currency_code) || !$currencies->is_set($currency_code)) {
        $currency_code = $_SESSION['currency'];
      }

      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
      }

      return number_format(tep_round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }
  }

