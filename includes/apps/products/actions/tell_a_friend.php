<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_products_action_tell_a_friend {
    public static function execute(app $app) {
      global $OSCOM_NavigationHistory, $OSCOM_PDO, $Qp, $from_name, $from_email_address, $breadcrumb;

      if ( !isset($_SESSION['customer_id']) && (ALLOW_GUEST_TO_TELL_A_FRIEND == 'false') ) {
        $OSCOM_NavigationHistory->setSnapshot();

        osc_redirect(osc_href_link('account', 'login', 'SSL'));
      }

      if ( isset($_GET['id']) && !empty($_GET['id']) ) {
        $Qp = $OSCOM_PDO->prepare('select pd.products_name from :table_products p, :table_products_description pd where p.products_id = :products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id');
        $Qp->bindInt(':products_id', osc_get_prid($_GET['id']));
        $Qp->bindInt(':language_id', $_SESSION['languages_id']);
        $Qp->execute();

        if ( $Qp->fetch() === false ) {
          osc_redirect(osc_href_link('products', 'id=' . $_GET['id']));
        }

        $Qaccount = $OSCOM_PDO->prepare('select customers_firstname, customers_lastname, customers_email_address from :table_customers where customers_id = :customers_id');
        $Qaccount->bindInt(':customers_id', $_SESSION['customer_id']);
        $Qaccount->execute();

        $from_name = $Qaccount->value('customers_firstname') . ' ' . $Qaccount->value('customers_lastname');
        $from_email_address = $Qaccount->value('customers_email_address');

        $app->setContentFile('tell_a_friend.php');

        $breadcrumb->add(NAVBAR_TITLE_TELL_A_FRIEND, osc_href_link('products', 'tell_a_friend&id=' . $_GET['id']));
      }
    }
  }
?>
