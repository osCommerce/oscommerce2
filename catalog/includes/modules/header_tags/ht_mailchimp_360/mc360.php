<?php
use OSC\OM\Mail;
use OSC\OM\Registry;

class mc360 {
    var $system = "osc";
    var $version = "1.1";

    var $debug = false;

    var $apikey = '';
    var $key_valid = false;
    var $store_id = '';

    function __construct() {
        $this->apikey = MODULE_HEADER_TAGS_MAILCHIMP_360_API_KEY;
        $this->store_id = MODULE_HEADER_TAGS_MAILCHIMP_360_STORE_ID;
        $this->key_valid = ((MODULE_HEADER_TAGS_MAILCHIMP_360_KEY_VALID == 'true') ? true : false);

        if (tep_not_null(MODULE_HEADER_TAGS_MAILCHIMP_360_DEBUG_EMAIL)) {
          $this->debug = true;
        }

        $this->validate_cfg();
    }

    function complain($msg){
            echo '<div style="position:absolute;left:0;top:0;width:100%;font-size:24px;text-align:center;background:#CCCCCC;color:#660000">MC360 Module: '.$msg.'</div><br />';
    }

    function validate_cfg(){
        $OSCOM_Db = Registry::get('Db');

        $this->valid_cfg = false;
        if (empty($this->apikey)){
            $this->complain('You have not entered your API key. Please read the installation instructions.');
            return;
        }

        if (!$this->key_valid){
            $GLOBALS["mc_api_key"] = $this->apikey;
            $api = new MCAPI('notused','notused');
            $res = $api->ping();
            if ($api->errorMessage!=''){
                $this->complain('Server said: "'.$api->errorMessage.'". Your API key is likely invalid. Please read the installation instructions.');
                return;
            } else {
                $this->key_valid = true;
                $OSCOM_Db->save('configuration', ['configuration_value' => 'true'], ['configuration_key' => 'MODULE_HEADER_TAGS_MAILCHIMP_360_KEY_VALID']);

                if (empty($this->store_id)){
                    $this->store_id = md5(uniqid(rand(), true));
                    $OSCOM_Db->save('configuration', ['configuration_value' => $this->store_id], ['configuration_key' => 'MODULE_HEADER_TAGS_MAILCHIMP_360_STORE_ID']);
                }
            }
        }

        if (empty($this->store_id)){
            $this->complain('Your Store ID has not been set. This is not good. Contact support.');
        } else {
            $this->valid_cfg = true;
        }
    }
    function set_cookies(){
        if (!$this->valid_cfg){
            return;
        }
        $thirty_days = time()+60*60*24*30;
        if (isset($_REQUEST['mc_cid'])){
            setcookie('mailchimp_campaign_id',trim($_REQUEST['mc_cid']), $thirty_days);
        }
        if (isset($_REQUEST['mc_eid'])){
            setcookie('mailchimp_email_id',trim($_REQUEST['mc_eid']), $thirty_days);
        }
        return;
    }

    function process() {
        if (!$this->valid_cfg){
            return;
        }

        global $order, $insert_id;

        $OSCOM_Db = Registry::get('Db');

        $orderId = $insert_id; // just to make it obvious.

        $debug_email = '';

        if ($this->debug){
            $debug_email .= '------------[New Order ' . $orderId . ']-----------------' . "\n" .
                            '$order =' . "\n" .
                            print_r($order, true) .
                            '$_COOKIE =' . "\n" .
                            print_r($_COOKIE, true);
        }

        if (!isset($_COOKIE['mailchimp_campaign_id']) || !isset($_COOKIE['mailchimp_email_id'])){
            return;
        }

        if ($this->debug){
            $debug_email .= date('Y-m-d H:i:s') . ' current ids:' . "\n" .
                            date('Y-m-d H:i:s') . ' eid =' . $_COOKIE['mailchimp_email_id'] . "\n" .
                            date('Y-m-d H:i:s') . ' cid =' . $_COOKIE['mailchimp_campaign_id'] . "\n";
        }

        $Qorder = $OSCOM_Db->get('orders', 'orders_id', ['customers_id' => $_SESSION['customer_id']], 'date_purchased desc', 1);

        $totals_array = array();
        $Qtotals = $OSCOM_Db->get('orders_total', ['value', 'class'], ['orders_id' => $Qorder->valueInt('orders_id')]);
        while ($Qtotals->fetch()) {
            $totals_array[$Qtotals->value('class')] = $Qtotals->value('value');
        }

        $products_array = array();
        $Qproducts = $OSCOM_Db->get('orders_products', ['products_id', 'products_model', 'products_name', 'products_tax', 'products_quantity', 'final_price'], ['orders_id' => $Qorder->valueInt('orders_id')]);
        while ($Qproducts->fetch()) {
            $products_array[] = array('id' => $Qproducts->valueInt('products_id'),
                                    'name' => $Qproducts->value('products_name'),
                                    'model' => $Qproducts->value('products_model'),
                                    'qty' => $Qproducts->value('products_quantity'),
                                    'final_price' => $Qproducts->value('final_price'),
                                    );
            $totals_array['ot_tax'] += $Qproducts->value('product_tax');
        }

        $mcorder = array(
                'id' => $Qorder->valueInt('orders_id'),
                'total'=>$totals_array['ot_total'],
                'shipping'=>$totals_array['ot_shipping'],
                'tax'  =>$totals_array['ot_tax'],
                'items'=>array(),
                'store_id'=>$this->store_id,
                'store_name' => $_SERVER['SERVER_NAME'],
                'campaign_id'=>$_COOKIE['mailchimp_campaign_id'],
                'email_id'=>$_COOKIE['mailchimp_email_id'],
                'plugin_id'=>1216
                );

        foreach($products_array as $product){
            $item = array();
            $item['line_num'] = $line;
            $item['product_id'] = $product['id'];
            $item['product_name'] = $product['name'];
            $item['sku'] = $product['model'];
            $item['qty'] = $product['qty'];
            $item['cost'] = $product['final_price'];

            //All this to get a silly category name from here
            $Qcat = $OSCOM_Db->get('products_to_categories', 'categories_id', ['products_id' => $product['id']], null, 1);

            $cat_id = $Qcat->valueInt('categories_id');

            $item['category_id'] = $cat_id;
            $cat_name == '';
            $continue = true;
            while($continue){
            //now recurse up the categories tree...
                $Qcat = $OSCOM_Db->prepare('select c.categories_id, c.parent_id, cd.categories_name from :table_categories c inner join :table_categories_description cd on c.categories_id = cd.categories_id where c.categories_id = :categories_id');
                $Qcat->bindInt(':categories_id', $cat_id);
                $Qcat->execute();

                if ($cat_name == ''){
                    $cat_name = $Qcat->value('categories_name');
                } else {
                    $cat_name = $Qcat->value('categories_name') .' - '.$cat_name;
                }
                $cat_id = $Qcat->valueInt('parent_id');
                if ($cat_id==0){
                    $continue = false;
                }
            }
            $item['category_name'] = $cat_name;

            $mcorder['items'][] = $item;
        }

        $GLOBALS["mc_api_key"] = $this->apikey;
        $api = new MCAPI('notused','notused');
        $res = $api->campaignEcommAddOrder($mcorder);
        if ($api->errorMessage!=''){
            if ($this->debug) {
              $debug_email .= 'Error:' . "\n" .
                               $api->errorMessage . "\n";
            }
        } else {
            //nothing
        }
        // send!()

        if ($this->debug && !empty($debug_email)) {
            $debugEmail = new Mail(MODULE_HEADER_TAGS_MAILCHIMP_360_DEBUG_EMAIL, null, STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, 'MailChimp Debug E-Mail');
            $debugEmail->setBody($debug_email);
            $debugEmail->send();
        }
  }//update
}//mc360 class

?>
