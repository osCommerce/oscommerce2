<?php
/*
  $Id: orders_status.php,v 1.8 2003/07/06 20:33:01 dgw_ Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_TITLE', 'Estado Pedidos');

define('TABLE_HEADING_ORDERS_STATUS', 'Estado Pedidos');
define('TABLE_HEADING_ACTION', 'Acci&oacute;n');

define('TEXT_INFO_EDIT_INTRO', 'Haga los cambios necesarios');
define('TEXT_INFO_ORDERS_STATUS_NAME', 'Estado Pedido:');
define('TEXT_INFO_INSERT_INTRO', 'Introduzca un nombre y los datos del nuevo estado de pedido');
define('TEXT_INFO_DELETE_INTRO', 'Esta seguro que desea suprimir permanentemente este estado de pedido?');
define('TEXT_INFO_HEADING_NEW_ORDERS_STATUS', 'Nuevo Estado Pedido');
define('TEXT_INFO_HEADING_EDIT_ORDERS_STATUS', 'Editar Estado Pedido');
define('TEXT_INFO_HEADING_DELETE_ORDERS_STATUS', 'Eliminar Estado Pedido');

define('ERROR_REMOVE_DEFAULT_ORDER_STATUS', 'Error: El estado de pedido por defecto no se puede eliminar. Establezca otro estado de pedido predeterminado y pruebe de nuevo.');
define('ERROR_STATUS_USED_IN_ORDERS', 'Error: Este estado de pedido esta siendo usado actualmente.');
define('ERROR_STATUS_USED_IN_HISTORY', 'Error: Este estado de pedido se esta usando en algun hist&oacute;rico de algun pedido.');
?>