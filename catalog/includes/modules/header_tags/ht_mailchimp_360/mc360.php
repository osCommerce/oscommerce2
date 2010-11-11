<?php
class mc360 {
    var $system = "osc";
    var $version = "1.1";
    
    var $debug = false;

    var $apikey = '';
    var $key_valid = false;
    var $store_id = '';
    
    function mc360() {
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
                tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = 'true' where configuration_key = 'MODULE_HEADER_TAGS_MAILCHIMP_360_KEY_VALID'");
                
                if (empty($this->store_id)){
                    $this->store_id = md5(uniqid(rand(), true));
                    tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . $this->store_id . "' where configuration_key = 'MODULE_HEADER_TAGS_MAILCHIMP_360_STORE_ID'");
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

        $customer_id = $_SESSION['customer_id'];

        $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where customers_id = '" . (int)$customer_id . "' order by date_purchased desc limit 1");
        $orders = tep_db_fetch_array($orders_query);

        $totals_array = array();
        $totals_query = tep_db_query("select value, class from " . TABLE_ORDERS_TOTAL . " where orders_id = " . (int)$orders['orders_id']);
        while ($totals = tep_db_fetch_array($totals_query)) {
            $totals_array[$totals['class']] = $totals['value'];
        }
        
        $products_array = array();
        $products_query = tep_db_query("select products_id, products_model, products_name, products_tax, products_quantity, final_price from " . TABLE_ORDERS_PRODUCTS . " where orders_id = " . (int)$orders['orders_id']);
        while ($products = tep_db_fetch_array($products_query)) {
            $products_array[] = array('id' => $products['products_id'],
                                    'name' => $products['products_name'],
                                    'model' => $products['products_model'],
                                    'qty' => $products['products_quantity'],
                                    'final_price' => $products['final_price'],
                                    );
            $totals_array['ot_tax'] += $products['product_tax'];
        }

        $mcorder = array(
                'id' => $orders['orders_id'],
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
            $cat_qry = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = " . (int)$product['id']." limit 1");
            $cats = tep_db_fetch_array($cat_qry);
            $cat_id = $cats['categories_id'];
            
            $item['category_id'] = $cat_id;
            $cat_name == '';
            $continue = true; 
            while($continue){            
            //now recurse up the categories tree...
                $cat_qry = tep_db_query("select c.categories_id, c.parent_id, cd.categories_name from  " . TABLE_CATEGORIES . " c inner join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id where c.categories_id =".$cat_id);
                $cats = tep_db_fetch_array($cat_qry);
                if ($cat_name == ''){
                    $cat_name = $cats['categories_name'];
                } else {
                    $cat_name = $cats['categories_name'] .' - '.$cat_name;
                }
                $cat_id = $cats['parent_id'];
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
            tep_mail('', MODULE_HEADER_TAGS_MAILCHIMP_360_DEBUG_EMAIL, 'MailChimp Debug E-Mail', $debug_email, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
        }
  }//update
}//mc360 class

?>
