/**
 * music-player.js
 * Created with PhpStorm
 * User: Robin | Juraji
 * Date: 3 dec 2015
 * Time: 04:16
 */

var display,
    audio,
    sfxHistory,
    connection;

$(window).ready(function () {
  display = $('#sfx-display');
  sfxHistory = $('#sfx-history');
  if (sfxEnabled) {
    connection = new WebSocket('ws://' + botAddress);
  } else {
    display.html($('<span class="text-danger">Sfx is disabled!</span>'));
    sfxHistory.html($('<p class="text-warning">Enable Sfx in the Phantombot Webpanel by going to Extras->Sfx, toggle "Toggle Sfx" and refresh this page.</p>'));
  }

  connection.onmessage = function (e) {
    var data = e.data.split('|'),
        now = new Date();

    if (data[0].match(/^CommandEvent.*/) && sfxCommands[data[1]]) {
      //noinspection JSUnresolvedFunction
      audio = new Audio(sfxCommands[data[1]]);
      audio.addEventListener('play', function () {
        display.text('Playing "' + sfxCommands[data[1]] + '" for !' + data[1]);
      });

      audio.addEventListener('ended', function () {
        display.text('Waiting for commands...');
      });

      audio.play();

      sfxHistory.append($('<div>[' + now.toLocaleDateString('en-GB').replace(/\s[0-9]{4}/, '').replace(/([a-z]{3})[a-z]+/i, '$1') + ' '
          + now.toLocaleTimeString() + '] Played <span class="text-success">!' + data[1] + '</span> <span class="text-muted">' + sfxCommands[data[1]] + '</span></div>'));
    }
  };
});