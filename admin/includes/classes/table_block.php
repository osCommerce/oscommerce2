<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class tableBlock {
    var $table_border = '0';
    var $table_width = '100%';
    var $table_cellspacing = '0';
    var $table_cellpadding = '2';
    var $table_parameters = '';
    var $table_row_parameters = '';
    var $table_data_parameters = '';

    function tableBlock($contents) {
      $tableBox_string = '';

      $form_set = false;
      if (isset($contents['form'])) {
        $tableBox_string .= $contents['form'] . "\n";
        $form_set = true;
        array_shift($contents);
      }

      $tableBox_string .= '<table border="' . $this->table_border . '" width="' . $this->table_width . '" cellspacing="' . $this->table_cellspacing . '" cellpadding="' . $this->table_cellpadding . '"';
      if (osc_not_null($this->table_parameters)) $tableBox_string .= ' ' . $this->table_parameters;
      $tableBox_string .= '>' . "\n";

      for ($i=0, $n=sizeof($contents); $i<$n; $i++) {
        $tableBox_string .= '  <tr';
        if (osc_not_null($this->table_row_parameters)) $tableBox_string .= ' ' . $this->table_row_parameters;
        if (isset($contents[$i]['params']) && osc_not_null($contents[$i]['params'])) $tableBox_string .= ' ' . $contents[$i]['params'];
        $tableBox_string .= '>' . "\n";

        if (isset($contents[$i][0]) && is_array($contents[$i][0])) {
          for ($x=0, $y=sizeof($contents[$i]); $x<$y; $x++) {
            if (isset($contents[$i][$x]['text']) && osc_not_null($contents[$i][$x]['text'])) {
              $tableBox_string .= '    <td';
              if (isset($contents[$i][$x]['align']) && osc_not_null($contents[$i][$x]['align'])) $tableBox_string .= ' align="' . $contents[$i][$x]['align'] . '"';
              if (isset($contents[$i][$x]['params']) && osc_not_null($contents[$i][$x]['params'])) {
                $tableBox_string .= ' ' . $contents[$i][$x]['params'];
              } elseif (osc_not_null($this->table_data_parameters)) {
                $tableBox_string .= ' ' . $this->table_data_parameters;
              }
              $tableBox_string .= '>';
              if (isset($contents[$i][$x]['form']) && osc_not_null($contents[$i][$x]['form'])) $tableBox_string .= $contents[$i][$x]['form'];
              $tableBox_string .= $contents[$i][$x]['text'];
              if (isset($contents[$i][$x]['form']) && osc_not_null($contents[$i][$x]['form'])) $tableBox_string .= '</form>';
              $tableBox_string .= '</td>' . "\n";
            }
          }
        } else {
          $tableBox_string .= '    <td';
          if (isset($contents[$i]['align']) && osc_not_null($contents[$i]['align'])) $tableBox_string .= ' align="' . $contents[$i]['align'] . '"';
          if (isset($contents[$i]['params']) && osc_not_null($contents[$i]['params'])) {
            $tableBox_string .= ' ' . $contents[$i]['params'];
          } elseif (osc_not_null($this->table_data_parameters)) {
            $tableBox_string .= ' ' . $this->table_data_parameters;
          }
          $tableBox_string .= '>' . $contents[$i]['text'] . '</td>' . "\n";
        }

        $tableBox_string .= '  </tr>' . "\n";
      }

      $tableBox_string .= '</table>' . "\n";

      if ($form_set == true) $tableBox_string .= '</form>' . "\n";

      return $tableBox_string;
    }
  }
  
  
// message stack output
  class alertBlock {
    var $alert_parameters = '';
    
	// class constructor
    function alertBlock($contents, $alert_output = false) {
		  
      for ($i=0, $n=sizeof($contents); $i<$n; $i++) {
        $alertBox_string .= '  <div class="span8 offset2">' . "\n";
        $alertBox_string .= '  <div';
		  
        if (isset($contents[$i]['params']) && tep_not_null($contents[$i]['params']))
		  $alertBox_string .= ' ' . $contents[$i]['params'];
        
		$alertBox_string .= '>' . "\n";
        $alertBox_string .= '	<button type="button" class="close" data-dismiss="alert">&times;</button>' . "\n";
        $alertBox_string .= $contents[$i]['text'];
    
        $alertBox_string .= '  </div>' . "\n";
		$alertBox_string .= '  </div>' . "\n";
      }

      if ($alert_output == true) echo $alertBox_string;
        return $alertBox_string;
    }
  }    
?>
