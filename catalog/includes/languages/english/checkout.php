<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

 define('NAVBAR_TITLE', 'Checkout');
 define('NAVBAR_TITLE_SHIPPING', 'Shipping Method');
 define('NAVBAR_TITLE_PAYMENT', 'Payment Method');
 define('NAVBAR_TITLE_CONFIRMATION', 'Confirmation');
 define('NAVBAR_TITLE_SUCCESS', 'Success');

 define('HEADING_TITLE_SHIPPING', 'Delivery Information');
 define('HEADING_TITLE_PAYMENT', 'Payment Information');
 define('HEADING_TITLE_CONFIRMATION', 'Order Confirmation');
 define('HEADING_TITLE_SUCCESS', 'Your Order Has Been Processed!');

 define('TITLE_CONTINUE_CHECKOUT_PROCEDURE', 'Continue Checkout Procedure');
 define('TEXT_CONTINUE_CHECKOUT_PROCEDURE_SHIPPING', 'to select the preferred payment method.');
 define('TEXT_CONTINUE_CHECKOUT_PROCEDURE_PAYMENT', 'to confirm this order.');

 define('TITLE_PLEASE_SELECT', 'Please Select');
 define('TABLE_HEADING_COMMENTS', 'Add Comments About Your Order');

 define('TABLE_HEADING_SHIPPING_ADDRESS', 'Shipping Address');
 define('TEXT_CHOOSE_SHIPPING_DESTINATION', 'Please choose from your address book where you would like the items to be delivered to.');
 define('TITLE_SHIPPING_ADDRESS', 'Shipping Address:');

 define('TABLE_HEADING_SHIPPING_METHOD', 'Shipping Method');
 define('TEXT_CHOOSE_SHIPPING_METHOD', 'Please select the preferred shipping method to use on this order.');
 define('TEXT_ENTER_SHIPPING_INFORMATION', 'This is currently the only shipping method available to use on this order.');

 define('TABLE_HEADING_BILLING_ADDRESS', 'Billing Address');
 define('TEXT_SELECTED_BILLING_DESTINATION', 'Please choose from your address book where you would like the invoice to be sent to.');
 define('TITLE_BILLING_ADDRESS', 'Billing Address:');

 define('TABLE_HEADING_PAYMENT_METHOD', 'Payment Method');
 define('TEXT_SELECT_PAYMENT_METHOD', 'Please select the preferred payment method to use on this order.');
 define('TEXT_ENTER_PAYMENT_INFORMATION', 'This is currently the only payment method available to use on this order.');

 define('HEADING_SHIPPING_INFORMATION', 'Shipping Information');
 define('HEADING_DELIVERY_ADDRESS', 'Delivery Address');
 define('HEADING_SHIPPING_METHOD', 'Shipping Method');
 define('HEADING_PRODUCTS', 'Products');
 define('HEADING_TAX', 'Tax');
 define('HEADING_TOTAL', 'Total');
 define('HEADING_BILLING_INFORMATION', 'Billing Information');
 define('HEADING_BILLING_ADDRESS', 'Billing Address');
 define('HEADING_PAYMENT_METHOD', 'Payment Method');
 define('HEADING_PAYMENT_INFORMATION', 'Payment Information');
 define('HEADING_ORDER_COMMENTS', 'Comments About Your Order');

 define('TEXT_EDIT', 'Edit');

 define('EMAIL_TEXT_SUBJECT', 'Order Process');
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

 define('TEXT_SUCCESS', 'Your order has been successfully processed! Your products will arrive at their destination within 2-5 working days.');
 define('TEXT_NOTIFY_PRODUCTS', 'Please notify me of updates to the products I have selected below:');
 define('TEXT_SEE_ORDERS', 'You can view your order history by going to the <a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">\'My Account\'</a> page and by clicking on <a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') . '">\'History\'</a>.');
 define('TEXT_CONTACT_STORE_OWNER', 'Please direct any questions you have to the <a href="' . tep_href_link(FILENAME_CONTACT_US) . '">store owner</a>.');
 define('TEXT_THANKS_FOR_SHOPPING', 'Thanks for shopping with us online!');

 define('TABLE_HEADING_DOWNLOAD_DATE', 'Expiry date: ');
 define('TABLE_HEADING_DOWNLOAD_COUNT', ' downloads remaining');
 define('HEADING_DOWNLOAD', 'Download your products here:');
 define('FOOTER_DOWNLOAD', 'You can also download your products at a later time at \'%s\'');
?>
