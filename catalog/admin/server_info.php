<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

use OSC\OM\HTML;
use OSC\OM\HTTP;
use OSC\OM\OSCOM;

require('includes/application_top.php');

$OSCOM_Language->loadDefinitions('server_info');

$info = tep_get_system_information();
$server = parse_url(OSCOM::getConfig('http_server'));

$action = (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
    case 'getPhpInfo':
        phpinfo();
        exit;
        break;

    case 'submit':
        $response = HTTP::getResponse([
            'url' => 'https://www.oscommerce.com/index.php?RPC&Website&Index&SaveUserServerInfo&v=2',
            'parameters' => [
                'info' => json_encode($info)
            ]
        ]);

        if ($response != 'OK') {
            $OSCOM_MessageStack->add(OSCOM::getDef('error_info_submit'), 'error');
        } else {
            $OSCOM_MessageStack->add(OSCOM::getDef('success_info_submit'), 'success');
        }

        OSCOM::redirect('server_info.php');
        break;

    case 'save':
        $info_file = 'server_info-' . date('YmdHis') . '.txt';

        header('Content-type: text/plain');
        header('Content-disposition: attachment; filename=' . $info_file);

        echo tep_format_system_info_array($info);

        exit;
        break;
}

require($oscTemplate->getFile('template_top.php'));

if (!isset($_GET['action'])) {
?>

<div class="pull-right">
  <?= HTML::button(OSCOM::getDef('image_export'), 'fa fa-upload', OSCOM::link('server_info.php', 'action=export'), null, 'btn-info'); ?>
  <?= HTML::button(OSCOM::getDef('button_php_info'), 'fa fa-info-circle', OSCOM::link('server_info.php', 'action=getPhpInfo'), ['newwindow' => true], 'btn-info'); ?>
</div>

<?php
}
?>

<h2><i class="fa fa-tasks"></i> <a href="<?= OSCOM::link('server_info.php'); ?>"><?= OSCOM::getDef('heading_title'); ?></a></h2>

<?php
if ($action == 'export') {
?>

<p>
  <?=
    OSCOM::getDef('text_export_intro', [
        'button_submit_to_oscommerce' => OSCOM::getDef('button_submit_to_oscommerce'),
        'button_save' => OSCOM::getDef('image_save')
    ]);
  ?>
</p>

<p>
  <?= HTML::textareaField('server_settings', '100', '15', tep_format_system_info_array($info), 'readonly', false); ?>
</p>

<p>
  <?= HTML::button(OSCOM::getDef('button_submit_to_oscommerce'), 'fa fa-upload', OSCOM::link('server_info.php', 'action=submit'), null, 'btn-info') . '&nbsp;' . HTML::button(OSCOM::getDef('image_save'), 'fa fa-save', OSCOM::link('server_info.php', 'action=save'), null, 'btn-info'); ?>
</p>

<?php
} else {
?>

<table class="table table-hover">
  <tbody>
    <tr>
      <td><strong><?= OSCOM::getDef('title_oscom_version'); ?></strong></td>
      <td><?= OSCOM::getVersion(); ?></td>
    </tr>
    <tr>
      <td><strong><?= OSCOM::getDef('title_http_server'); ?></strong></td>
      <td><?= $info['system']['http_server']; ?></td>
    </tr>
    <tr>
      <td><strong><?= OSCOM::getDef('title_php_version'); ?></strong></td>
      <td><?= $info['php']['version'] . ' (' . OSCOM::getDef('title_zend_version') . ' ' . $info['php']['zend'] . ')'; ?></td>
    </tr>
    <tr>
      <td><strong><?= OSCOM::getDef('title_server_host'); ?></strong></td>
      <td><?= $server['host'] . ' (' . gethostbyname($server['host']) . ')'; ?></td>
    </tr>
    <tr>
      <td><strong><?= OSCOM::getDef('title_server_os'); ?></strong></td>
      <td><?= $info['system']['os'] . ' ' . $info['system']['kernel']; ?></td>
    </tr>
    <tr>
      <td><strong><?= OSCOM::getDef('title_server_date'); ?></strong></td>
      <td><?= $info['system']['date']; ?></td>
    </tr>
    <tr>
      <td><strong><?= OSCOM::getDef('title_server_up_time'); ?></strong></td>
      <td><?= $info['system']['uptime']; ?></td>
    </tr>
    <tr>
      <td><strong><?= OSCOM::getDef('title_database_host'); ?></strong></td>
      <td><?= OSCOM::getConfig('db_server') . ' (' . gethostbyname(OSCOM::getConfig('db_server')) . ')'; ?></td>
    </tr>
    <tr>
      <td><strong><?= OSCOM::getDef('title_database'); ?></strong></td>
      <td><?= 'MySQL ' . $info['mysql']['version']; ?></td>
    </tr>
    <tr>
      <td><strong><?= OSCOM::getDef('title_database_date'); ?></strong></td>
      <td><?= $info['mysql']['date']; ?></td>
    </tr>
    <tr>
      <td><strong><?= OSCOM::getDef('title_database_name'); ?></strong></td>
      <td><?= OSCOM::getConfig('db_database'); ?></td>
    </tr>
  </tbody>
</table>

<?php
}

require($oscTemplate->getFile('template_bottom.php'));
require('includes/application_bottom.php');
?>
