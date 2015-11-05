<?php
/**
 * music-player.php
 * Created with PhpStorm
 * User: Robin | Juraji
 * Date: 12 okt 2015
 * Time: 12:48
 */
require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . '/app/php/classes/Configuration.class.php';
$config = new Configuration();
$botControl = filter_input(INPUT_GET, 'botControl', FILTER_VALIDATE_BOOLEAN);
?>
<!DOCTYPE html>
<html>
<head lang="en">
  <meta charset="UTF-8">
  <title></title>
  <link href="/app/css/music-player.css" rel="stylesheet" type="text/css"/>
  <link rel="icon" href="/favicon.ico" type="image/x-icon"/>
  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"/>
  <script src="https://www.youtube.com/iframe_api"></script>
  <script>
    var botAddress = '<?= $config->botIp ?>',
        botControl = <?= ($botControl ? 'true' : 'false') ?>;
  </script>
  <script src="//code.jquery.com/jquery-1.11.3.min.js" type="text/javascript"></script>
  <script src="/app/js/rsocket.min.js"></script>
  <script src="/app/js/music-player.min.js"></script>
</head>
<body>
<div class="player-wrapper">
  <div id="player"></div>
</div>
<div class="info">
  <h3 class="title">Music on <a href="http://twitch.tv/<?= $config->channelOwner ?>"><?= $config->channelOwner ?></a></h3>
  Now playing: <span id="current-video-title">Waiting for next song...</span></div>
</body>
</html>