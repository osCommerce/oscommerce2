<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/
  class alertBlock {    
    function alertBlock($contents, $alert_output = false) {
	  $alertBox_string = '';
		  
      for ($i=0, $n=sizeof($contents); $i<$n; $i++) {
        $alertBox_string .= '  <div';
		  
        if (isset($contents[$i]['params']) && tep_not_null($contents[$i]['params'])) $alertBox_string .= ' ' . $contents[$i]['params'];
        
		    $alertBox_string .= '>' . "\n";
        $alertBox_string .= '	<button type="button" class="close" data-dismiss="alert">&times;</button>' . "\n";
        $alertBox_string .= $contents[$i]['text'];
    
        $alertBox_string .= '  </div>' . "\n";
      }

      if ($alert_output == true) echo $alertBox_string;
        return $alertBox_string;
     }
  }

