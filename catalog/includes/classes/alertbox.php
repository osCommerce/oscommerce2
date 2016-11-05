<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  class alertBlock {
    function __construct($contents, $alert_output = false) {
	  $alertBox_string = '';

      for ($i=0, $n=sizeof($contents); $i<$n; $i++) {
        $alertBox_string .= '  <div';

        if (isset($contents[$i]['params']) && tep_not_null($contents[$i]['params']))
		  $alertBox_string .= ' ' . $contents[$i]['params'];

		  $alertBox_string .= '>' . "\n";
          $alertBox_string .= '	<button type="button" class="close" data-dismiss="alert">&times;</button>' . "\n";
          $alertBox_string .= $contents[$i]['text'];

          $alertBox_string .= '  </div>' . "\n";
      }

      if ($alert_output == true) echo $alertBox_string;
        return $alertBox_string;
     }
  }
?>
