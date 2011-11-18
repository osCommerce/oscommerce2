<?php
/*
 $Id: inpay.php VER: 1.0.3443 $
 osCommerce, Open Source E-Commerce Solutions
 http://www.oscommerce.com
 Copyright (c) 2008 osCommerce
 Released under the GNU General Public License
 */

  define('MODULE_PAYMENT_INPAY_TEXT_TITLE', 'Inpay - instant online bank transfers');
  define('MODULE_PAYMENT_INPAY_TEXT_PUBLIC_TITLE', 'Pay with your online bank - instant and 100% secure');
  define('MODULE_PAYMENT_INPAY_TEXT_PUBLIC_HTML', '<img src="https://resources.inpay.com/images/oscommerce/inpay_checkout.png" alt="Secure checkouts using inpay" /><br /><br />
  <table cellspacing="5">
  	  <tr><td><img src="https://resources.inpay.com/images/oscommerce/inpay_check.png" alt="100% Secure payments using inpay" /></td><td class="main">100% Secure payments using inpay <span style="color: #666;">- our security level matches the security of your online bank.</span></td></tr>
  	  <tr><td><img src="https://resources.inpay.com/images/oscommerce/inpay_check.png" alt="Instant payments using inpay" /></td><td class="main">Instant payments using inpay <span style="color: #666;">- our system ensures you will receive your order as soon as possible.</span></td></tr>
  	  <tr><td><img src="https://resources.inpay.com/images/oscommerce/inpay_check.png" alt="Anonymous payment using inpay" /></td><td class="main">Anonymous payment using inpay <span style="color: #666;">- no need to share your credit card number or any other personal information.</span></td></tr>
  </table><a href="http://inpay.com/shoppers" style="text-decoration: underline;" target="_blank" class="main">Click here to read more about inpay</a><br />');
  define('MODULE_PAYMENT_INPAY_TEXT_DESCRIPTION', '<strong>What is inpay?</strong><br />
  	  inpay is an extra payment option for webshops, that allows customers to pay using their online bank - instantly and worldwide.<br />
  	  <br />
  	  <strong>Increase profits</strong><br />
	By allowing shoppers to pay using their online bank, you can now sell to customers that are otherwise unable or unwilling to pay today.<br />
<br />
<strong>Increase market size</strong><br />
By offering your customers the inpay payment option you increase your market share to not only credit and debit card owners, but also online bank users from all over the world.<br />
<br />
<strong>No risk</strong><br />
With inpay there is no risk of credit card fraud or any kind of chargebacks. This means that when you get paid you stay paid! With inpay you can even sell to customers from \'high risk\' regions including all parts of Asia and Eastern Europe.<br /><br />
  <a href="http://inpay.com/" style="text-decoration: underline;" target="_blank">Read more or signup at inpay.com</a><br />');
  // ------------- e-mail settings ---------------------------------
  define('EMAIL_TEXT_SUBJECT', 'Payment confirmed by inpay');
  define('EMAIL_TEXT_ORDER_NUMBER', 'Order Number:');
  define('EMAIL_TEXT_INVOICE_URL', 'Detailed Invoice:');
  define('EMAIL_TEXT_DATE_ORDERED', 'Date Ordered:');
  define('EMAIL_TEXT_PRODUCTS', 'Products');
  define('EMAIL_TEXT_SUBTOTAL', 'Sub-Total:');
  define('EMAIL_TEXT_TAX', 'Tax:        ');
  define('EMAIL_TEXT_SHIPPING', 'Shipping: ');
  define('EMAIL_TEXT_TOTAL', 'Total:    ');
  define('EMAIL_TEXT_DELIVERY_ADDRESS', 'Delivery Address');
  define('EMAIL_TEXT_BILLING_ADDRESS', 'Billing Address');
  define('EMAIL_TEXT_PAYMENT_METHOD', 'Payment Method');
  define('EMAIL_SEPARATOR', '------------------------------------------------------');
  define('TEXT_EMAIL_VIA', 'via'); 
  
?>