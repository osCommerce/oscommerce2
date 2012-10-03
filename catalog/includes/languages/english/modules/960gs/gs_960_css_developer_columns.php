<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

  define( 'MODULE_960GS_CSS_DEVELOPER_COLUMNS_TITLE', '960 Grid System CSS Developer' );
  define( 'MODULE_960GS_CSS_DEVELOPER_COLUMNS_DESCRIPTION', '<p>Add 960gs div developer columns with css hack to all pages. This module can be usefull when javaScripts are disabled in browsers.</p><p><strong>Installation Steps</strong><ol><li>Install this module</li><li>Find <strong>id="bodyWrapper"</strong> code in template_top.php<br /><br />and replace with<br /><br /><strong>id="bodyWrapper&lt;?php echo $oscTemplate->getBlocks("960grid_css_developer"); ?&gt;"</strong></li></ol>and run.</p>' );
?>
