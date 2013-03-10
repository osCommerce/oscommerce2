<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_newsletters {
    public static function execute(app $app) {
      global $OSCOM_Customer, $OSCOM_NavigationHistory, $OSCOM_PDO, $newsletter, $OSCOM_Breadcrumb;

      if ( !$OSCOM_Customer->isLoggedOn() ) {
        $OSCOM_NavigationHistory->setSnapshot();

        osc_redirect(osc_href_link('account', 'login', 'SSL'));
      }

      $app->setContentFile('newsletters.php');

      $Qnewsletter = $OSCOM_PDO->prepare('select customers_newsletter from :table_customers where customers_id = :customers_id');
      $Qnewsletter->bindInt(':customers_id', $OSCOM_Customer->getID());
      $Qnewsletter->execute();

      $newsletter = $Qnewsletter->fetch();

      $OSCOM_Breadcrumb->add(NAVBAR_TITLE_NEWSLETTERS, osc_href_link('account', 'newsletters', 'SSL'));
    }
  }
?>
