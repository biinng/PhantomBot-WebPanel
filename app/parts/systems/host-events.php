<?php
/**
 * host-events.php
 * Created with PhpStorm
 * User: Robin | Juraji
 * Date: 12 okt 2015
 * Time: 12:43
 */
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/app/php/classes/Configuration.class.php');
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/app/php/classes/ConnectionHandler.class.php');
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/app/php/classes/Functions.class.php');
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/app/php/classes/ComponentTemplates.class.php');

$config = new Configuration();
$connection = new ConnectionHandler($config);
$functions = new Functions($config, $connection);
$templates = new ComponentTemplates();

$botSettings = $functions->getIniArray('settings');

?>
<div class="app-part">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">
        Host Events
        <?= $templates->toggleFavoriteButton() ?>
        <?= $templates->moduleActiveIndicator($functions->getModuleStatus('hostHandler.js')) ?>
      </h3>
    </div>
    <div class="panel-body">
      <div class="btn-group">
        <?= $templates->botCommandButton('hostcount', 'Current Host Count') ?>
        <?= $templates->botCommandButton('hostlist', 'Hoster List') ?>
      </div>
      <hr/>
      <h4 class="collapsible-master">Hosts Settings</h4>

      <div class="collapsible-content">
        <?= $templates->botCommandForm('hostmessage', 'Message on host', 'message', (array_key_exists('hostmessage', $botSettings) ? $botSettings['hostmessage'] : '')) ?>
        <div class="row">
          <div class="col-sm-4">
            <?= $templates->botCommandForm('hostreward', 'Points reward on host', 'amount', (array_key_exists('hostreward', $botSettings) ? $botSettings['hostreward'] : '')) ?>
          </div>
          <div class="col-sm-4">
            <?= $templates->botCommandForm('hosttime', 'Host message cool down', 'minutes', (array_key_exists('hosttimeout', $botSettings) ? $botSettings['hosttimeout'] : '')) ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>