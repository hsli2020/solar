{% extends "layouts/base.volt" %}

{% block main %}
<style type="text/css">
img {
  width: 90%; margin-left: auto; margin-right: auto; display: block;
}
#pic {
  border: 1px solid #ccc; padding: 20px;
}
.dot {
  height: 25px;
  width: 25px;
  border: 1px solid gray;
  border-radius: 50%;
  display: inline-block;
}
.green {
  background-color: lightgreen;
}
</style>

<div class="w3-container">
  <div class="w3-cell-row w3-padding w3-border w3-margin-bottom">
    <div class="w3-cell">
       <button onclick="turnOn()">ON</button>
       <button onclick="turnOff()">OFF</button>
       <button onclick="pulse()">Pulse</button>
       <button onclick="autoPulse(1)">Auto Pulse ON</button>
       <button onclick="autoPulse(0)">Auto Pulse OFF</button>
       <button onclick="getState()">Check State</button>
    </div>
    <div class="w3-cell"><span>iWiper State: </span><span id="wiper" class="dot"></span></div>
    <div class="w3-cell"><span>AutoPulse State: </span><span id="autopulse" class="dot"></span></div>
  </div>
  <div id="pic"><img src=""></div>
</div>
{% endblock %}

{% block jscode %}
  function getState() {
    var url = '/snowwiper/getstate';
    $.get(url, function(res) {
      //var data = JSON.parse(res);
      //$("pre").html(data.relaystate);
    });
  }

  function turnOn() {
    var url = '/snowwiper/turnon';
    $.get(url, function(res) {
      updateRelayState(res.relaystate);
    });
  }

  function turnOff() {
    var url = '/snowwiper/turnoff';
    $.get(url, function(res) {
      updateRelayState(res.relaystate);
    });
  }

  function pulse() {
      var url = '/snowwiper/pulse';
      $.get(url, function(res) {
          //$("pre").html(res);
      });
  }

  function autoPulse(state) {
    var url = '/snowwiper/autopulse/' + state;
    $.get(url, function(res) {
      updateAutoPulseState(res.state);
    });
  }
{% endblock %}

{% block domready %}
  function latestPicture() {
    var url = '/ajax/latestpic/999';
    $.get(url, function(res) {
      $("#pic img").attr('src', '/picture/show/' + res.picture[0].id);
      updateRelayState(res.wiper.relaystate);
      updateAutoPulseState(res.autopulse.state);
    });
  }

  function updateRelayState(st) {
    if (st == 1) {
      $('#wiper').addClass('green');
    } else {
      $('#wiper').removeClass('green');
    }
  }

  function updateAutoPulseState(st) {
    if (st == 1) {
      $('#autopulse').addClass('green');
    } else {
      $('#autopulse').removeClass('green');
    }
  }

  function autoRefresh(t) {
    latestPicture();
    setInterval(latestPicture, t);
  }
  window.onload = autoRefresh(1000*5);
{% endblock %}
