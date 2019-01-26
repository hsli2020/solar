{% extends "layouts/base.volt" %}

{% block main %}
<style type="text/css">
</style>

<div class="w3-container">
<button onclick="getState()">GetState</button>
<button onclick="turnOn()">Turn On</button>
<button onclick="turnOff()">Turn Off</button>
<pre></pre>
</div>
{% endblock %}

{% block jscode %}
  function getState() {
      var url = '/snowwiper/getstate';
      $.get(url, function(res) {
          $("pre").html(res);
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
{% endblock %}

{% block domready %}
  function AutoRefresh(t) {
    setTimeout("location.reload(true);", t);
  }
  window.onload = AutoRefresh(1000*60*1);
{% endblock %}
