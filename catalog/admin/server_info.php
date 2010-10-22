<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  switch ($action) {
    case 'export':
      $info = tep_get_system_information();
    break;

    case 'submit':
      $target_host = 'usage.oscommerce.com';
      $target_path = '/submit.php';

      $encoded = base64_encode(serialize(tep_get_system_information()));

      $response = false;

      if (function_exists('curl_init')) {
        $data = array('info' => $encoded);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://' . $target_host . $target_path);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = trim(curl_exec($ch));
        curl_close($ch);
      } else {
        if ($fp = @fsockopen($target_host, 80, $errno, $errstr, 30)) {
          $data = 'info=' . $encoded;

          fputs($fp, "POST " . $target_path . " HTTP/1.1\r\n");
          fputs($fp, "Host: " . $target_host . "\r\n");
          fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
          fputs($fp, "Content-length: " . strlen($data) . "\r\n");
          fputs($fp, "Connection: close\r\n\r\n");
          fputs($fp, $data."\r\n\r\n");

          $response = '';

          while (!feof($fp)) {
            $response .= fgets($fp, 4096);
          }

          fclose($fp);

          $response = trim(substr($response, strrpos($response, "\r\n\r\n")));
        }
      }

      if ($response != 'OK') {
        $messageStack->add_session(ERROR_INFO_SUBMIT, 'error');
      } else {
        $messageStack->add_session(SUCCESS_INFO_SUBMIT, 'success');
      }

      tep_redirect(tep_href_link(FILENAME_SERVER_INFO));
    break;

    case 'save':
      $info = tep_get_system_information();
      $info_file = 'server_info-' . date('YmdHis') . '.txt';
      header('Content-type: text/plain');
      header('Content-disposition: attachment; filename=' . $info_file);
      echo tep_format_system_info_array($info);
      exit;

    break;

    default:
      $info = tep_get_system_information();
      break;
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
<?php
  if ($action == 'export') {
?>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="smallText" colspan="2"><?php echo TEXT_EXPORT_INTRO; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_textarea_field('server configuration', 'soft', '100', '15', tep_format_system_info_array($info)); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
          <td align="right" class="smallText"><?php echo tep_draw_button(IMAGE_SEND, 'arrowreturnthick-1-n', tep_href_link(FILENAME_SERVER_INFO, 'action=submit'), 'primary') . tep_draw_button(IMAGE_SAVE, 'disk', tep_href_link(FILENAME_SERVER_INFO, 'action=save'), 'primary');?>
      </tr>
  <?php
  } else {
    $server = parse_url(HTTP_SERVER);
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td><table border="0" cellspacing="0" cellpadding="3">
              <tr>
                <td class="smallText"><strong><?php echo TITLE_SERVER_HOST; ?></strong></td>
                <td class="smallText"><?php echo $server['host'] . ' (' . gethostbyname($server['host']) . ')'; ?></td>
                <td class="smallText">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo TITLE_DATABASE_HOST; ?></strong></td>
                <td class="smallText"><?php echo DB_SERVER . ' (' . gethostbyname(DB_SERVER) . ')'; ?></td>
              </tr>
              <tr>
                <td class="smallText"><strong><?php echo TITLE_SERVER_OS; ?></strong></td>
                <td class="smallText"><?php echo $info['system']['os'] . ' ' . $info['system']['kernel']; ?></td>
                <td class="smallText">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo TITLE_DATABASE; ?></strong></td>
                <td class="smallText"><?php echo 'MySQL ' . $info['mysql']['version']; ?></td>
              </tr>
              <tr>
                <td class="smallText"><strong><?php echo TITLE_SERVER_DATE; ?></strong></td>
                <td class="smallText"><?php echo $info['system']['date']; ?></td>
                <td class="smallText">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo TITLE_DATABASE_DATE; ?></strong></td>
                <td class="smallText"><?php echo $info['mysql']['date']; ?></td>
              </tr>
              <tr>
                <td class="smallText"><strong><?php echo TITLE_SERVER_UP_TIME; ?></strong></td>
                <td colspan="3" class="smallText"><?php echo $info['system']['uptime']; ?></td>
              </tr>
              <tr>
                <td colspan="4"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><strong><?php echo TITLE_HTTP_SERVER; ?></strong></td>
                <td colspan="3" class="smallText"><?php echo $info['system']['http_server']; ?></td>
              </tr>
              <tr>
                <td class="smallText"><strong><?php echo TITLE_PHP_VERSION; ?></strong></td>
                <td colspan="3" class="smallText"><?php echo $info['php']['version'] . ' (' . TITLE_ZEND_VERSION . ' ' . $info['php']['zend'] . ')'; ?></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="smallText"><?php echo tep_draw_button(IMAGE_EXPORT, 'triangle-1-nw', tep_href_link(FILENAME_SERVER_INFO, 'action=export'));?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td>
<?php
  if (function_exists('ob_start')) {
?>
<style type="text/css">
body, td, th {font-family: sans-serif; font-size: 10px;}
.p {text-align: left;}
.e {background-color: #ccccff; font-weight: bold;}
.h {background-color: #9999cc; font-weight: bold;}
.v {background-color: #cccccc;}
i {color: #666666;}
hr {display: none;}
</style>
<?php
    ob_start();
    phpinfo();
    $phpinfo = ob_get_contents();
    ob_end_clean();

    $phpinfo = str_replace('border: 1px', '', $phpinfo);
    preg_match('/<body>(.*)<\/body>/is', $phpinfo, $regs);
    echo '<table border="1" cellpadding="3" width="600" style="border: 0px; border-color: #000000;">' .
         '  <tr><td><a href="http://www.oscommerce.com"><img border="0" src="images/oscommerce.png" title="osCommerce Online Merchant v' . tep_get_version() . '" /></a><h1 class="p">osCommerce Online Merchant v' . tep_get_version() . '</h1></td>' .
         '  </tr>' .
         '</table>';
    echo $regs[1];
  } else {
    phpinfo();
  }
?>
        </td>
      </tr>
<?php
  }
 ?>
    </table>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
