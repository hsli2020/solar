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
  width: 25px;
  height: 25px;
  border: 1px solid gray;
  border-radius: 50%;
  background-color: white;
  vertical-align: middle;
  display: inline-block;
}
.green {
  background-color: lightgreen;
}
</style>

<div class="w3-container">
  <div class="w3-cell-row w3-padding w3-margin-bottom">
    <div class="w3-cell">
       <button class="w3-button w3-white w3-border" onclick="turnOn()">ON</button>
       <button class="w3-button w3-white w3-border" onclick="turnOff()">OFF</button>
       <button class="w3-button w3-white w3-border" onclick="pulse()">Pulse</button>
       <button class="w3-button w3-white w3-border" onclick="autoPulse(1)">Auto Pulse ON</button>
       <button class="w3-button w3-white w3-border" onclick="autoPulse(0)">Auto Pulse OFF</button>
<!--   <button class="w3-button w3-white w3-border" onclick="getState()">Check State</button> -->
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
  var curpic = 0;
  function latestPicture() {
    var url = '/ajax/latestpic/' + curpic;
    $.get(url, function(res) {
      curpic = res.picture.id;
      $("#pic img").attr('src', '/picture/show/' + curpic);
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
  window.onload = autoRefresh(3000);
{% endblock %}
