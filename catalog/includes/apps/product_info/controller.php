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
      $Qpc = $OSCOM_PDO->prepare('select p.products_model from :table_products p, :table_products_description pd where p.products_id = :products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :languages_id');
      $Qpc->bindInt(':products_id', tep_get_prid($_GET['products_id']));
      $Qpc->bindInt(':languages_id', $_SESSION['languages_id']);
      $Qpc->execute();

      if ( $Qpc->fetch() !== false ) {
        $model = $Qpc->value('products_model');

        if ( !empty($model) ) {
          $breadcrumb->add($model, tep_href_link(FILENAME_PRODUCT_INFO, 'cPath=' . $cPath . '&products_id=' . $_GET['products_id']));
        }
      } else {
        $this->_content_file = 'not_found.php';
      }
    }
  }
?>
