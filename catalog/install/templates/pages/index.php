<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  $compat_register_globals = true;

?>

<div class="alert alert-info">
  <h1>Welcome to osCommerce Online Merchant v<?php echo osc_get_version(); ?>!</h1>

  <p>osCommerce Online Merchant helps you sell products worldwide with your own online store. Its Administration Tool manages products, customers, orders, newsletters, specials, and more to successfully build the success of your online business.</p>
  <p>osCommerce has attracted a large community of store owners and developers who support each other and have provided over 7,000 free add-ons that can extend the features and potential of your online store.</p>
</div>

<div class="row">
  <div class="col-xs-12 col-sm-push-3 col-sm-9">
    <div class="page-header">
      <h2>New Installation</h2>
    </div>

<?php
    $configfile_array = array();

    if (file_exists(osc_realpath(dirname(__FILE__) . '/../../../includes') . '/configure.php') && !osc_is_writable(osc_realpath(dirname(__FILE__) . '/../../../includes') . '/configure.php')) {
      @chmod(osc_realpath(dirname(__FILE__) . '/../../../includes') . '/configure.php', 0777);
    }

    if (file_exists(osc_realpath(dirname(__FILE__) . '/../../../admin/includes') . '/configure.php') && !osc_is_writable(osc_realpath(dirname(__FILE__) . '/../../../admin/includes') . '/configure.php')) {
      @chmod(osc_realpath(dirname(__FILE__) . '/../../../admin/includes') . '/configure.php', 0777);
    }

    if (file_exists(osc_realpath(dirname(__FILE__) . '/../../../includes') . '/configure.php') && !osc_is_writable(osc_realpath(dirname(__FILE__) . '/../../../includes') . '/configure.php')) {
      $configfile_array[] = osc_realpath(dirname(__FILE__) . '/../../../includes') . '/configure.php';
    }

    if (file_exists(osc_realpath(dirname(__FILE__) . '/../../../admin/includes') . '/configure.php') && !osc_is_writable(osc_realpath(dirname(__FILE__) . '/../../../admin/includes') . '/configure.php')) {
      $configfile_array[] = osc_realpath(dirname(__FILE__) . '/../../../admin/includes') . '/configure.php';
    }

    $warning_array = array();

    if (function_exists('ini_get')) {
      if ($compat_register_globals == false) {
        $warning_array['register_globals'] = 'Compatibility with register_globals is supported from PHP 4.3+. This setting <u>must be enabled</u> due to an older PHP version being used.';
      }
    }

    if (!extension_loaded('mysql')) {
      $warning_array['mysql'] = 'The MySQL extension is required but is not installed. Please enable it to continue installation.';
    }

    if ((sizeof($configfile_array) > 0) || (sizeof($warning_array) > 0)) {
?>

      <div class="noticeBox">

<?php
      if (sizeof($warning_array) > 0) {
?>

        <table class="table table-condensed table-striped">

<?php
        foreach ( $warning_array as $key => $value ) {
          echo '        <tr>' . "\n" .
               '          <td valign="top"><strong>' . $key . '</strong></td>' . "\n" .
               '          <td valign="top">' . $value . '</td>' . "\n" .
               '        </tr>' . "\n";
        }
?>

        </table>
<?php
      }

      if (sizeof($configfile_array) > 0) {
?>

        <div class="alert alert-danger">
          <p>The webserver is not able to save the installation parameters to its configuration files.</p>
          <p>The following files need to have their file permissions set to world-writeable (chmod 777):</p>
          <p>

<?php
          for ($i=0, $n=sizeof($configfile_array); $i<$n; $i++) {
            echo $configfile_array[$i];

            if (isset($configfile_array[$i+1])) {
              echo '<br />';
            }
          }
?>

          </p>
        </div>

<?php
      }
?>

      </div>

<?php
    }

    if ((sizeof($configfile_array) > 0) || (sizeof($warning_array) > 0)) {
?>

      <div class="alert alert-danger">Please correct the above errors and retry the installation procedure with the changes in place.</div>

<?php
      if (sizeof($warning_array) > 0) {
        echo '    <div class="alert alert-info"><i>Changing webserver configuration parameters may require the webserver service to be restarted before the changes take affect.</i></div>' . "\n";
      }
?>

      <p><a href="index.php" class="btn btn-danger btn-block" role="button">Retry</a></p>

<?php
    } else {
?>

      <div class="alert alert-success">The webserver environment has been verified to proceed with a successful installation and configuration of your online store.</div>

      <div id="jsOn" style="display: none;">
        <p><a href="install.php" class="btn btn-success btn-block" role="button">Start the installation procedure</a></p>
      </div>

      <div id="jsOff">
        <p class="text-danger">Please enable Javascript in your browser to be able to start the installation procedure.</p>
        <p><a href="index.php" class="btn btn-danger btn-block" role="button">Retry</a></p>
      </div>

<script>
$(function() {
  $('#jsOff').hide();
  $('#jsOn').show();
});
</script>

<?php
  }
?>
  </div>
  <div class="col-xs-12 col-sm-pull-9 col-sm-3">
    <div class="panel panel-success">
      <div class="panel-heading">
        Server Capabilities
      </div>
        <table class="table table-condensed table-striped">
          <tr>
            <th colspan="2">PHP Version</th>
          </tr>
          <tr>
            <th><?php echo PHP_VERSION; ?></th>
            <td align="right" width="25"><?php echo ((PHP_VERSION >= 5.3) ? '<i class="fa fa-thumbs-up text-success"></i>' : '<i class="fa fa-thumbs-down text-danger"></i>'); ?></td>
          </tr>
        </table>

<?php
  if (function_exists('ini_get')) {
?>

        <br />

        <table class="table table-condensed table-striped">
          <tr>
            <th colspan="3">PHP Settings</th>
          </tr>
          <tr>
            <th>register_globals</th>
            <td class="text-right"><?php echo (((int)ini_get('register_globals') == 0) ? 'Off' : 'On'); ?></td>
            <td class="text-right"><?php echo (($compat_register_globals == true) ? '<i class="fa fa-thumbs-up text-success"></i>' : '<i class="fa fa-thumbs-down text-danger"></i>'); ?></td>
          </tr>
          <tr>
            <th>magic_quotes</th>
            <td class="text-right"><?php echo (((int)ini_get('magic_quotes') == 0) ? 'Off' : 'On'); ?></td>
            <td class="text-right"><?php echo (((int)ini_get('magic_quotes') == 0) ? '<i class="fa fa-thumbs-up text-success"></i>' : '<i class="fa fa-thumbs-down text-danger"></i>'); ?></td>
          </tr>
          <tr>
            <th>file_uploads</th>
            <td class="text-right"><?php echo (((int)ini_get('file_uploads') == 0) ? 'Off' : 'On'); ?></td>
            <td class="text-right"><?php echo (((int)ini_get('file_uploads') == 1) ? '<i class="fa fa-thumbs-up text-success"></i>' : '<i class="fa fa-thumbs-down text-danger"></i>'); ?></td>
          </tr>
          <tr>
            <th>session.auto_start</th>
            <td class="text-right"><?php echo (((int)ini_get('session.auto_start') == 0) ? 'Off' : 'On'); ?></td>
            <td class="text-right"><?php echo (((int)ini_get('session.auto_start') == 0) ? '<i class="fa fa-thumbs-up text-success"></i>' : '<i class="fa fa-thumbs-down text-danger"></i>'); ?></td>
          </tr>
          <tr>
            <th>session.use_trans_sid</th>
            <td class="text-right"><?php echo (((int)ini_get('session.use_trans_sid') == 0) ? 'Off' : 'On'); ?></td>
            <td class="text-right"><?php echo (((int)ini_get('session.use_trans_sid') == 0) ? '<i class="fa fa-thumbs-up text-success"></i>' : '<i class="fa fa-thumbs-down text-danger"></i>'); ?></td>
          </tr>
        </table>

        <br />

        <table class="table table-condensed table-striped">
          <tr>
            <th colspan="2">Required PHP Extensions</th>
          </tr>
          <tr>
            <th>MySQL</th>
            <td class="text-right"><?php echo (extension_loaded('mysql') || extension_loaded('mysqli') ? '<i class="fa fa-thumbs-up text-success"></i>' : '<i class="fa fa-thumbs-down text-danger"></i>'); ?></td>
          </tr>
        </table>

        <br />

        <table class="table table-condensed table-striped">
          <tr>
            <th colspan="2">Recommended PHP Extensions</th>
          </tr>
          <tr>
            <th>GD</th>
            <td class="text-right"><?php echo (extension_loaded('gd') ? '<i class="fa fa-thumbs-up text-success"></i>' : '<i class="fa fa-thumbs-down text-danger"></i>'); ?></td>
          </tr>
          <tr>
            <th>cURL</th>
            <td class="text-right"><?php echo (extension_loaded('curl') ? '<i class="fa fa-thumbs-up text-success"></i>' : '<i class="fa fa-thumbs-down text-danger"></i>'); ?></td>
          </tr>
          <tr>
            <th>OpenSSL</th>
            <td class="text-right"><?php echo (extension_loaded('openssl') ? '<i class="fa fa-thumbs-up text-success"></i>' : '<i class="fa fa-thumbs-down text-danger"></i>'); ?></td>
          </tr>
        </table>

<?php
  }
?>
    </div>
  </div>
</div>
