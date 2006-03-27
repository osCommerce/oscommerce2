<?php
/*
  $Id: backup.php,v 1.22 2003/07/06 20:33:01 dgw_ Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_TITLE', 'Copia de Seguridad de la Base de Datos');

define('TABLE_HEADING_TITLE', 'T&iacute;tulo');
define('TABLE_HEADING_FILE_DATE', 'Fecha');
define('TABLE_HEADING_FILE_SIZE', 'Tama&ntilde;o');
define('TABLE_HEADING_ACTION', 'Acci&oacute;n');

define('TEXT_INFO_HEADING_NEW_BACKUP', 'Nueva Copia de Seguridad');
define('TEXT_INFO_HEADING_RESTORE_LOCAL', 'Restaurar Localmente');
define('TEXT_INFO_NEW_BACKUP', 'No interrumpa el proceso de copia, que puede durar unos minutos.');
define('TEXT_INFO_UNPACK', '<br><br>(despues de descomprimir el archivo)');
define('TEXT_INFO_RESTORE', 'No interrumpa el proceso de restauraci&oacute;n.<br><br>Cuanto mas grande sea la copia de seguridad, mas tardar&aacute; este proceso!<br><br>Si es posible, use el cliente de mysql.<br><br>Por ejemplo:<br><br><b>mysql -h' . DB_SERVER . ' -u' . DB_SERVER_USERNAME . ' -p ' . DB_DATABASE . ' < %s </b> %s');
define('TEXT_INFO_RESTORE_LOCAL', 'No interrumpa el proceso de restauraci&oacute;n.<br><br>Cuanto mas grande sea la copia de seguridad, mas tardar&aacute; este proceso!');
define('TEXT_INFO_RESTORE_LOCAL_RAW_FILE', 'El fichero subido debe ser de texto.');
define('TEXT_INFO_DATE', 'Fecha:');
define('TEXT_INFO_SIZE', 'Tama&ntilde;o:');
define('TEXT_INFO_COMPRESSION', 'Compresi&oacute;n:');
define('TEXT_INFO_USE_GZIP', 'Usar GZIP');
define('TEXT_INFO_USE_ZIP', 'Usar ZIP');
define('TEXT_INFO_USE_NO_COMPRESSION', 'Sin Compresi&oacute;n (directamente SQL)');
define('TEXT_INFO_DOWNLOAD_ONLY', 'Bajar solo (no guardar en el servidor)');
define('TEXT_INFO_BEST_THROUGH_HTTPS', 'Preferiblemente con una conexi&oacute;n segura');
define('TEXT_NO_EXTENSION', 'Ninguna');
define('TEXT_BACKUP_DIRECTORY', 'Directorio para Copias de Seguridad:');
define('TEXT_LAST_RESTORATION', 'Ultima Restauraci&oacute;n:');
define('TEXT_FORGET', '(<u>olvidar</u>)');
define('TEXT_DELETE_INTRO', 'Seguro que quiere eliminar esta copia?');

define('ERROR_BACKUP_DIRECTORY_DOES_NOT_EXIST', 'Error: No existe el directorio de copias de seguridad.');
define('ERROR_BACKUP_DIRECTORY_NOT_WRITEABLE', 'Error: No hay permiso de escritura en el directorio de copias de seguridad.');
define('ERROR_DOWNLOAD_LINK_NOT_ACCEPTABLE', 'Error: No se aceptan enlaces.');

define('SUCCESS_LAST_RESTORE_CLEARED', 'Exito: La fecha de ultima restauraci&oacute;n ha sido borrada.');
define('SUCCESS_DATABASE_SAVED', 'Exito: Se ha guardado la base de datos.');
define('SUCCESS_DATABASE_RESTORED', 'Exito: Se ha restaurado la base de datos.');
define('SUCCESS_BACKUP_DELETED', 'Exito: Se ha eliminado la copia de seguridad.');
?>
