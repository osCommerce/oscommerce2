<?php
/*
  $Id: orders_status.php,v 1.7 2002/01/30 11:10:08 harley_vb Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_TITLE', 'Bestellstatus');

define('TABLE_HEADING_ORDERS_STATUS', 'Bestellstatus');
define('TABLE_HEADING_ACTION', 'Aktion');

define('TEXT_INFO_EDIT_INTRO', 'Bitte f&uuml;hren Sie alle notwendigen &Auml;nderungen durch');
define('TEXT_INFO_ORDERS_STATUS_NAME', 'Bestellstatus:');
define('TEXT_INFO_INSERT_INTRO', 'Bitte geben Sie den neuen Bestellstatus mit allen relevanten Daten ein');
define('TEXT_INFO_DELETE_INTRO', 'Sind Sie sicher, dass Sie diesen Bestellstatus l&ouml;schen m&ouml;chten?');
define('TEXT_INFO_HEADING_NEW_ORDERS_STATUS', 'Neuer Bestellstatus');
define('TEXT_INFO_HEADING_EDIT_ORDERS_STATUS', 'Bestellstatus bearbeiten');
define('TEXT_INFO_HEADING_DELETE_ORDERS_STATUS', 'Bestellstatus l&ouml;schen');

define('ERROR_REMOVE_DEFAULT_ORDER_STATUS', 'Fehler: Der Standard-Bestellstatus kann nicht gel&ouml;scht werden. Bitte definieren Sie einen neuen Standard-Bestellstatus und wiederholen Sie den Vorgang.');
define('ERROR_STATUS_USED_IN_ORDERS', 'Fehler: Dieser Bestellstatus wird zur Zeit noch bei den Bestellungen verwendet.');
define('ERROR_STATUS_USED_IN_HISTORY', 'Fehler: Dieser Bestellstatus wird zur Zeit noch in der Bestellhistorie verwendet.');
?>