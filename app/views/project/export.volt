{% extends "layouts/base.volt" %}

{% block main %}
<style type="text/css">
  ul#breadcrumb { list-style: none; margin: 0; padding: 0; }
  ul#breadcrumb li { display: inline; }
</style>
<div class="container">
  <div class="w3-row-padding w3-margin-bottom">
    <div class="w3-container w3-light-grey w3-padding-4 w3-small w3-text-grey">
      <ul id="breadcrumb">
        <li><i class="fa fa-home w3-small"></i></li>
        <li>Home</li>
        <li>&nbsp; &#10095; &nbsp;</li>
        <li>Welcome</li>
        <li>&nbsp;  &#10095; &nbsp;</li>
        <li>{{ today }}</li>
      </ul>
    </div>
  </div>
  <div style="display: block;margin: 0 auto;width: 500px;">
      <form class="w3-container" method="POST">
        <div class="w3-section">
          <div class="w3-row-padding">
            <div class="w3-third w3-padding-16">
              <label><b>Project</b></label>
            </div>
            <div class="w3-twothird w3-padding-8">
              <select class="w3-select w3-border" name="project">
                <option value="0" selected>Select Project</option>
                {% for project in projects %}
                <option value="{{ project.id }}">{{ project.name }}</option>
                {% endfor %}
              </select>
            </div>
          </div>

          <div class="w3-row-padding">
            <div class="w3-third w3-padding-16">
              <label><b>Interval</b></label>
            </div>
            <div class="w3-twothird w3-padding-8">
              <select class="w3-select w3-border" name="interval">
                <option value="" selected>Select Interval</option>
                <option value="1">1 Minute</option>
                <option value="15">15 Minute (average)</option>
                <option value="60">1 Hour (average)</option>
                <option value="daily" disabled>Daily (total)</option>
                <option value="monthly" disabled>Monthly (total)</option>
              </select>
            </div>
          </div>

          <div class="w3-row-padding">
            <div class="w3-third w3-padding-16">
              <label><b>Start Date</b></label>
            </div>
            <div class="w3-twothird w3-padding-8">
              <input class="w3-input w3-border datepicker" name="start-time" required type="text">
            </div>
          </div>

          <div class="w3-row-padding">
            <div class="w3-third w3-padding-16">
              <label><b>End Date</b></label>
            </div>
            <div class="w3-twothird w3-padding-8">
              <input class="w3-input w3-border datepicker" name="end-time" required type="text">
            </div>
          </div>

          <div class="w3-row-padding">
            <div class="w3-third w3-padding-16">
              <label><b>Devices</b></label>
            </div>

            <div class="w3-twothird w3-padding-8">
              <input class="w3-check" type="checkbox" checked="checked" name="inverters" value="1">
              <label>Export Inverter Data</label><br>

              <input class="w3-check" type="checkbox" checked="checked" name="genmeters" value="1">
              <label>Export GenMeter Data</label><br>

              <input class="w3-check" type="checkbox" checked="checked" name="envkits" value="1">
              <label>Export EnvKit Data</label><br>
            </div>
          </div>

          <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}"/>

          <button class="w3-btn-block w3-indigo w3-section w3-padding" type="submit">Export/Download Data</button>
        </div>
      </form>
  </div>
</div>
{% endblock %}

{% block cssfile %}
  {{ stylesheet_link("/pickadate/themes/classic.css") }}
  {{ stylesheet_link("/pickadate/themes/classic.date.css") }}
{% endblock %}

{% block jsfile %}
  {{ javascript_include("/pickadate/picker.js") }}
  {{ javascript_include("/pickadate/picker.date.js") }}
{% endblock %}

{% block domready %}
  $('.datepicker').pickadate({format: 'yyyy-mm-dd'});
{% endblock %}
