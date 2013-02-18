<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_product_info extends app {
    public function __construct() {
      global $OSCOM_PDO, $cPath, $languages_id, $product_check, $breadcrumb;

      if ( !isset($_GET['products_id']) ) {
        tep_redirect(tep_href_link('index'));
      }

//product check query
      $Qpc = $OSCOM_PDO->prepare('select count(*) as total from :table_products p, :table_products_description pd where p.products_status = 1 and p.products_id = :products_id and pd.products_id = p.products_id and pd.language_id = :languages_id');
      $Qpc->bindInt(':products_id', $_GET['products_id']);
      $Qpc->bindInt(':languages_id', $_SESSION['languages_id']);
      $Qpc->execute();

      $product_check = $Qpc->fetch();
      $languages_id = $_SESSION['languages_id'];

//product breadcrumb
      $Qpbreadcrumb = $OSCOM_PDO->prepare('select products_model from :table_products where products_id = :products_id');
      $Qpbreadcrumb->bindInt(':products_id', $_GET['products_id']);
      $Qpbreadcrumb->execute();
      $model = $Qpbreadcrumb->fetch();

      if ( $model != false ) {
        $breadcrumb->add($model['products_model'], tep_href_link(FILENAME_PRODUCT_INFO, 'cPath=' . $cPath . '&products_id=' . (int)$_GET['products_id']));
      }

      if ($product_check['total'] < 1) {
        $this->_content_file = 'not_found.php';
      }
    }
  }
?>
