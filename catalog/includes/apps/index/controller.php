<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_index extends app {
    public function __construct() {
      global $OSCOM_PDO, $cPath, $current_category_id;
      global $breadcrumb;

      $category_depth = 'top';

      if ( isset($cPath) && !empty($cPath) ) {
        $category_depth = 'products';

        $Qcp = $OSCOM_PDO->prepare('select categories_id from :table_categories where parent_id = :parent_id limit 1');
        $Qcp->bindInt(':parent_id', $current_category_id);
        $Qcp->execute();

        if ( $Qcp->fetch() !== false ) {
          $category_depth = 'nested';
        }
      }

      if ( $category_depth == 'nested' ) {
        $this->_content_file = 'categories.php';
      } elseif ( ($category_depth == 'products') || (isset($_GET['manufacturers_id']) && !empty($_GET['manufacturers_id'])) ) {
        $this->_content_file = 'products.php';
      }

      if (isset($_GET['specials'])) {
        $this->_content_file = 'specials.php';
        $breadcrumb->add(NAVBAR_TITLE_SPECIALS);
      }

    }
  }
?>
