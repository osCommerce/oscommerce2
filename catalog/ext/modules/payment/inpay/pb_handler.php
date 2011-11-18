<?php
/*
 $Id: pb_handler.php VER: 1.0.3414 $
 osCommerce, Open Source E-Commerce Solutions
 http://www.oscommerce.com
 Copyright (c) 2008 osCommerce
 Released under the GNU General Public License
 */

chdir('../../../../');
require ('includes/application_top.php');
reset($HTTP_POST_VARS);
$result = "VERIFIED";
$ok = true;
$my_order = null;
$my_order_query = null;
//*************************************
// Validate request
//
if (! isset ($HTTP_POST_VARS['order_id']) || !is_numeric($HTTP_POST_VARS['order_id']) || ($HTTP_POST_VARS['order_id'] <= 0))
{
    $ok = false;
    $result = "bad order id";
}
if ($ok)
{
    if (! isset ($HTTP_POST_VARS["invoice_amount"]))
    {
        $ok = false;
        $result = "bad amount";
    }
}
if ($ok)
{
    if (! isset ($HTTP_POST_VARS["invoice_currency"]))
    {
        $ok = false;
        $result = "bad currency";
    }
}
if ($ok)
{
    if (! isset ($HTTP_POST_VARS["checksum"]) || ! isset ($HTTP_POST_VARS["invoice_reference"]) || ! isset ($HTTP_POST_VARS["invoice_created_at"]) || ! isset ($HTTP_POST_VARS["invoice_status"]))
    {
        $ok = false;
        $result = "missing vatiables";
    }
}
if ($ok)
{
    //
    // calc checksum
    //
    $sk = MODULE_PAYMENT_INPAY_SECRET_KEY;
    $q = http_build_query( array (
    "order_id"=>$HTTP_POST_VARS['order_id'],
    "invoice_reference"=>$HTTP_POST_VARS['invoice_reference'],
    "invoice_amount"=>$HTTP_POST_VARS['invoice_amount'],
    "invoice_currency"=>$HTTP_POST_VARS['invoice_currency'],
    "invoice_created_at"=>$HTTP_POST_VARS['invoice_created_at'],
    "invoice_status"=>$HTTP_POST_VARS['invoice_status'],
    "secret_key"=>$sk), "", "&");
    $md5v = md5($q);
    if ($md5v != $HTTP_POST_VARS["checksum"])
    {
        $ok = false;
        $result = "bad checksum";
    }
}
if ($ok)
{
    $my_order_query = tep_db_query("select orders_status, currency, currency_value from ".TABLE_ORDERS." where orders_id = '".$HTTP_POST_VARS['order_id']."'"); // TODO: fix PB to add all params"' and customers_id = '" . (int)$HTTP_POST_VARS['custom'] . "'");
    if (tep_db_num_rows($my_order_query) <= 0)
    {
        $ok = false;
        $result = "order not found";
    }
}
if ($ok)
{
    $my_order = tep_db_fetch_array($my_order_query);
    $order = $my_order;
    $total_query = tep_db_query("select value from ".TABLE_ORDERS_TOTAL." where orders_id = '".$HTTP_POST_VARS['order_id']."' and class = 'ot_total' limit 1");
    $total = tep_db_fetch_array($total_query);
    if (number_format($HTTP_POST_VARS['invoice_amount'], $currencies->get_decimal_places($order['currency'])) != number_format($total['value']*$order['currency_value'], $currencies->get_decimal_places($order['currency'])))
    {
        $ok = false;
        $result = 'Inpay transaction value ('.tep_output_string_protected($HTTP_POST_VARS['invoice_amount']).') does not match order value ('.number_format($total['value']*$order['currency_value'], $currencies->get_decimal_places($order['currency'])).')';
    }
}
if ($ok)
{
    //
    // check status
    //
    $order = $my_order;
    $delivered_status = 3;
    if (($order['orders_status'] == MODULE_PAYMENT_INPAY_COMP_ORDER_STATUS_ID) || ($order['orders_status'] == $delivered_status))
    {
        $ok = false;
        $result = 'Status already in level'.$order['orders_status'];
    }
}
if ($ok) {
    require_once ('inpay_functions.php');
    $invoice_status = get_invoice_status($HTTP_POST_VARS);
    $ok = false;
    if ((($invoice_status == "pending")||($invoice_status == "created"))&&(($HTTP_POST_VARS["invoice_status"] == "pending")||($HTTP_POST_VARS["invoice_status"] == "created"))) {
        $ok = true;
    } else if (($invoice_status == "approved") && ($HTTP_POST_VARS["invoice_status"] == "approved")) {
        $ok = true;
    } else if (($invoice_status == "sum_too_low") && ($HTTP_POST_VARS["invoice_status"] == "sum_too_low")) {
        $ok = true;
    }
	if (!$ok)
	{
		$result = "Bad invoice status:".$invoice_status;
	}
}

//
// Validate request end
//************************************
if ($result == 'VERIFIED')
{
    $order = $my_order;
    $order_status_id = DEFAULT_ORDERS_STATUS_ID;
    $invoice_approved = false;
    switch($HTTP_POST_VARS["invoice_status"])
    {
        case "created":
        case "pending":
            $msg = "customer has been asked to pay ".$HTTP_POST_VARS['invoice_amount']." ".$HTTP_POST_VARS['invoice_currency']." with reference: ".$HTTP_POST_VARS["invoice_reference"]. " via his online bank";
            $order_status_id = MODULE_PAYMENT_INPAY_CREATE_ORDER_STATUS_ID;
            break;
        case "approved":
            $msg = "Inpay has confimed that the payment of ".$HTTP_POST_VARS['invoice_amount']." ".$HTTP_POST_VARS['invoice_currency']." has been received";
            $order_status_id = MODULE_PAYMENT_INPAY_COMP_ORDER_STATUS_ID;
            $invoice_approved = true;
            break;
        case "sum_too_low":
            $msg = "Partial payment received by inpay. Reference: ".$HTTP_POST_VARS["invoice_reference"];
            $order_status_id = MODULE_PAYMENT_INPAY_SUM_TOO_LOW_ORDER_STATUS_ID;
            break;
    }
    $comment_status .= $msg." ;";
    $customer_notified = '0';
    //
    // update order status
    //
    $sql_data_array = array ('orders_id'=>$HTTP_POST_VARS['order_id'],
    'orders_status_id'=>$order_status_id,
    'date_added'=>'now()',
    'customer_notified'=>$customer_notified,
    'comments'=>'Inpay '.ucfirst($HTTP_POST_VARS['invoice_status']).'['.$comment_status.']');
    tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
    tep_db_query("update ".TABLE_ORDERS." set orders_status = '".$order_status_id."', last_modified = now() where orders_id = '".(int)$HTTP_POST_VARS['order_id']."'");
    if ($invoice_approved)
    {
    	// for email
		include(DIR_WS_LANGUAGES . $language . '/modules/payment/inpay.php');
        // let's re-create the required arrays
        require (DIR_WS_CLASSES.'order.php');
        $order = new order($HTTP_POST_VARS['order_id']);
        // START STATUS == COMPLETED LOOP
        // initialized for the email confirmation
        $products_ordered = '';
        $total_tax = 0;

        // let's update the stock
        // #######################################################
        for ($i = 0, $n = sizeof($order->products); $i < $n; $i++)
        { // PRODUCT LOOP STARTS HERE
            // Stock Update - Joao Correia
            if ((MODULE_PAYMENT_INPAY_DECREASE_STOCK_ON_CREATION=='False') && (STOCK_LIMITED == 'true'))
            {
                if (DOWNLOAD_ENABLED == 'true')
                {
                    $stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename 
                                    FROM ".TABLE_PRODUCTS." p
                                    LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES." pa
                                    ON p.products_id=pa.products_id
                                    LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD." pad
                                    ON pa.products_attributes_id=pad.products_attributes_id
                                    WHERE p.products_id = '".tep_get_prid($order->products[$i]['id'])."'";
                    // Will work with only one option for downloadable products
                    // otherwise, we have to build the query dynamically with a loop
                    $products_attributes = $order->products[$i]['attributes'];
                    if (is_array($products_attributes))
                    {
                        $stock_query_raw .= " AND pa.options_id = '".$products_attributes[0]['option_id']."' AND pa.options_values_id = '".$products_attributes[0]['value_id']."'";
                    }
                    $stock_query = tep_db_query($stock_query_raw);
                } else
                {
                    $stock_query = tep_db_query("select products_quantity from ".TABLE_PRODUCTS." where products_id = '".tep_get_prid($order->products[$i]['id'])."'");
                }
                if (tep_db_num_rows($stock_query) > 0)
                {
                    $stock_values = tep_db_fetch_array($stock_query);
                    // do not decrement quantities if products_attributes_filename exists
                    if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename']))
                    {
                        $stock_left = $stock_values['products_quantity']-$order->products[$i]['qty'];
                    } else
                    {
                        $stock_left = $stock_values['products_quantity'];
                    }
                    tep_db_query("update ".TABLE_PRODUCTS." set products_quantity = '".$stock_left."' where products_id = '".tep_get_prid($order->products[$i]['id'])."'");
                    if (($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false'))
                    {
                        tep_db_query("update ".TABLE_PRODUCTS." set products_status = '0' where products_id = '".tep_get_prid($order->products[$i]['id'])."'");
                    }
                }
            } // decrease stock end

            // Update products_ordered (for bestsellers list)
            tep_db_query("update ".TABLE_PRODUCTS." set products_ordered = products_ordered + ".sprintf('%d', $order->products[$i]['qty'])." where products_id = '".tep_get_prid($order->products[$i]['id'])."'");

            // Let's get all the info together for the email
            $total_weight += ($order->products[$i]['qty']*$order->products[$i]['weight']);
            $total_tax += tep_calculate_tax($total_products_price, $products_tax)*$order->products[$i]['qty'];
            $total_cost += $total_products_price;

            // Let's get the attributes
            $products_ordered_attributes = '';
            if (( isset ($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0))
            {
                for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++)
                {
                    $products_ordered_attributes .= "\n\t".$order->products[$i]['attributes'][$j]['option'].' '.$order->products[$i]['attributes'][$j]['value'];
                }
            }

            // Let's format the products model
            $products_model = '';
            if (! empty($order->products[$i]['model']))
            {
                $products_model = ' ('.$order->products[$i]['model'].')';
            }

            // Let's put all the product info together into a string
            $products_ordered .= $order->products[$i]['qty'].' x '.$order->products[$i]['name'].$products_model.' = '.$currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']).$products_ordered_attributes."\n";
        } // PRODUCT LOOP ENDS HERE
        #######################################################

        // lets start with the email confirmation
        // BOF content type fix by AlexStudio
        $content_type = '';
        $content_count = 0;
        // BOF order comment fix
        $comment_query = tep_db_query("select comments from ".TABLE_ORDERS_STATUS_HISTORY." where orders_id = '".$HTTP_POST_VARS['order_id']."'");
        $comment_array = tep_db_fetch_array($comment_query);
        $comments = $comment_array['comments'];
        // EOF order comment fix

        if (DOWNLOAD_ENABLED == 'true')
        {
            $content_query = tep_db_query("select * from ".TABLE_ORDERS_PRODUCTS_DOWNLOAD." where orders_id = '".(int)$HTTP_POST_VARS['order_id']."'");
            $content_count = tep_db_num_rows($content_query);
            if ($content_count > 0)
            {
                $content_type = 'virtual';
            }
        }
        switch($content_type)
        {
            case 'virtual':
                if ($content_count != sizeof($order->products))$content_type = 'mixed';
                break;
            default:
                $content_type = 'physical';
                break;
        }
        // EOF content type fix by AlexStudio
        // $order variables have been changed from checkout_process to work with the variables from the function query () instead of cart () in the order class
        $email_order = STORE_NAME."\n".
        EMAIL_SEPARATOR."\n".
        EMAIL_TEXT_ORDER_NUMBER.' '.$HTTP_POST_VARS['order_id']."\n".
        EMAIL_TEXT_INVOICE_URL.' '.tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id='.$HTTP_POST_VARS['order_id'], 'SSL', false)."\n".
        EMAIL_TEXT_DATE_ORDERED.' '.strftime(DATE_FORMAT_LONG)."\n\n";
        // BOF order comment fix by AlexStudio
        if ($comments)
        {
            // do not add comments
			// $email_order .= $comments."\n\n";
        }
        // EOF order comment fix by AlexStudio
        
        $email_order .= EMAIL_TEXT_PRODUCTS."\n".
        EMAIL_SEPARATOR."\n".
        $products_ordered.
        EMAIL_SEPARATOR."\n";

        for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++)
        {
            $email_order .= strip_tags($order->totals[$i]['title']).' '.strip_tags($order->totals[$i]['text'])."\n";
        }
        // BOF content type fix by AlexStudio
        if ($content_type != 'virtual')
        {
            // EOF content type fix by AlexStudio
            $email_order .= "\n".EMAIL_TEXT_DELIVERY_ADDRESS."\n".
            EMAIL_SEPARATOR."\n".
            tep_address_format($order->delivery['format_id'], $order->delivery, 0, '', "\n")."\n";
        }

        $email_order .= "\n".EMAIL_TEXT_BILLING_ADDRESS."\n".
        EMAIL_SEPARATOR."\n".
        tep_address_format($order->billing['format_id'], $order->billing, 0, '', "\n")."\n\n";
        if (is_object($$payment))
        {
            $email_order .= EMAIL_TEXT_PAYMENT_METHOD."\n".
            EMAIL_SEPARATOR."\n";
            $payment_class = $$payment;
            $email_order .= $payment_class->title."\n\n";
            if ($payment_class->email_footer)
            {
                $email_order .= $payment_class->email_footer."\n\n";
            }
        }
        tep_mail($order->customer['name'], $order->customer['email_address'], EMAIL_TEXT_SUBJECT, nl2br($email_order), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

        // send emails to other people
        if (SEND_EXTRA_ORDER_EMAILS_TO != '')
        {
            tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, EMAIL_TEXT_SUBJECT, nl2br($email_order), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
        }
    } // END oreder approved LOOP



} else
{
    //
    // Invalid result
    //
    //
    // send warning email
    //
    if (tep_not_null(MODULE_PAYMENT_INPAY_DEBUG_EMAIL))
    {
        $email_body = '$HTTP_POST_VARS:'."\n\n";

        reset($HTTP_POST_VARS);
        while ( list ($key, $value) = each($HTTP_POST_VARS))
        {
            $email_body .= $key.'='.$value."\n";
        }

        $email_body .= "\n".'$HTTP_GET_VARS:'."\n\n";

        reset($HTTP_GET_VARS);
        while ( list ($key, $value) = each($HTTP_GET_VARS))
        {
            $email_body .= $key.'='.$value."\n";
        }

        tep_mail('', MODULE_PAYMENT_INPAY_DEBUG_EMAIL, 'Inpay Invalid Process', $email_body, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
    }
    //
    // add error message to history if order can be found
    //
    if ( isset ($HTTP_POST_VARS['order_id']) && is_numeric($HTTP_POST_VARS['order_id']) && ($HTTP_POST_VARS['order_id'] > 0))
    {
        $check_query = tep_db_query("select orders_id from ".TABLE_ORDERS." where orders_id = '".$HTTP_POST_VARS['order_id']."'"); //TODO: fix custom "' and customers_id = '" . (int)$HTTP_POST_VARS['custom'] . "'");
        $order_status_id = $order['orders_status'];
		if (($order_status_id==null)||($order['orders_status']=='')){
		  $order_status_id = DEFAULT_ORDERS_STATUS_ID;
		}
        if (tep_db_num_rows($check_query) > 0)
        {
            $comment_status = $result;
            //tep_db_query("update ".TABLE_ORDERS." set orders_status = '".((MODULE_PAYMENT_INPAY_ORDER_STATUS_ID > 0)?MODULE_PAYMENT_INPAY_ORDER_STATUS_ID:DEFAULT_ORDERS_STATUS_ID)."', last_modified = now() where orders_id = '".$HTTP_POST_VARS['order_id']."'");
            $sql_data_array = array ('orders_id'=>$HTTP_POST_VARS['order_id'],
            'orders_status_id'=>$order_status_id,
            'date_added'=>'now()',
            'customer_notified'=>'0',
            'comments'=>'Inpay Invalid ['.$comment_status.']');
            tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        }
    }
}

require ('includes/application_bottom.php');

?>
