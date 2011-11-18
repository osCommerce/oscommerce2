<?php 
	
/*
 $Id: inpay_functions.php VER: 1.0.3443 $
 osCommerce, Open Source E-Commerce Solutions
 http://www.oscommerce.com
 Copyright (c) 2008 osCommerce
 Released under the GNU General Public License
 */
	
    	
/* Ensure the http_build_query is defined */

if (!function_exists('http_build_query')) { 
    function http_build_query($data, $prefix='', $sep='', $key='') { 
        $ret = array(); 
        foreach ((array)$data as $k => $v) { 
            if (is_int($k) && $prefix != null) { 
                $k = urlencode($prefix . $k); 
            } 
            if ((!empty($key)) || ($key === 0))  $k = $key.'['.urlencode($k).']'; 
            if (is_array($v) || is_object($v)) { 
                array_push($ret, http_build_query($v, '', $sep, $k)); 
            } else { 
                array_push($ret, $k.'='.urlencode($v)); 
            } 
        } 
        if (empty($sep)) $sep = ini_get('arg_separator.output'); 
        return implode($sep, $ret); 
    }// http_build_query 
}//if 

function get_invoice_status($pars) {
    //
    // prepare parameters
    //
    $calc_md5 = calc_inpay_invoice_status_md5key($pars);
    $q = http_build_query(array("merchant_id"=>MODULE_PAYMENT_INPAY_MERCHANT_ID, "invoice_ref"=>$pars['invoice_reference'], "checksum"=>$calc_md5), "", "&");
    //
    // communicate to inpay server
    //
    $fsocket = false;
    $curl = false;
    $result = false;
	$fp = false;
    $server = 'secure.inpay.com';
    if (MODULE_PAYMENT_INPAY_GATEWAY_SERVER != 'Production') {
        $server = 'test-secure.inpay.com';
    }
    
    if ((PHP_VERSION >= 4.3) && ($fp = @fsockopen('ssl://'.$server, 443, $errno, $errstr, 30))) {
        $fsocket = true;
    } elseif (function_exists('curl_exec')) {
        $curl = true;
    }
    if ($fsocket == true) {
        $header = 'POST /api/get_invoice_status HTTP/1.1'."\r\n".
		'Host: '.$server."\r\n".
		'Content-Type: application/x-www-form-urlencoded'."\r\n".
		'Content-Length: '.strlen($q)."\r\n".
		'Connection: close'."\r\n\r\n";
		@fputs($fp, $header.$q);
        $str = '';
        while (!@feof($fp)) {
            $res = @fgets($fp, 1024);
            $str .= (string)$res;
        }
		@fclose($fp);
		$result=$str;
		$result = preg_split('/^\r?$/m', $result, 2);
        $result = trim($result[1]);
    } elseif ($curl == true) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://'.$server.'/api/get_invoice_status');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $q);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
    }
    return (string)$result;
}

function calc_inpay_invoice_status_md5key($pars) {
    $q = http_build_query(array("invoice_ref"=>$pars['invoice_reference'], "merchant_id"=>MODULE_PAYMENT_INPAY_MERCHANT_ID,
	 "secret_key"=>MODULE_PAYMENT_INPAY_SECRET_KEY), "", "&");
    $md5v = md5($q);
    return $md5v;
}
?>
