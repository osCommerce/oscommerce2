<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Apps;
  use OSC\OM\Cache;
  use OSC\OM\HTML;
  use OSC\OM\HTTP;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'getShowcase':
        $result = [
          'result' => -1
        ];

        $AppsShowcaseCache = new Cache('apps-showcase');

        if ($AppsShowcaseCache->exists(360)) {
          $showcase = $AppsShowcaseCache->get();
        } else {
          $showcase = [];

          $version_url = str_replace('.', '_', OSCOM::getVersion());

          $response = HTTP::getResponse([
            'url' => 'https://apps.oscommerce.com/index.php?RPC&GetShowcase&' . $version_url
          ]);

          if (!empty($response)) {
            $showcase = json_decode($response, true);
          }

          if (is_array($showcase) && !empty($showcase) && isset($showcase['rpcStatus']) && ($showcase['rpcStatus'] === 1)) {
            $AppsShowcaseCache->save($showcase);
          }
        }

        if (is_array($showcase) && !empty($showcase) && isset($showcase['rpcStatus']) && ($showcase['rpcStatus'] === 1) && isset($showcase['showcase'])) {
          $result['result'] = 1;
          $result['showcase'] = [];

          foreach($showcase['showcase'] as $app) {
            $result['showcase'][] = [
              'vendor' => $app['vendor'],
              'app' => $app['app'],
              'title' => $app['title'],
              'description' => $app['description'],
              'is_installed' => Apps::exists($app['vendor'] . '\\' . $app['app'])
            ];
          }
        }

        echo json_encode($result);
        exit;
        break;

      case 'getInstalledApps':
        $result = [
          'result' => -1
        ];

        $apps = Apps::getAll();

        if (is_array($apps)) {
          $result['result'] = 1;
          $result['apps'] = $apps;
        }

        echo json_encode($result);
        exit;
        break;
    }
  }

  require($oscTemplate->getFile('template_top.php'));
?>

<h2><i class="fa fa-th-large"></i> <a href="<?= OSCOM::link('apps.php'); ?>"><?= OSCOM::getDef('heading_title'); ?></a></h2>

<h3>Showcase Apps</h3>

<div id="appShowcase" class="container"></div>

<h3>Installed Apps</h3>

<table id="appsInstalledTable" class="oscom-table table table-hover">
  <thead>
    <tr class="info">
      <th><?= OSCOM::getDef('table_heading_apps'); ?></th>
      <th><?= OSCOM::getDef('table_heading_vendor'); ?></th>
      <th class="text-right"><?= OSCOM::getDef('table_heading_version'); ?></th>
      <th class="action"></th>
    </tr>
  </thead>
  <tbody></tbody>
</table>

<script id="appShowcaseBlock" type="x-tmpl-mustache">
<div class="col-xs-3 center-block text-center">
  <img src="https://apps.oscommerce.com/public/sites/Apps/images/showcase/{{image}}" title="{{title}}: {{description}}" class="img-responsive" />
  <span class="label label-{{label_type}}">{{label_text}}</span>
</div>
</script>

<script id="appInstalledTableEntry" type="x-tmpl-mustache">
<tr>
  <td>{{title}}</td>
  <td>{{vendor}}</td>
  <td class="text-right">{{version}}</td>
  <td class="action"></td>
</tr>
</script>

<script>
$(function() {
  function rpcGetShowcase() {
    if ($('#appShowcase').hasClass('container-fluid')) {
      $('#appShowcase').removeClass('container-fluid').addClass('container');
    }

    $('#appShowcase').empty();

    $('#appShowcase').append('<div class="row"><div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div></div>');

    $.get('<?= addslashes(OSCOM::link('apps.php', 'action=getShowcase')); ?>', function(response) {
      $('#appShowcase').empty();

      if ((typeof response == 'object') && ('result' in response) && (response.result === 1)) {
        var counter = 0;
        var row = 0;

        var appShowcaseBlock = $('#appShowcaseBlock').html();
        Mustache.parse(appShowcaseBlock);

        $(response.showcase).each(function(k, v) {
          counter += 1;

          if ((counter === 1) || (counter === 5)) {
            row += 1;

            if (counter === 5) {
              counter = 1;
            }

            $('#appShowcase').append('<div id="appShowcaseRow' + row + '" class="row" style="padding-bottom: 20px;"></div>');
          }

          var block = $.parseHTML(Mustache.render(appShowcaseBlock, {
            title: v.title,
            description: v.description,
            image: v.vendor.toLowerCase() + '_' + v.app.toLowerCase() + '.png',
            label_type: v.is_installed ? 'success' : 'info',
            label_text: v.is_installed ? 'Installed' : 'Coming Soon'
          }));

          $(block).appendTo('#appShowcaseRow' + row);
        });
      } else {
        errorRpcGetShowcase();
      }
    }, 'json').fail(function() {
      errorRpcGetShowcase();
    });
  };

  $('#appShowcase').on('click', 'div[data-row="rpcError"] a[data-action="doRpcGetShowcase"]', function() {
    rpcGetShowcase();
  });

  function errorRpcGetShowcase() {
    $('#appShowcase').empty().removeClass('container').addClass('container-fluid').append('<div class="row" data-row="rpcError">There was a problem retrieving the list of showcase Apps. <a data-action="doRpcGetShowcase">Try again.</a></div>');
  };

  function rpcGetInstalledApps() {
    $('#appsInstalledTable tbody').empty();

    $('#appsInstalledTable tbody').append('<tr><td colspan="' + $('#appsInstalledTable thead th').length + '"><i class="fa fa-spinner fa-spin"></i></td></tr>');

    $.get('<?= addslashes(OSCOM::link('apps.php', 'action=getInstalledApps')); ?>', function(response) {
      $('#appsInstalledTable tbody').empty();

      if ((typeof response == 'object') && ('result' in response) && (response.result === 1)) {
        var appInstalledTableEntry = $('#appInstalledTableEntry').html();
        Mustache.parse(appInstalledTableEntry);

        $(response.apps).each(function(k, v) {
          var entry = $.parseHTML(Mustache.render(appInstalledTableEntry, {
            title: v.title,
            vendor: v.vendor,
            version: v.version
          }));

          $(entry).appendTo('#appsInstalledTable tbody');
        });

        if ($('#appsInstalledTable tbody tr').length < 1) {
          $('#appsInstalledTable tbody').append('<tr><td colspan="' + $('#appsInstalledTable thead th').length + '">There are currently no Apps installed.</td></tr>');
        }
      } else {
        errorRpcGetInstalledApps();
      }
    }, 'json').fail(function() {
      errorRpcGetInstalledApps();
    });
  };

  $('#appsInstalledTable tbody').on('click', 'tr[data-row="rpcError"] td a[data-action="doRpcGetInstalledApps"]', function() {
    rpcGetInstalledApps();
  });

  function errorRpcGetInstalledApps() {
    $('#appsInstalledTable tbody').empty().append('<tr data-row="rpcError"><td colspan="' + $('#appsInstalledTable thead th').length + '">There was a problem retrieving the list of installed Apps. <a data-action="doRpcGetInstalledApps">Try again.</a></td></tr>');
  };

  rpcGetInstalledApps();
  rpcGetShowcase();
});
</script>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
