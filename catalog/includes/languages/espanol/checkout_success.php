<?php
/*
  $Id: checkout_success.php,v 1.12 2003/07/08 16:45:36 dgw_ Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

define('NAVBAR_TITLE_1', 'Pedido');
define('NAVBAR_TITLE_2', 'Realizado con Exito');

define('HEADING_TITLE', 'Su Pedido ha sido Procesado!');

define('TEXT_SUCCESS', 'Su pedido ha sido realizado con &eacute;xito! Sus productos llegar&aacute;n a su destino de 2 a 5 dias laborales.');
define('TEXT_NOTIFY_PRODUCTS', 'Por favor notifiqueme de cambios realizados a los productos seleccionados:');
define('TEXT_SEE_ORDERS', 'Puede ver sus pedidos viendo la pagina de <a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">\'Su Cuenta\'</a> y pulsando sobre <a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') . '">\'Historial\'</a>.');
define('TEXT_CONTACT_STORE_OWNER', 'Dirija sus preguntas al <a href="' . tep_href_link(FILENAME_CONTACT_US) . '">administrador</a>.');
define('TEXT_THANKS_FOR_SHOPPING', '¡Gracias por comprar con nosotros!');

define('TABLE_HEADING_COMMENTS', 'Introduzca un comentario sobre su pedido');

define('TABLE_HEADING_DOWNLOAD_DATE', 'Fecha Caducidad: ');
define('TABLE_HEADING_DOWNLOAD_COUNT', ' descargas restantes');
define('HEADING_DOWNLOAD', 'Descargue sus productos aqui:');
define('FOOTER_DOWNLOAD', 'Puede descargar sus productos mas tarde en \'%s\'');
?>