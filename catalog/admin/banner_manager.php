<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\FileSystem;
  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  $banner_extension = tep_banner_image_extension();

  if (tep_not_null($action)) {
    switch ($action) {
      case 'fetchStats':
        $result = [];

        if (isset($_GET['banners_id']) && is_numeric($_GET['banners_id'])) {
          $Qbanner = $OSCOM_Db->prepare('select banners_title from :table_banners where banners_id = :banners_id');
          $Qbanner->bindInt(':banners_id', $_GET['banners_id']);
          $Qbanner->execute();

          if ($Qbanner->fetch() !== false) {
            $days_shown = $days_clicked = [];
            for($i = 0; $i < 7; $i++) {
              $date = date('m-d', strtotime('-'. $i .' days'));

              $days_shown[$date] = $days_clicked[$date] = 0;
            }

            $Qstats = $OSCOM_Db->prepare('select date_format(banners_history_date, "%m-%d") as date_day, banners_shown, banners_clicked from :table_banners_history where banners_id = :banners_id and banners_history_date >= date_sub(now(), interval 7 day)');
            $Qstats->bindInt(':banners_id', $_GET['banners_id']);
            $Qstats->execute();

            while ($Qstats->fetch()) {
              $days_shown[$Qstats->value('date_day')] = $Qstats->valueInt('banners_shown');
              $days_clicked[$Qstats->value('date_day')] = $Qstats->valueInt('banners_clicked');
            }

            $result['labels'] = array_reverse(array_keys($days_shown));
            $result['days'] = array_reverse(array_values($days_shown));
            $result['clicks'] = array_reverse(array_values($days_clicked));
            $result['title'] = $Qbanner->valueProtected('banners_title');
          }
        }

        echo json_encode($result);

        exit;
        break;
      case 'setflag':
        if ( ($_GET['flag'] == '0') || ($_GET['flag'] == '1') ) {
          tep_set_banner_status($_GET['bID'], $_GET['flag']);

          $OSCOM_MessageStack->add(SUCCESS_BANNER_STATUS_UPDATED, 'success');
        } else {
          $OSCOM_MessageStack->add(ERROR_UNKNOWN_STATUS_FLAG, 'error');
        }

        OSCOM::redirect(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page']);
        break;
      case 'insert':
      case 'update':
        if (isset($_POST['banners_id'])) $banners_id = HTML::sanitize($_POST['banners_id']);
        $banners_title = HTML::sanitize($_POST['banners_title']);
        $banners_url = HTML::sanitize($_POST['banners_url']);
        $new_banners_group = HTML::sanitize($_POST['new_banners_group']);
        $banners_group = (empty($new_banners_group)) ? HTML::sanitize($_POST['banners_group']) : $new_banners_group;
        $banners_html_text = $_POST['banners_html_text'];
        $banners_image_local = HTML::sanitize($_POST['banners_image_local']);
        $banners_image_target = HTML::sanitize($_POST['banners_image_target']);
        $db_image_location = '';
        $expires_date = HTML::sanitize($_POST['expires_date']);
        $expires_impressions = HTML::sanitize($_POST['expires_impressions']);
        $date_scheduled = HTML::sanitize($_POST['date_scheduled']);

        $banner_error = false;
        if (empty($banners_title)) {
          $OSCOM_MessageStack->add(ERROR_BANNER_TITLE_REQUIRED, 'error');
          $banner_error = true;
        }

        if (empty($banners_group)) {
          $OSCOM_MessageStack->add(ERROR_BANNER_GROUP_REQUIRED, 'error');
          $banner_error = true;
        }

        if (empty($banners_html_text)) {
          if (empty($banners_image_local)) {
            $banners_image = new upload('banners_image');
            $banners_image->set_destination(OSCOM::getConfig('dir_root', 'Shop') . 'images/' . $banners_image_target);
            if ( ($banners_image->parse() == false) || ($banners_image->save() == false) ) {
              $banner_error = true;
            }
          }
        }

        if ($banner_error == false) {
          $db_image_location = (tep_not_null($banners_image_local)) ? $banners_image_local : $banners_image_target . $banners_image->filename;
          $sql_data_array = array('banners_title' => $banners_title,
                                  'banners_url' => $banners_url,
                                  'banners_image' => $db_image_location,
                                  'banners_group' => $banners_group,
                                  'banners_html_text' => $banners_html_text,
                                  'expires_date' => 'null',
                                  'expires_impressions' => 0,
                                  'date_scheduled' => 'null');

          if ($action == 'insert') {
            $insert_sql_data = array('date_added' => 'now()',
                                     'status' => '1');

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            $OSCOM_Db->save('banners', $sql_data_array);

            $banners_id = $OSCOM_Db->lastInsertId();

            $OSCOM_MessageStack->add(SUCCESS_BANNER_INSERTED, 'success');
          } elseif ($action == 'update') {
            $OSCOM_Db->save('banners', $sql_data_array, ['banners_id' => (int)$banners_id]);

            $OSCOM_MessageStack->add(SUCCESS_BANNER_UPDATED, 'success');
          }

          if (tep_not_null($expires_date)) {
            $expires_date = substr($expires_date, 0, 4) . substr($expires_date, 5, 2) . substr($expires_date, 8, 2);

            $OSCOM_Db->save('banners', [
              'expires_date' => $expires_date,
              'expires_impressions' => 'null'
            ], [
              'banners_id' => (int)$banners_id
            ]);
          } elseif (tep_not_null($expires_impressions)) {
            $OSCOM_Db->save('banners', [
              'expires_impressions' => $expires_impressions,
              'expires_date' => 'null'
            ], [
              'banners_id' => (int)$banners_id
            ]);
          }

          if (tep_not_null($date_scheduled)) {
            $date_scheduled = substr($date_scheduled, 0, 4) . substr($date_scheduled, 5, 2) . substr($date_scheduled, 8, 2);

            $OSCOM_Db->save('banners', [
              'status' => '0',
              'date_scheduled' => $date_scheduled
            ],
            [
              'banners_id' => (int)$banners_id
            ]);
          }

          OSCOM::redirect(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page']);
        } else {
          $action = 'new';
        }
        break;
      case 'deleteconfirm':
        $banners_id = HTML::sanitize($_GET['bID']);

        if (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on')) {
          $Qbanner = $OSCOM_Db->get('banners', 'banners_image', ['banners_id' => (int)$banners_id]);

          if (tep_not_null($Qbanner->value('banners_image')) && is_file(OSCOM::getConfig('dir_root', 'Shop') . 'images/' . $Qbanner->value('banners_image'))) {
            if (FileSystem::isWritable(OSCOM::getConfig('dir_root', 'Shop') . 'images/' . $Qbanner->value('banners_image'))) {
              unlink(OSCOM::getConfig('dir_root', 'Shop') . 'images/' . $Qbanner->value('banners_image'));
            } else {
              $OSCOM_MessageStack->add(ERROR_IMAGE_IS_NOT_WRITEABLE, 'error');
            }
          } else {
            $OSCOM_MessageStack->add(ERROR_IMAGE_DOES_NOT_EXIST, 'error');
          }
        }

        $OSCOM_Db->delete('banners', ['banners_id' => (int)$banners_id]);
        $OSCOM_Db->delete('banners_history', ['banners_id' => (int)$banners_id]);

        $OSCOM_MessageStack->add(SUCCESS_BANNER_REMOVED, 'success');

        OSCOM::redirect(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page']);
        break;
      case 'preview':
        $banners_id = HTML::sanitize($_GET['banner']);

        $Qbanner = $OSCOM_Db->get('banners', [
          'banners_title',
          'banners_image',
          'banners_html_text'
        ], [
          'banners_id' => (int)$banners_id
        ]);

        if ($Qbanner->check()) {
          echo '<h1>' . $Qbanner->valueProtected('banners_title') . '</h1>';

          if (tep_not_null($Qbanner->value('banners_html_text'))) {
            echo $Qbanner->value('banners_html_text');
          } elseif (tep_not_null($Qbanner->value('banners_image'))) {
            echo HTML::image(OSCOM::linkImage('Shop/' . $Qbanner->value('banners_image')), $Qbanner->value('banners_title'));
          }

          exit;
        }
        break;
    }
  }

  $show_listing = true;

  require($oscTemplate->getFile('template_top.php'));

  if (empty($action)) {
?>

<div class="pull-right">
  <?= HTML::button(IMAGE_NEW_BANNER, 'fa fa-plus', OSCOM::link('banner_manager.php', 'action=new'), null, 'btn-info'); ?>
</div>

<?php
  }
?>

<h2><i class="fa fa-picture-o"></i> <a href="<?= OSCOM::link('banner_manager.php'); ?>"><?= HEADING_TITLE; ?></a></h2>

<?php
  if (!empty($action)) {
    if ($action == 'new') {
      $show_listing = false;

      $form_action = 'insert';

      $parameters = array('expires_date' => '',
                          'date_scheduled' => '',
                          'banners_title' => '',
                          'banners_url' => '',
                          'banners_group' => '',
                          'banners_image' => '',
                          'banners_html_text' => '',
                          'expires_impressions' => '');

      $bInfo = new objectInfo($parameters);

      if (isset($_GET['bID'])) {
        $form_action = 'update';

        $bID = HTML::sanitize($_GET['bID']);

        $Qbanner = $OSCOM_Db->get('banners', [
          'banners_title',
          'banners_url',
          'banners_image',
          'banners_group',
          'banners_html_text',
          'status',
          'date_format(date_scheduled, "%Y-%m-%d") as date_scheduled',
          'date_format(expires_date, "%Y-%m-%d") as expires_date',
          'expires_impressions',
          'date_status_change'
        ], [
          'banners_id' => (int)$bID
        ]);

        $bInfo->objectInfo($Qbanner->toArray());
      } elseif (tep_not_null($_POST)) {
        $bInfo->objectInfo($_POST);
      }

      $groups_array = [];

      $Qgroups = $OSCOM_Db->get('banners', 'distinct banners_group', null, 'banners_group');

      while ($Qgroups->fetch()) {
        $groups_array[] = [
          'id' => $Qgroups->value('banners_group'),
          'text' => $Qgroups->value('banners_group')
        ];
      }
?>

<?= HTML::form('new_banner', OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&action=' . $form_action), 'post', 'enctype="multipart/form-data"') . (($form_action == 'update') ? HTML::hiddenField('banners_id', $bID) : ''); ?>

<div class="panel panel-info">
  <div class="panel-body">
    <div class="form-group">
      <label for="banners_title"><?= TEXT_BANNERS_TITLE . TEXT_FIELD_REQUIRED; ?></label>
      <?= HTML::inputField('banners_title', $bInfo->banners_title); ?>
    </div>

    <div class="form-group">
      <label for="banners_url"><?= TEXT_BANNERS_URL; ?></label>
      <?= HTML::inputField('banners_url', $bInfo->banners_url); ?>
    </div>

    <div class="form-group">
      <label for="banners_group"><?= TEXT_BANNERS_GROUP; ?></label>
      <?= HTML::selectField('banners_group', $groups_array, $bInfo->banners_group); ?>

      <label for="new_banners_group"><?= TEXT_BANNERS_NEW_GROUP . ((sizeof($groups_array) > 0) ? '' : TEXT_FIELD_REQUIRED); ?></label>
      <?= HTML::inputField('new_banners_group'); ?>
    </div>

    <div class="form-group">
      <label for="banners_image"><?= TEXT_BANNERS_IMAGE; ?></label>
      <?= HTML::fileField('banners_image'); ?>

      <label for="banners_image_local"><?= TEXT_BANNERS_IMAGE_LOCAL; ?></label>
      <div class="input-group">
        <div class="input-group-addon"><?= OSCOM::getConfig('dir_root', 'Shop') . 'images/'; ?></div>
        <?= HTML::inputField('banners_image_local', (isset($bInfo->banners_image) ? $bInfo->banners_image : '')); ?>
      </div>
    </div>

    <div class="form-group">
      <label for="banners_image_target"><?= TEXT_BANNERS_IMAGE_TARGET; ?></label>
      <div class="input-group">
        <div class="input-group-addon"><?= OSCOM::getConfig('dir_root', 'Shop') . 'images/'; ?></div>
        <?= HTML::inputField('banners_image_target'); ?>
      </div>
    </div>

    <div class="form-group">
      <label for="banners_html_text"><?= TEXT_BANNERS_HTML_TEXT; ?></label>
      <?= HTML::textareaField('banners_html_text', '60', '5', $bInfo->banners_html_text); ?>
    </div>

    <div class="form-group">
      <label for="date_scheduled"><?= TEXT_BANNERS_SCHEDULED_AT; ?></label>
      <?= HTML::inputField('date_scheduled', $bInfo->date_scheduled, 'id="date_scheduled"', 'date'); ?>
    </div>

    <div class="form-group">
      <label for="expires_date"><?= TEXT_BANNERS_EXPIRES_ON; ?></label>
      <?= HTML::inputField('expires_date', $bInfo->expires_date, 'id="expires_date"', 'date'); ?>

      <label for="expires_impressions"><?= TEXT_BANNERS_OR_AT; ?></label>
      <?= HTML::inputField('expires_impressions', $bInfo->expires_impressions, 'maxlength="7" size="7"'); ?>
      <p class="help-block"><?= TEXT_BANNERS_IMPRESSIONS; ?></p>
    </div>

    <?= HTML::button(IMAGE_SAVE, 'fa fa-save', null, null, 'btn-success') . HTML::button(IMAGE_CANCEL, null, OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page']), null, 'btn-link'); ?>
  </div>
</div>

</form>

<p>
  <?= TEXT_BANNERS_BANNER_NOTE . '<br />' . TEXT_BANNERS_INSERT_NOTE . '<br />' . TEXT_BANNERS_EXPIRCY_NOTE . '<br />' . TEXT_BANNERS_SCHEDULE_NOTE; ?>
</p>

<?php
    } else {
      $heading = $contents = [];

      if (isset($_GET['bID'])) {
        $Qbanner = $OSCOM_Db->get('banners', '*', ['banners_id' => (int)$_GET['bID']]);

        if ($Qbanner->fetch() !== false) {
          $bInfo = new objectInfo($Qbanner->toArray());

          if ($action == 'delete') {
            $heading[] = array('text' => $bInfo->banners_title);

            $contents = array('form' => HTML::form('banners', OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id . '&action=deleteconfirm')));
            $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
            $contents[] = array('text' => '<strong>' . $bInfo->banners_title . '</strong>');
            if ($bInfo->banners_image) $contents[] = array('text' => HTML::checkboxField('delete_image', 'on', true) . ' ' . TEXT_INFO_DELETE_IMAGE);
            $contents[] = array('text' => HTML::button(IMAGE_DELETE, 'fa fa-trash', null, null, 'btn-danger') . HTML::button(IMAGE_CANCEL, null, OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $_GET['bID']), null, 'btn-link'));
          }
        }
      }

      if (tep_not_null($heading) && tep_not_null($contents)) {
        $show_listing = false;

        echo HTML::panel($heading, $contents, ['type' => 'info']);
      }
    }
  }

  if ($show_listing === true) {
?>

<table class="oscom-table table table-hover">
  <thead>
    <tr class="info">
      <th><?php echo TABLE_HEADING_BANNERS; ?></th>
      <th class="text-right"><?php echo TABLE_HEADING_GROUPS; ?></th>
      <th class="text-right"><?php echo TABLE_HEADING_STATISTICS; ?></th>
      <th class="text-right"><?php echo TABLE_HEADING_STATUS; ?></th>
      <th class="action"></th>
    </tr>
  </thead>
  <tbody>

<?php
    $Qbanners = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS banners_id, banners_title, banners_group, status from :table_banners order by banners_title, banners_group limit :page_set_offset, :page_set_max_results');
    $Qbanners->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
    $Qbanners->execute();

    while ($Qbanners->fetch()) {
      $Qinfo = $OSCOM_Db->get('banners_history', [
        'sum(banners_shown) as banners_shown',
        'sum(banners_clicked) as banners_clicked'
      ], [
        'banners_id' => $Qbanners->valueInt('banners_id')
      ]);
?>

    <tr>
      <td><?= '<a href="' . OSCOM::link(FILENAME_BANNER_MANAGER, 'action=preview&banner=' . $Qbanners->valueInt('banners_id')) . '" target="_blank"><i class="fa fa-external-link" title="View Banner"></i></a>&nbsp;' . $Qbanners->value('banners_title'); ?></td>
      <td class="text-right"><?= $Qbanners->value('banners_group'); ?></td>
      <td class="text-right"><?= $Qinfo->valueInt('banners_shown') . ' / ' . $Qinfo->valueInt('banners_clicked'); ?></td>
      <td class="text-right">

<?php
      if ($Qbanners->valueInt('status') === 1) {
        echo '<i class="fa fa-circle text-success" title="' . IMAGE_ICON_STATUS_GREEN . '"></i>&nbsp;<a href="' . OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $Qbanners->valueInt('banners_id') . '&action=setflag&flag=0') . '"><i class="fa fa-circle-o text-danger" title="' . IMAGE_ICON_STATUS_RED_LIGHT . '"></i></a>';
      } else {
        echo '<a href="' . OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $Qbanners->valueInt('banners_id') . '&action=setflag&flag=1') . '"><i class="fa fa-circle-o text-success" title="' . IMAGE_ICON_STATUS_GREEN_LIGHT . '"></i></a>&nbsp;<i class="fa fa-circle text-danger" title="' . IMAGE_ICON_STATUS_RED . '"></i>';
      }
?>

      </td>
      <td class="action">
        <?= '<a data-banner-id="' . $Qbanners->valueInt('banners_id') . '" data-toggle="modal" data-target="#statsModal"><i class="fa fa-line-chart" title="' . ICON_STATISTICS . '"></i></a>'; ?>
        <?= '<a href="' . OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $Qbanners->valueInt('banners_id') . '&action=new') . '"><i class="fa fa-pencil" title="' . IMAGE_EDIT . '"></i></a>'; ?>
        <?= '<a href="' . OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $Qbanners->valueInt('banners_id') . '&action=delete') . '"><i class="fa fa-trash" title="' . IMAGE_DELETE . '"></i></a>'; ?>
      </td>
    </tr>

<?php
    }
?>

  </tbody>
</table>

<div>
  <span class="pull-right"><?= $Qbanners->getPageSetLinks(); ?></span>
  <span><?= $Qbanners->getPageSetLabel(TEXT_DISPLAY_NUMBER_OF_BANNERS); ?></span>
</div>

<div id="statsModal" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

        <div class="statsModalContent">
          <i class="fa fa-spinner fa-spin fa-fw"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
$(function() {
  var fetchStatsUrl = '<?= addslashes(OSCOM::link('banner_manager.php', 'action=fetchStats&banners_id={{id}}')); ?>';

  $('#statsModal').on('shown.bs.modal', function (e) {
    var json = $.getJSON(Mustache.render(fetchStatsUrl, {id: $(e.relatedTarget).data('banner-id')}), function(data) {
      if (typeof data.labels !== 'undefined') {
        $('#statsModal .statsModalContent').html('<h4 class="modal-title">' + data.title + '</h4><div id="banner_statistics"></div><div class="text-right"><span class="label label-info">Views</span><span class="label label-danger">Clicks</span></div>');

        var data = {
          labels: data.labels,
          series: [
            {
              name: 'shown',
              data: data.days
            },
            {
              name: 'clicked',
              data: data.clicks
            }
          ]
        };

        var options = {
          fullWidth: true,
          series: {
            'shown': {
              showPoint: false,
              showArea: true
            },
            'clicked': {
              showPoint: false,
              showArea: true
            }
          },
          height: '400px',
          axisY: {
            labelInterpolationFnc: function skipLabels(value, index) {
              return index % 2  === 0 ? value : null;
            }
          }
        }

        var chart = new Chartist.Line('#banner_statistics', data, options);

        chart.on('draw', function(context) {
          if ((typeof context.series !== 'undefined') && (typeof context.series.name !== 'undefined')) {
            if (context.series.name == 'shown') {
              if (context.type === 'line') {
                context.element.attr({
                  style: 'stroke: skyblue;'
                });
              } else if (context.type === 'area') {
                context.element.attr({
                  style: 'fill: skyblue;'
                });
              }
            } else if (context.series.name == 'clicked') {
              if (context.type === 'line') {
                context.element.attr({
                  style: 'stroke: salmon;'
                });
              } else if (context.type === 'area') {
                context.element.attr({
                  style: 'fill: salmon;'
                });
              }
            }
          }
        });

//        chart.update();
      } else {
        $('#statsModal .statsModalContent').html('<div class="alert alert-danger">Could not find banner statistics.</div>');
      }
    }).fail(function() {
        $('#statsModal .statsModalContent').html('<div class="alert alert-danger">Could not fetch banner statistics.</div>');
    });
  });
});
</script>

<?php
  }

  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
