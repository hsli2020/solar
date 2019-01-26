{% extends "layouts/base.volt" %}

{% block main %}
<style type="text/css">
</style>

<div class="w3-container">
<button onclick="turnOn()">ON</button>
<button onclick="turnOff()">OFF</button>
<button onclick="pulse()">Pulse</button>
<button onclick="autoPulse(1)">Auto Pulse ON</button>
<button onclick="autoPulse(0)">Auto Pulse OFF</button>
<button onclick="getState()">Check State</button>
<pre></pre>
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
          $("pre").html(res);
      });
  }

  function turnOff() {
      var url = '/snowwiper/turnoff';
      $.get(url, function(res) {
          $("pre").html(res);
      });
  }

  function pulse() {
      var url = '/snowwiper/pulse';
      $.get(url, function(res) {
          $("pre").html(res);
      });
  }

  function autoPulse(state) {
      var url = '/snowwiper/autopulse/' + state;
      $.get(url, function(res) {
          $("pre").html(res);
      });
  }
{% endblock %}

{% block domready %}
  function AutoRefresh(t) {
    setTimeout("location.reload(true);", t);
  }
  //window.onload = AutoRefresh(1000*60*1);
{% endblock %}
