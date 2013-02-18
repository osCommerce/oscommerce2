<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_products extends app {
    public function __construct() {
      global $OSCOM_PDO, $cPath, $product_exists, $breadcrumb;

      if ( !isset($_GET['id']) ) {
        tep_redirect(tep_href_link('index'));
      }

      $product_exists = false;

//product check query
      $Qpc = $OSCOM_PDO->prepare('select p.products_model from :table_products p, :table_products_description pd where p.products_id = :products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :languages_id');
      $Qpc->bindInt(':products_id', tep_get_prid($_GET['id']));
      $Qpc->bindInt(':languages_id', $_SESSION['languages_id']);
      $Qpc->execute();

      if ( $Qpc->fetch() !== false ) {
        $product_exists = true;

        $model = $Qpc->value('products_model');

        if ( !empty($model) ) {
          $breadcrumb->add($model, tep_href_link('products', 'cPath=' . $cPath . '&id=' . $_GET['id']));
        }
      } else {
        $this->_content_file = 'not_found.php';
      }
    }
  }
?>
