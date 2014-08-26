<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $www_location = 'http://' . $_SERVER['HTTP_HOST'];

  if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
    $www_location .= $_SERVER['REQUEST_URI'];
  } else {
    $www_location .= $_SERVER['SCRIPT_FILENAME'];
  }

  $www_location = substr($www_location, 0, strpos($www_location, 'install'));

  $dir_fs_www_root = osc_realpath(dirname(__FILE__) . '/../../../') . '/';
?>


<div class="row">
  <div class="col-sm-9">
    <div class="alert alert-info">
      <h1>New Installation</h1>

      <p>This web-based installation routine will correctly setup and configure osCommerce Online Merchant to run on this server.</p>
      <p>Please follow the on-screen instructions that will take you through the database server, web server, and store configuration options. If help is needed at any stage, please consult the documentation or seek help at the community support forums.</p>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="panel panel-default">
      <div class="panel-body">
        <ol>
          <li class="text-muted">Database Server</li>
          <li class="text-success"><strong>Web Server</strong></li>
          <li class="text-muted">Online Store Settings</li>
          <li class="text-muted">Finished!</li>
        </ol>
      </div>
    </div>
    <div class="progress">
      <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100" style="width: 50%">50%</div>
    </div>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
  <div class="col-xs-12 col-sm-push-3 col-sm-9">

    <div class="page-header">
      <p class="inputRequirement pull-right text-right"><span class="glyphicon glyphicon-asterisk inputRequirement"></span> Required information</p>
      <h2>Web Server</h2>
    </div>

    <form name="install" id="installForm" action="install.php?step=3" method="post" class="form-horizontal" role="form">

      <div class="form-group has-feedback">
        <label for="wwwAddress" class="control-label col-xs-3">WWW Address</label>
        <div class="col-xs-9">
          <?php echo osc_draw_input_field('HTTP_WWW_ADDRESS', $www_location, 'required aria-required="true" id="wwwAddress" placeholder="http://"'); ?>
          <span class="glyphicon glyphicon-asterisk form-control-feedback inputRequirement"></span>
          <span class="help-block">The web address to the online store.</span>
        </div>
      </div>
    
      <div class="form-group has-feedback">
        <label for="webRoot" class="control-label col-xs-3">Webserver Root Directory</label>
        <div class="col-xs-9">
          <?php echo osc_draw_input_field('DIR_FS_DOCUMENT_ROOT', $dir_fs_www_root, 'required aria-required="true" id="webRoot"'); ?>
          <span class="glyphicon glyphicon-asterisk form-control-feedback inputRequirement"></span>
          <span class="help-block">The directory where the online store is installed on the server.</span>
        </div>
      </div>

      <p><?php echo osc_draw_button('Continue To Step 3', 'triangle-1-e', null, 'primary', null, 'btn-success btn-block'); ?></p>

      <?php
      foreach ( $_POST as $key => $value ) {
        if (($key != 'x') && ($key != 'y')) {
          echo osc_draw_hidden_field($key, $value);
        }
      }
      ?>

    </form>

  </div>
  <div class="col-xs-12 col-sm-pull-9 col-sm-3">
    <div class="panel panel-success">
      <div class="panel-heading">
        Step 2: Web Server
      </div>
      <div class="panel-body">
        <p>The web server takes care of serving the pages of your online store to your guests and customers. The web server parameters make sure the links to the pages point to the correct location.</p>
      </div>
    </div>
  </div>
  
</div>
