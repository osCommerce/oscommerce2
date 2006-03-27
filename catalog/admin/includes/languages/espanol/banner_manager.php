<?php
/*
  $Id: banner_manager.php,v 1.21 2003/07/07 09:23:06 dgw_ Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_TITLE', 'Administrador de Banners');

define('TABLE_HEADING_BANNERS', 'Banners');
define('TABLE_HEADING_GROUPS', 'Grupos');
define('TABLE_HEADING_STATISTICS', 'Vistas / Clicks');
define('TABLE_HEADING_STATUS', 'Estado');
define('TABLE_HEADING_ACTION', 'Acci&oacute;n');

define('TEXT_BANNERS_TITLE', 'T&iacute;tulo:');
define('TEXT_BANNERS_URL', 'URL:');
define('TEXT_BANNERS_GROUP', 'Grupo:');
define('TEXT_BANNERS_NEW_GROUP', ', o introduzca un grupo nuevo');
define('TEXT_BANNERS_IMAGE', 'Imagen:');
define('TEXT_BANNERS_IMAGE_LOCAL', ', o introduzca un fichero local');
define('TEXT_BANNERS_IMAGE_TARGET', 'Destino de la Imagen (Grabar en):');
define('TEXT_BANNERS_HTML_TEXT', 'Texto HTML:');
define('TEXT_BANNERS_EXPIRES_ON', 'Caduca el:');
define('TEXT_BANNERS_OR_AT', ', o tras');
define('TEXT_BANNERS_IMPRESSIONS', 'vistas.');
define('TEXT_BANNERS_SCHEDULED_AT', 'Programado el:');
define('TEXT_BANNERS_BANNER_NOTE', '<b>Notas sobre el Banner:</b><ul><li>Use una imagen o texto HTML para el banner - no ambos.</li><li>Texto HTML tiene prioridad sobre una imagen</li></ul>');
define('TEXT_BANNERS_INSERT_NOTE', '<b>Notas sobre la Imagen:</b><ul><li>El directorio donde suba la imagen debe de tener configurados los permisos de escritura necesarios!</li><li>No rellene el campo \'Grabar en\' si no va a subir una imagen al servidor (por ejemplo, cuando use una imagen ya existente en el servidor -fichero local).</li><li>El campo \'Grabar en\' debe de ser un directorio que exista y terminado en una barra (por ejemplo: banners/).</li></ul>');
define('TEXT_BANNERS_EXPIRCY_NOTE', '<b>Notas sobre la Caducidad:</b><ul><li>Solo se debe de rellenar uno de los dos campos</li><li>Si el banner no debe de caducar no rellene ninguno de los campos</li></ul>');
define('TEXT_BANNERS_SCHEDULE_NOTE', '<b>Notas sobre la Programaci&oacute;n:</b><ul><li>Si se configura una fecha de programaci&oacute;n el banner se activara en esa fecha.</li><li>Todos los banners programados se marcan como inactivos hasta que llegue su fecha, cuando se marcan activos.</li></ul>');

define('TEXT_BANNERS_DATE_ADDED', 'A&ntilde;adido el:');
define('TEXT_BANNERS_SCHEDULED_AT_DATE', 'Programado el: <b>%s</b>');
define('TEXT_BANNERS_EXPIRES_AT_DATE', 'Caduca el: <b>%s</b>');
define('TEXT_BANNERS_EXPIRES_AT_IMPRESSIONS', 'Caduca tras: <b>%s</b> vistas');
define('TEXT_BANNERS_STATUS_CHANGE', 'Cambio Estado: %s');

define('TEXT_BANNERS_DATA', 'D<br>A<br>T<br>O<br>S');
define('TEXT_BANNERS_LAST_3_DAYS', 'Ultimos 3 dias');
define('TEXT_BANNERS_BANNER_VIEWS', 'Vistas');
define('TEXT_BANNERS_BANNER_CLICKS', 'Clicks');

define('TEXT_INFO_DELETE_INTRO', 'Seguro que quiere eliminar este banner?');
define('TEXT_INFO_DELETE_IMAGE', 'Borrar imagen');

define('SUCCESS_BANNER_INSERTED', 'Exito: Se ha a&ntilde;adido el banner.');
define('SUCCESS_BANNER_UPDATED', 'Exito: Se ha actualizado el banner.');
define('SUCCESS_BANNER_REMOVED', 'Exito: Se ha eliminado el banner.');
define('SUCCESS_BANNER_STATUS_UPDATED', 'Exito: El estado del banner se ha actualizado.');

define('ERROR_BANNER_TITLE_REQUIRED', 'Error: Es necesario el t&iacute;tulo del banner.');
define('ERROR_BANNER_GROUP_REQUIRED', 'Error: Es necesario el grupo del banner.');
define('ERROR_IMAGE_DIRECTORY_DOES_NOT_EXIST', 'Error: No existe el directorio destino: %s');
define('ERROR_IMAGE_DIRECTORY_NOT_WRITEABLE', 'Error: No se puede escribir en el directorio destino: %s');
define('ERROR_IMAGE_DOES_NOT_EXIST', 'Error: No existe imagen.');
define('ERROR_IMAGE_IS_NOT_WRITEABLE', 'Error: No se puede eliminar la imagen.');
define('ERROR_UNKNOWN_STATUS_FLAG', 'Error: Estado desconocido.');

define('ERROR_GRAPHS_DIRECTORY_DOES_NOT_EXIST', 'Error: No existe el directorio de gr&aacute;ficos. Por favor cree un directorio llamado \'graphs\' dentro de \'images\'.');
define('ERROR_GRAPHS_DIRECTORY_NOT_WRITEABLE', 'Error: No se puede escribir en el directorio de gr&aacute;ficos.');
?>
