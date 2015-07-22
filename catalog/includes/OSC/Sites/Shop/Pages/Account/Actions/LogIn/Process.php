<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Sites\Shop\Pages\Account\Actions\LogIn;

use OSC\OM\HTTP;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

class Process extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        global $login_customer_id;

        $OSCOM_Db = Registry::get('Db');

        if (is_int($login_customer_id) && ($login_customer_id > 0)) {
            if (SESSION_RECREATE == 'True') {
                tep_session_recreate();
            }

            $Qcustomer = $OSCOM_Db->prepare('select c.customers_firstname, c.customers_default_address_id, ab.entry_country_id, ab.entry_zone_id from :table_customers c left join :table_address_book ab on (c.customers_id = ab.customers_id and c.customers_default_address_id = ab.address_book_id) where c.customers_id = :customers_id');
            $Qcustomer->bindInt(':customers_id', $login_customer_id);
            $Qcustomer->execute();

            $_SESSION['customer_id'] = $login_customer_id;
            $_SESSION['customer_default_address_id'] = $Qcustomer->valueInt('customers_default_address_id');
            $_SESSION['customer_first_name'] = $Qcustomer->value('customers_firstname');
            $_SESSION['customer_country_id'] = $Qcustomer->valueInt('entry_country_id');
            $_SESSION['customer_zone_id'] = $Qcustomer->valueInt('entry_zone_id');

            $Qupdate = $OSCOM_Db->prepare('update :table_customers_info set customers_info_date_of_last_logon = now(), customers_info_number_of_logons = customers_info_number_of_logons+1, password_reset_key = null, password_reset_date = null where customers_info_id = :customers_info_id');
            $Qupdate->bindInt(':customers_info_id', $_SESSION['customer_id']);
            $Qupdate->execute();

// reset session token
            $_SESSION['sessiontoken'] = md5(tep_rand() . tep_rand() . tep_rand() . tep_rand());

// restore cart contents
            $_SESSION['cart']->restore_contents();

            if (count($_SESSION['navigation']->snapshot) > 0) {
                $origin_href = OSCOM::link($_SESSION['navigation']->snapshot['page'], tep_array_to_string($_SESSION['navigation']->snapshot['get'], array(session_name())), $_SESSION['navigation']->snapshot['mode']);
                $_SESSION['navigation']->clear_snapshot();

                HTTP::redirect($origin_href);
            }

            OSCOM::redirect('index.php');
        }
    }
}
