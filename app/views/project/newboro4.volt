{% extends "layouts/base.volt" %}

{% block main %}
<style type="text/css">
img { width: 90%; margin-left: auto; margin-right: auto; display: block; }
#pic { border: 1px solid #ccc; padding: 20px; margin-bottom: 20px; }
#bar { padding: 10px; margin-bottom: 25px; }
</style>

<div class="w3-container">
  <div id="pic"><img src="/picture/show/4313"></div>
  <div id="bar">
    <button onclick="turnOn()">ON</button>
    <button onclick="turnOff()">OFF</button>
    <button onclick="pulse()">Pulse</button>
    <button onclick="autoPulse(1)">Auto Pulse ON</button>
    <button onclick="autoPulse(0)">Auto Pulse OFF</button>
    <button onclick="getState()">Check State</button>
  </div>
</div>
{% endblock %}

{% block jscode %}
  function getState() {
      var url = '/snowwiper/getstate';
      $.get(url, function(res) {
          $("pre").html(res);
          //var data = JSON.parse(res);
          //$("pre").html(data.relaystate);
      });
  }

  function turnOn() {
      var url = '/snowwiper/turnon';
      $.get(url, function(res) {
          //$("pre").html(res);
      });
  }

  function turnOff() {
      var url = '/snowwiper/turnoff';
      $.get(url, function(res) {
          //$("pre").html(res);
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
          //$("pre").html(res);
      });
  }
{% endblock %}

{% block domready %}
  function latestPicture() {
      var url = '/ajax/latestpic/999';
      $.get(url, function(res) {
          $("#pic img").attr('src', '/picture/show/' + res.picture[0].id);
      });
  }
  function autoRefresh(t) {
      latestPicture();
      setInterval(latestPicture, t);
  }
  window.onload = autoRefresh(1000*5);
{% endblock %}
