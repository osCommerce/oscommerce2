<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Cache;
  use OSC\OM\DateTime;
  use OSC\OM\FileSystem;
  use OSC\OM\HTML;
  use OSC\OM\HTTP;
  use OSC\OM\OnlineUpdate;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  require('includes/application_top.php');

  $current_version = OSCOM::getVersion();

  preg_match('/^(\d+\.)?(\d+\.)?(\d+)$/', $current_version, $version);

  $major_version = (int)$version[1];
  $minor_version = (int)$version[2];
  $inc_version = (int)$version[3];

  $VersionCache = new Cache('core_version_check');

  if ($VersionCache->exists(360)) {
    $releases = $VersionCache->get();
  } else {
    $releases = HTTP::getResponse([
      'url' => 'https://www.oscommerce.com/version/online_merchant/' . $major_version . $minor_version
    ]);

    if (!empty($releases)) {
      $releases = explode("\n", trim($releases));

      if (preg_match('/^(\d+\.)?(\d+\.)?(\d+)\|[0-9]{8}$/', $releases[0]) === 1) {
        usort($releases, function($a, $b) {
          $aa = explode('|', $a);
          $ba = explode('|', $b);

          return version_compare($aa[0], $ba[0], '>');
        });

        $VersionCache->save($releases);
      } else {
        $releases = -1;
      }
    }
  }

  $versions = [];

  if (is_array($releases) && !empty($releases)) {
    foreach ($releases as $version) {
      $version_array = explode('|', $version);

      if (version_compare($current_version, $version_array[0], '<')) {
        $versions[] = [
          'version' => $version_array[0],
          'date' => DateTime::toLong(substr($version_array[1], 0, 4) . '-' . substr($version_array[1], 4, 2) . '-' . substr($version_array[1], 6, 2))
        ];
      }
    }
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'getUpdateLog':
        $check = false;

        if (isset($_POST['version']) && preg_match('/^(\d+\.)?(\d+\.)?(\d+)$/', $_POST['version'])) {
          foreach ($versions as $v) {
            if ($v['version'] == $_POST['version']) {
              $check = true;

              break;
            }
          }
        }

        if ($check !== true) {
          trigger_error('Online Update: Retrievel of update log for requested v' . $_POST['version'] . ' is not valid.');

          http_response_code(404);
          exit;
        }

        $result = [
          'result' => -1
        ];

        if (OnlineUpdate::logExists($_POST['version'])) {
          $result['result'] = 1;
          $result['log'] = OnlineUpdate::getLog($_POST['version']);
          $result['path'] = OnlineUpdate::getLogPath($_POST['version']);
        }

        echo json_encode($result);

        exit;
        break;

      case 'getReleaseNotes':
        $check = false;

        if (isset($_POST['version']) && preg_match('/^(\d+\.)?(\d+\.)?(\d+)$/', $_POST['version'])) {
          foreach ($versions as $v) {
            if ($v['version'] == $_POST['version']) {
              $check = true;

              break;
            }
          }
        }

        if ($check !== true) {
          trigger_error('Online Update: Retrievel of Release Notes for requested v' . $_POST['version'] . ' is not valid.');

          http_response_code(404);
          exit;
        }

        $version = str_replace('.', '_', $_POST['version']);

        $ReleaseNotesCache = new Cache('online_update-rel_notes-' . $version);

        if ($ReleaseNotesCache->exists()) {
          $notes = $ReleaseNotesCache->get();
        } else {
          $notes = HTTP::getResponse([
            'url' => 'https://www.oscommerce.com/version/online_merchant/notes/' . $_POST['version'] . '.txt'
          ]);

          $notes = trim($notes);

          if (!empty($notes)) {
            $ReleaseNotesCache->save($notes);
          }
        }

        echo $notes;

        exit;
        break;

      case 'downloadRelease':
        $check = false;

        if (isset($_POST['version']) && preg_match('/^(\d+\.)?(\d+\.)?(\d+)$/', $_POST['version'])) {
          foreach ($versions as $v) {
            if ($v['version'] == $_POST['version']) {
              $check = true;

              break;
            }
          }
        }

        if ($check !== true) {
          trigger_error('Online Update: Download for requested v' . $_POST['version'] . ' update package is not valid.');

          http_response_code(404);
          exit;
        }

        $result = [
          'result' => -1
        ];

        if (FileSystem::isWritable(OSCOM::BASE_DIR . 'Work/OnlineUpdates', true)) {
          if (!is_dir(OSCOM::BASE_DIR . 'Work/OnlineUpdates')) {
            mkdir(OSCOM::BASE_DIR . 'Work/OnlineUpdates', 0777, true);
          }

          $filepath = OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $_POST['version'] . '-update.zip';

          if (FileSystem::isWritable($filepath)) {
            unlink($filepath);
          }

          $downloadFile = HTTP::getResponse([
            'url' => 'https://www.oscommerce.com/?Products&Download=oscom-' . $_POST['version'] . '-ou',
            'method' => 'post'
          ]);

          $save_result = file_put_contents($filepath, $downloadFile);

          if (($save_result !== false) && ($save_result > 0)) {
            $result['result'] = 1;
          } else {
            $result['result'] = -3;
            $result['path'] = FileSystem::displayPath($filepath);
          }
        } else {
          $result['result'] = -2;
          $result['path'] = FileSystem::displayPath(OSCOM::BASE_DIR . 'Work/OnlineUpdates');
        }

        echo json_encode($result);

        exit;
        break;

      case 'applyRelease':
        $check = false;

        if (isset($_POST['version']) && preg_match('/^(\d+\.)?(\d+\.)?(\d+)$/', $_POST['version'])) {
          foreach ($versions as $v) {
            if ($v['version'] == $_POST['version']) {
              $check = true;

              break;
            }
          }
        }

        if ($check !== true) {
          trigger_error('Online Update: Processing for requested v' . $_POST['version'] . ' update package is not valid.');

          http_response_code(404);
          exit;
        }

        $result = [
          'result' => -1
        ];

        // reset the log
        OnlineUpdate::resetLog($_POST['version']);

        OnlineUpdate::log('Starting update', $_POST['version']);

        try {
          if (!is_file(OSCOM::BASE_DIR . 'Work/Keys/oscommerce.pubkey')) {
            throw new \Exception('### ERROR ###' . "\n" . 'The following required public key cannot be found:' . "\n\n" . FileSystem::displayPath(OSCOM::BASE_DIR . 'Work/Keys/oscommerce.pubkey'));
          }

          if (!FileSystem::isWritable(OSCOM::BASE_DIR . 'version.txt')) {
            throw new \Exception('### ERROR ###' . "\n" . 'The following file cannot be written to - please check the file permissions: ' . "\n\n" . FileSystem::displayPath(OSCOM::BASE_DIR . 'version.txt'));
          }

          $update_zip = OSCOM::BASE_DIR . 'Work/OnlineUpdates/' . $_POST['version'] . '-update.zip';

          if (!is_file($update_zip)) {
            throw new \Exception('### ERROR ###' . "\n" . 'The following downloaded update package could not be found:' . "\n\n" . FileSystem::displayPath($update_zip));
          }

          $work_dir = OSCOM::BASE_DIR . 'Work/OnlineUpdates/update_contents';

          if (is_dir($work_dir)) {
            OnlineUpdate::log('Cleaning work directory', $_POST['version']);

            $errors = [];

            foreach (FileSystem::rmdir($work_dir) as $wd) {
              if ($wd['result'] !== true) {
                $errors[] = FileSystem::displayPath($wd['source']);
              }
            }

            if (!empty($errors)) {
              throw new \Exception('### ERROR ###' . "\n" . 'Could not clean the following files and directories from the work directory:' . "\n\n" . implode("\n", $errors));
            }
          }

          if (!mkdir($work_dir, 0777, true)) {
            throw new \Exception('### ERROR ###' . "\n" . 'Could not create the following work directory:' . "\n\n" . FileSystem::displayPath($work_dir));
          }

          if (!FileSystem::isWritable($work_dir)) {
            throw new \Exception('### ERROR ###' . "\n" . 'Could not write to the following work directory:' . "\n\n" . FileSystem::displayPath($work_dir));
          }

          OnlineUpdate::log('Extracting downloaded update package', $_POST['version']);

          try {
            $zip = new \ZipArchive();

            if ($zip->open($update_zip) === true) {
              $zip->extractTo($work_dir);
              $zip->close();
            }
          } catch (\Exception $e) {
            throw new \Exception('### ERROR ###' . "\n" . 'Could not extract the following downloaded update package:' . "\n\n" . FileSystem::displayPath($update_zip) . "\n\n" . 'to the following work directory:' . "\n\n" . FileSystem::displayPath($work_dir));
          }

          unset($zip);

          OnlineUpdate::log('Verifying downloaded update package', $_POST['version']);

          $update_pkg = $work_dir . '/' . $_POST['version'] . '.zip';

          if (!is_file($update_pkg) || !is_file($update_pkg . '.sig')) {
            throw new \Exception('### ERROR ###' . "\n" . 'The following downloaded update package does not seem to be a valid update package:' . "\n\n" . FileSystem::displayPath($update_zip));
          }

          $public = openssl_get_publickey(file_get_contents(OSCOM::BASE_DIR . 'Work/Keys/oscommerce.pubkey'));

          if (openssl_verify(sha1_file($update_pkg), file_get_contents($update_pkg . '.sig'), $public) !== 1) {
            throw new \Exception('### ERROR ###' . "\n" . 'Could not verify the following downloaded update package:' . "\n\n" . FileSystem::displayPath($update_zip));
          }

          if (!unlink($update_zip)) {
            throw new \Exception('### ERROR ###' . "\n" . 'Could not delete the following downloaded update package:' . "\n\n" . FileSystem::displayPath($update_zip));
          }

          mkdir($work_dir . '/' . $_POST['version'], 0777, true);

          OnlineUpdate::log('Extracting downloaded update package files', $_POST['version']);

          try {
            $zip = new \ZipArchive();

            if ($zip->open($update_pkg) === true) {
               $zip->extractTo($work_dir . '/' . $_POST['version']);
              $zip->close();
            }
          } catch (\Exception $e) {
            throw new \Exception('### ERROR ###' . "\n" . 'Could not extract the files of the following update package:' . "\n\n" . FileSystem::displayPath($update_pkg) . "\n\n" . 'to the following work directory:' . "\n\n" . FileSystem::displayPath($work_dir . '/' . $_POST['version']));
          }

          unset($zip);
          unlink($update_pkg);

          OnlineUpdate::log('Verifying update package meta file', $_POST['version']);

          $meta = [];

          if (!is_file($work_dir . '/' . $_POST['version'] . '/oscommerce.json')) {
            throw new \Exception('### ERROR ###' . "\n" . 'The oscommerce.json meta file could not be found in the following update package:' . "\n\n" . FileSystem::displayPath($update_pkg));
          }

          $meta = json_decode(file_get_contents($work_dir . '/' . $_POST['version'] . '/oscommerce.json'), true);

          if (!is_array($meta) || empty($meta)) {
            throw new \Exception('### ERROR ###' . "\n" . 'The oscommerce.json meta file in the following update package seems to be corrupt:' . "\n\n" . FileSystem::displayPath($update_pkg));
          }

          if (!isset($meta['version']) || ($meta['version'] != $_POST['version'])) {
            throw new \Exception('### ERROR ###' . "\n" . 'The update package version does not match the requested update version. Update Package version: ' . $meta['version'] . '; Requested version: ' . $_POST['version']);
          }

          if (!isset($meta['version_req']) || ($meta['version_req'] != $current_version)) {
            throw new \Exception('### ERROR ###' . "\n" . 'The update package version does not match the required current version of osCommerce Online Merchant. Current version: ' . $current_version . '; Update Package required version: ' . $meta['version_req']);
          }

          OnlineUpdate::log('Verifying file and directory permissions', $_POST['version']);

          $errors = [];

          $update_pkg_contents = FileSystem::getDirectoryContents($work_dir . '/' . $_POST['version']);

          foreach ($update_pkg_contents as $file) {
            $pathname = substr($file, strlen($work_dir . '/' . $_POST['version'] . '/'));

            $file_source = null;

            if (substr($pathname, 0, 8) == 'catalog/') {
              $file_source = OSCOM::getConfig('dir_root', 'Shop') . substr($pathname, 8);
            } elseif (substr($pathname, 0, 6) == 'admin/') {
              $file_source = OSCOM::getConfig('dir_root') . substr($pathname, 6);
            }

            if (isset($file_source)) {
              // check if target and target directory are writable
              if (!FileSystem::isWritable($file_source, true) || !FileSystem::isWritable(dirname($file_source), true)) {
                $errors[] = FileSystem::displayPath($file_source);
              }
            }
          }

          $to_del = [];

          if (is_file($work_dir . '/' . $_POST['version'] . '/delete.txt')) {
            $to_del = explode("\n", trim(file_get_contents($work_dir . '/' . $_POST['version'] . '/delete.txt')));

            foreach ($to_del as $d) {
              $file_source = null;

              if (substr($d, 0, 8) == 'catalog/') {
                $file_source = OSCOM::getConfig('dir_root', 'Shop') . substr($d, 8);
              } elseif (substr($d, 0, 6) == 'admin/') {
                $file_source = OSCOM::getConfig('dir_root') . substr($d, 6);
              }

              if (isset($file_source)) {
                if (file_exists($file_source)) {
                  if (is_dir($file_source)) {
                    foreach (FileSystem::getDirectoryContents($file_source) as $dr) {
                      if (!FileSystem::isWritable($dr, true) || !FileSystem::isWritable(dirname($dr), true)) {
                        $errors[] = FileSystem::displayPath($dr);
                      }
                    }
                  }

                  if (!FileSystem::isWritable($file_source, true) || !FileSystem::isWritable(dirname($file_source), true)) {
                    $errors[] = FileSystem::displayPath($file_source);
                  }
                }
              }
            }
          }

          if (!empty($errors)) {
            throw new \Exception('### ERROR ###' . "\n" . 'Could not write to the following files and directories - please check their file permissions:' . "\n\n" . implode("\n", $errors));
          }

          OnlineUpdate::log('Starting the update process', $_POST['version']);

          $OU = null;

          if (is_file($work_dir . '/' . $_POST['version'] . '/Update.php')) {
            include($work_dir . '/' . $_POST['version'] . '/Update.php');

            $OU = new OSC\OM\OnlineUpdate\Update;

            if ($OU->version != $meta['version']) {
              throw new \Exception('### ERROR ###' . "\n" . 'Update class version does not match update package version. Update Package version: ' . $meta['version'] . '; Update Class version: ' . $OU->version);
            }
          }

          if (isset($OU) && ($OU instanceof \OSC\OM\OnlineUpdate\Update) && method_exists($OU, 'runBefore')) {
            OnlineUpdate::log('Executing update package runBefore()', $_POST['version']);

            $OU->runBefore();
          }

          foreach ($update_pkg_contents as $file) {
            $pathname = substr($file, strlen($work_dir . '/' . $_POST['version'] . '/'));

            $file_source = null;

            if (substr($pathname, 0, 8) == 'catalog/') {
              $file_source = OSCOM::getConfig('dir_root', 'Shop') . substr($pathname, 8);
            } elseif (substr($pathname, 0, 6) == 'admin/') {
              $file_source = OSCOM::getConfig('dir_root') . substr($pathname, 6);
            }

            if (isset($file_source)) {
              $target = dirname($file_source);

              if (!is_dir($target)) {
                mkdir($target, 0777, true);

                OnlineUpdate::log('+ CREATED: ' . FileSystem::displayPath($target), $_POST['version']);
              }

              $action = is_file($file_source) ? 'UPDATED' : 'ADDED';

              if (copy($file, $file_source)) {
                OnlineUpdate::log('+ ' . $action . ': ' . FileSystem::displayPath($file_source), $_POST['version']);
              } else {
                throw new \Exception('### ERROR ###' . "\n" . 'Could not write to the following file: ' . FileSystem::displayPath($file_source));
              }
            }
          }

          if (!empty($to_del)) {
            foreach ($to_del as $d) {
              $file_source = null;

              if (substr($d, 0, 8) == 'catalog/') {
                $file_source = OSCOM::getConfig('dir_root', 'Shop') . substr($d, 8);
              } elseif (substr($d, 0, 6) == 'admin/') {
                $file_source = OSCOM::getConfig('dir_root') . substr($d, 6);
              }

              if (isset($file_source)) {
                if (file_exists($file_source)) {
                  if (is_dir($file_source)) {
                    foreach (FileSystem::rmdir($file_source) as $delresult) {
                      if ($delresult['result'] === true) {
                        OnlineUpdate::log('- DELETED: ' . FileSystem::displayPath($delresult['source']), $_POST['version']);
                      } else {
                        OnlineUpdate::log('--- DELETE ERROR: Could not delete the following file or directory: ' . FileSystem::displayPath($delresult['source']), $_POST['version']);
                      }
                    }
                  } else {
                    if (unlink($file_source)) {
                      OnlineUpdate::log('- DELETED: ' . FileSystem::displayPath($file_source), $_POST['version']);
                    } else {
                      OnlineUpdate::log('--- DELETE ERROR: Could not delete the following file: ' . FileSystem::displayPath($file_source), $_POST['version']);
                    }
                  }
                }
              }
            }
          }

          if (isset($OU) && ($OU instanceof \OSC\OM\OnlineUpdate\Update) && method_exists($OU, 'runAfter')) {
            OnlineUpdate::log('Executing update package runAfter()', $_POST['version']);

            $OU->runAfter();
          }

          if (file_put_contents(OSCOM::BASE_DIR . 'version.txt', $_POST['version'])) {
            OnlineUpdate::log('+ UPDATED: ' . FileSystem::displayPath(OSCOM::BASE_DIR . 'version.txt'), $_POST['version']);
          } else {
            OnlineUpdate::log('+++ UPDATE ERROR: Could not update the following file: ' . FileSystem::displayPath(OSCOM::BASE_DIR . 'version.txt'), $_POST['version']);
          }

          OnlineUpdate::log('Finished update', $_POST['version']);

          $result['result'] = 1;

          FileSystem::rmdir($work_dir);
        } catch (\Exception $e) {
          OnlineUpdate::log($e->getMessage(), $_POST['version']);
        }

        echo json_encode($result);

        exit;
        break;
    }
  }

  $new_version = [];

  if (is_array($releases) && !empty($releases)) {
    if (!empty($versions)) {
      $new_version = array_slice($versions, -1)[0];
    }

    if (!empty($new_version)) {
      $OSCOM_MessageStack->add(OSCOM::getDef('version_upgrades_available', ['version' => $new_version['version']]), 'warning', 'versionCheck');
    } else {
      $OSCOM_MessageStack->add(OSCOM::getDef('version_running_latest'), 'success', 'versionCheck');
    }
  } else {
    $OSCOM_MessageStack->add(OSCOM::getDef('error_could_not_connect'), 'error', 'versionCheck');
  }

  require($oscTemplate->getFile('template_top.php'));
?>

<h2><i class="fa fa-cloud-download"></i> <a href="<?= OSCOM::link('online_update.php'); ?>"><?= OSCOM::getDef('heading_title'); ?></a></h2>

<div id="onlineUpdateContentBlock">
  <p><?php echo OSCOM::getDef('title_installed_version') . ' <strong>osCommerce Online Merchant v' . $current_version . '</strong>'; ?></p>

  <?php echo $OSCOM_MessageStack->get('versionCheck'); ?>

<?php
  if (!empty($new_version)) {
?>

  <?= HTML::button('Start Update Procedure', 'fa fa-cloud-download', null, ['params' => 'id="updateStartButton"'], 'btn-success'); ?>

  <div id="updateProgressBar" class="progress hide">
    <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em;"></div>
  </div>
</div>

<div id="onlineUpdateSuccessBlock" class="hidden">

<?php
    $heading = $contents = [];

    $heading[] = array('text' => 'Success!');
    $contents[] = array('text' => 'osCommerce Online Merchant has been successfully updated to the latest version!');

    echo HTML::panel($heading, $contents, ['type' => 'success']);
?>

</div>

<h3>Releases</h3>

<div id="releasePanels" class="panel-group"></div>

<script id="templateReleasePanel" type="x-tmpl-mustache">

<?php
    $heading = $contents = [];

    $heading[] = array('text' => 'v{{version}} ({{date}})');
    $contents[] = array('text' => '');

    echo HTML::panel($heading, $contents, ['type' => 'info']);
?>

</script>

<script>
$(function() {
  var versions = <?= json_encode($versions); ?>;

  var showVersionBlock = function(version) {
    var upDivId = 'up' + version.replace(/\./g, '\\\.');

    $('#' + upDivId + ' .panel-body .row').html('<i class="fa fa-refresh fa-spin fa-fw"></i>');

    $.post('<?= OSCOM::link('online_update.php', 'action=getReleaseNotes'); ?>', {version: version}, function(data) {
      data = $('<div>').text(data).html().replace(/\n/g, '<br />');

      data = anchorme.js(data, {
        'attributes': {
          'target': '_blank'
        }
      });

      if ($('#' + upDivId).hasClass('panel-danger')) {
        $('#' + upDivId).removeClass('panel-danger').addClass('panel-info');
      }

      $('#' + upDivId + ' .panel-body .row').html(data);
      $('#' + upDivId + ' .panel-body .row').prepend('<a href="https://www.oscommerce.com/?RPC&GotoReleaseAnnouncement&v=oscom-' + version + '-ou" class="pull-right btn btn-info btn-sm" target="_blank"><i class="fa fa-external-link fa-fw"></i> View Online</a>');
    }).fail(function() {
      $('#' + upDivId + ' .panel-body .row').html('Error: Could not retrieve release notes for this version. <a data-action="showVersionBlock" class="btn btn-danger btn-sm"><i class="fa fa-refresh"></i> Retry</a>');

      $('#' + upDivId + ' .panel-body .row a[data-action="showVersionBlock"]').on('click', function() {
        showVersionBlock(version);
      });

      if ($('#' + upDivId).hasClass('panel-info')) {
        $('#' + upDivId).removeClass('panel-info').addClass('panel-danger');
      }
    });
  };

  var templateReleasePanel = $('#templateReleasePanel').html();
  Mustache.parse(templateReleasePanel);

  $(versions).each(function (k, v) {
    var panel = $.parseHTML(Mustache.render(templateReleasePanel, v));

    $(panel).attr('id', 'up' + v.version);

    $(panel).appendTo('#releasePanels');

    showVersionBlock(v.version);
  });

  $('#updateStartButton').on('click', function() {
    var updateError = false;
    var total = $('#releasePanels .panel').length;
    var each_percent = Math.round(100 / total);
    var counter = 0;

    $('#updateStartButton').hide();

    $('#updateProgressBar').removeClass('hide');

    $('#releasePanels .panel .panel-body').hide();

    (function() {
      var runQueueInOrder = function(i) {
        if (updateError == true) {
          return false;
        }

        if (i >= versions.length) {
          $('#onlineUpdateContentBlock').hide();

          $('#onlineUpdateSuccessBlock').removeClass('hidden');

          return true;
        }

        var upDivId = 'up' + versions[i].version.replace(/\./g, '\\\.');

        $('#' + upDivId).removeClass('panel-info').addClass('panel-primary');
        $('#' + upDivId + ' .panel-heading').prepend('<i data-icon="status" class="fa fa-refresh fa-spin fa-fw pull-right"></i>');
        $('#' + upDivId + ' .panel-body .row').html('Downloading..');
        $('#' + upDivId + ' .panel-body').show();

        $.post('<?= addslashes(OSCOM::link('online_update.php', 'action=downloadRelease')); ?>', {version: versions[i].version}, function(data) {
          if ((typeof data == 'object') && ('result' in data) && (data.result === 1)) {
            $('#' + upDivId + ' .panel-body .row').html('Applying..');

            $.post('<?= addslashes(OSCOM::link('online_update.php', 'action=applyRelease')); ?>', {version: versions[i].version}, function(data) {
              if ((typeof data == 'object') && ('result' in data) && (data.result === 1)) {
                $('#' + upDivId + ' .panel-body').hide();
                $('#' + upDivId + ' .panel-heading i[data-icon="status"]').removeClass('fa-spin').removeClass('fa-refresh').addClass('fa-check');
                $('#' + upDivId).removeClass('panel-primary').addClass('panel-success');

                var counter = i + 1;

                var progress_value = each_percent * counter;
                $('#updateProgressBar .progress-bar').css('width', progress_value + '%').attr('aria-valuenow', progress_value);
              } else {
                updateError = true;

                $('#' + upDivId).removeClass('panel-primary').addClass('panel-danger');
                $('#' + upDivId + ' .panel-heading i[data-icon="status"]').removeClass('fa-spin').removeClass('fa-refresh').addClass('fa-exclamation-circle');
                $('#' + upDivId + ' .panel-body .row').html('Error!');

                $.post('<?= OSCOM::link('online_update.php', 'action=getUpdateLog'); ?>', {version: versions[i].version}, function(data) {
                  if ((typeof data == 'object') && ('result' in data) && (data.result === 1)) {
                    var log = $('<div>').text(data.log).html().replace(/\n/g, '<br />');

                    $('#' + upDivId + ' .panel-body .row').append('<br /><br />The following log can be found at:<br /><br />' + data.path + '<br /><br />' + log);
                  }
                }, 'json');
              }
            }, 'json').fail(function() {
              updateError = true;

              $('#' + upDivId).removeClass('panel-primary').addClass('panel-danger');
              $('#' + upDivId + ' .panel-heading i[data-icon="status"]').removeClass('fa-spin').removeClass('fa-refresh').addClass('fa-exclamation-circle');
              $('#' + upDivId + ' .panel-body .row').html('Error!<br /><br />Could not start the procedure to apply the update package. Please try again.');
            }).then(function() {
              i++;

              runQueueInOrder(i);
            });
          } else {
            updateError = true;

            var error_msg = 'Error!';

            if ((typeof data == 'object') && ('result' in data)) {
              if (data.result === -2) {
                error_msg = error_msg + '<br /><br />Cannot download the online update package. Please check the file permissions of the following directory:<br /><br />' + data.path;
              } else if (data.result === -3) {
                error_msg = error_msg + '<br /><br />Cannot save the online update package. Please check the file permissions of the following file:<br /><br />' + data.path;
              }
            }

            $('#' + upDivId).removeClass('panel-primary').addClass('panel-danger');
            $('#' + upDivId + ' .panel-heading i[data-icon="status"]').removeClass('fa-spin').removeClass('fa-refresh').addClass('fa-exclamation-circle');
            $('#' + upDivId + ' .panel-body .row').html(error_msg);
          }
        }, 'json').fail(function() {
          updateError = true;

          $('#' + upDivId).removeClass('panel-primary').addClass('panel-danger');
          $('#' + upDivId + ' .panel-heading i[data-icon="status"]').removeClass('fa-spin').removeClass('fa-refresh').addClass('fa-exclamation-circle');
          $('#' + upDivId + ' .panel-body .row').html('Error!<br /><br />Could not connect to the osCommerce Website to download the update package. Please try again.');
        });
      }

      runQueueInOrder(0);
    })();
  });
});
</script>

<script src="<?= OSCOM::link('Shop/ext/anchorme/anchorme.min.js'); ?>"></script>

<?php
  }

  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
