<?php
/**
 * control-panel.php
 * Created with PhpStorm
 * User: Robin | Juraji
 * Date: 12 okt 2015
 * Time: 12:47
 */
require_once('AppLoader.class.php');
\PBPanel\AppLoader::load();

$session = new \PBPanel\Util\PanelSession();
$dataStore = new PBPanel\Util\DataStore();
$connection = new \PBPanel\Util\BotConnectionHandler($dataStore);
$functions = new \PBPanel\Util\Functions($dataStore, $connection);
$templates = new \PBPanel\Util\ComponentTemplates();

if (\PBPanel\AppLoader::runInstall($dataStore)) {
  require_once('install.php');
  exit;
}

if (\PBPanel\AppLoader::updateAvailable($dataStore)) {
  require_once('update.php');
  exit;
}

$session->createToken();

$botSettings = $functions->getIniArray('settings');
$isBotOnline = ($connection->testConnection()[2] == 52);
$hostHandlerActive = $functions->getIniValueByKey('modules.ini', 'hostHandler.js', true);
$subscribeHandlerActive = $functions->getIniValueByKey('modules.ini', 'subscribeHandler.js', true);
$musicPlayerCurrentSong = $functions->getOtherFile($dataStore->getVar('paths', 'youtubeCurrentSong'));
$NOHosts = -1;
$NOSubscribers = -1;
$partsList = $functions->getPartsList();
$renderedMenu = '';

foreach ($partsList as $parentName => $subItems) {
  $parentId = 'menu-parent-' . $parentName;
  $renderedMenu .= '<li class="dropdown" id="' . $parentId . '"><a nohref class="dropdown-toggle" role="button">' . ucwords($parentName) . '</a><ul class="dropdown-menu" role="menu">';

  $icon = 'fa-cog';
  switch ($parentName) {
    case 'games':
      $icon = 'fa-gamepad';
      break;
    case 'extras':
      $icon = 'fa-plug';
      break;
  }

  foreach ($subItems as $item) {
    $openPartParam = '\'' . $parentName . '/' . $item['partFile'] . '\'';
    $customScriptIcon = ($item['isCustom'] ? '&nbsp;&nbsp;&nbsp;<span class="fa fa-wrench"></span>' : '');
    $renderedMenu .= '<li><a nohref onclick="openPart(' . $openPartParam . ')" role="button"><span class="fa ' . $icon . '"></span>&nbsp;' . $item['partName'] . $customScriptIcon . '</a></li>';
  }

  if ($parentName == 'extras') {
    $renderedMenu .= '<li class="divider"></li>'
        . '<li><a nohref onclick="toggleChat(false, this);" id="toggle-chat" role="button"><span class="fa fa-eject"></span>&nbsp;Show Chat</a></li>'
        . '<li><a nohref onclick="toggleMusicPlayerControls(false, this);" id="player-controls-toggle" role="button"><span class="fa fa-eject"></span>&nbsp;Show Music Player Controls</a></li>'
        . '<li class="divider"></li>'
        . '<li><a href="http://www.twitch.tv/' . $dataStore->getVar('connector', 'channelOwner') . '" target="_blank"><span class="fa fa-info-circle" role="button"></span>&nbsp;Your Twitch Channel</a></li>'
        . '<li><a href="http://www.twitch.tv/' . $dataStore->getVar('connector', 'channelOwner') . '/profile" target="_blank"><span class="fa fa-info-circle" role="button"></span>&nbsp;Your Twitch Profile</a></li>'
        . '<li class="divider"></li>'
        . '<li><a href="http://www.phantombot.net/threads/command-list-by-script.13/" target="_blank" role="button"><span class="fa fa-question-circle"></span>&nbsp;PhantomBot Commands ist</a></li>'
        . '<li><a href="http://www.phantombot.net" target="_blank"><span class="fa fa-question-circle" role="button"></span>&nbsp;PhantomBot Forums</a></li>';
  }

  $renderedMenu .= '</ul></li>';
}

//if ($hostHandlerActive == 1) {
//  $NOHosts = $functions->getIniValueByKey('stream_info.ini', 'hosts_amount');
//}
if ($subscribeHandlerActive == 1) {
  $subscribers = $functions->getIniArray('subscribed.ini');
  $NOSubscribers = 0;
  foreach ($subscribers as $subActive) {
    $NOSubscribers += ($subActive == 1 ? 1 : 0);
  }
}
?>
<!DOCTYPE html>
<html>
<head lang="en">
  <meta charset="UTF-8">
  <title></title>
  <link href="app/css/<?= $dataStore->getVar('misc', 'theme', 'style_dark') ?>.css"
        rel="stylesheet" type="text/css"/>
  <link href="app/css/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <link rel="icon" href="favicon.ico" type="image/x-icon"/>
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
  <script src="//code.jquery.com/jquery-1.11.3.min.js" type="text/javascript"></script>
  <script src="//code.jquery.com/ui/1.11.3/jquery-ui.min.js" type="text/javascript"></script>
  <script src="app/js/jquery.ui-contextmenu.min.js" type="text/javascript"></script>
  <script src="app/js/date.min.js" type="text/javascript"></script>
  <script src="app/js/app.min.js" type="text/javascript"></script>
  <script src="app/js/tooltip.min.js" type="text/javascript"></script>
  <script src="app/js/switch-toggle.min.js" type="text/javascript"></script>
</head>
<body>
<div id="page-wrapper" class="chat-responsive">
  <nav class="navbar navbar-default">
    <div class="container-fluid">
      <div class="navbar-header">
        <a class="navbar-brand" nohref>
          <img alt="PhantomBot Web Panel" src="app/content/static/logo-small.png" role="button"
               onclick="loadPartFromStorage()"/>
          <span class="panel-version text-muted">version <?= $dataStore->getVar('misc', 'currentVersion') ?></span>
        </a>
      </div>
      <ul class="nav navbar-nav">
        <li id="menu-parent-dashboard">
          <a nohref onclick="openPart('static/dashboard.php')" role="button">Dashboard</a>
        </li>
        <?= $renderedMenu ?>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li>
          <a nogref onclick="openPart('extras/panel-users.php')" role="button">Hi <span id="current-user"></span></a>
        </li>
        <li>
          <a nohref onclick="logOut()" role="button">Logout</a>
        </li>
      </ul>
    </div>
    <div class="container-fluid">
      <ul id="favorites-menu" class="nav navbar-nav">
        <?= $templates->addTooltip('<li class="favorites-menu-icon"><span class="fa fa-star-half-empty"></span></li>', 'Favorites',
            ['position' => \PBPanel\Util\ComponentTemplates::TOOLTIP_POS_LEFT, 'offsetX' => 10, 'offsetY' => 21, 'appendToBody' => true]) ?>
      </ul>
    </div>
  </nav>
  <div class="panel panel-primary">
    <div class="panel-heading">
      <h3 class="panel-title">
        <?= $dataStore->getVar('connector', 'botName') ?> on channel <?= $dataStore->getVar('connector', 'channelOwner') ?>
        <?= str_repeat('<span class="pull-right info-banner-space-left">&nbsp;</span>', 3) ?>
        <?= $templates->streamInfoBanner($NOSubscribers, 'dollar', 'warning', 'Subscriber Count', '', ($NOSubscribers > -1)) ?>
        <?= '' /* $templates->streamInfoBanner($NOHosts, 'forward', 'info', 'Host Count', 'stream-hosts', ($NOHosts > -1))*/ ?>
        <?= $templates->streamInfoBanner('NA', 'heartbeat', 'danger', 'Follower Count', 'stream-followers') ?>
        <?= $templates->streamInfoBanner('NA', 'users', 'success', 'Viewer Count', 'stream-viewer-count') ?>
        <?= $templates->streamInfoBanner('Offline', 'rss', 'info', 'Stream Status', 'stream-status') ?>
        <?= (!$isBotOnline ? $templates->streamInfoBanner('Bot Offline', 'exclamation-circle', 'danger',
            '<span class="text-danger">Could not contact the bot.</span><br />Make sure it\'s running and the HTTP server is active.') : '') ?>
      </h3>
    </div>
    <div class="panel-body">
      <div>
        <span class="fa fa-desktop"></span> <span id="stream-title" class="text-muted">NA <a nohref
                                                                                             onclick="loadChannelData(true)">Retry</a></span>
      </div>
      <div>
        <span class="fa fa-gamepad"></span> <span id="stream-game" class="text-muted">NA</span>
      </div>
    </div>
  </div>
  <div id="part-window"></div>
  <div class="panel panel-default page-footer">
    <div class="panel-heading">
      PhantomBot Control Panel
      <small><?= $dataStore->getVar('misc', 'currentVersion') ?></small>
      &#xFF0F; <a href="//juraji.nl" target="_blank">juraji</a> &copy;<?= date('Y') ?>
      &#xFF0F; Compatible with <a href="//www.phantombot.net/" target="_blank">PhantomBot <?= $dataStore->getVar('misc', 'pbCompat') ?></a>
    </div>
  </div>
</div>
<div id="chat-sidebar" class="chat-responsive">
  <iframe id="chat-iframe" src="" scrolling="no"></iframe>
</div>
<div id="general-alert" class="alert"></div>
<div id="music-player-controls">
  <?php require_once(\PBPanel\AppLoader::getBaseDir() . '/app/parts/static/music-player-controls.php'); ?>
</div>
</body>
</html>
