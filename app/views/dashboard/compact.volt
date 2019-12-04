{% extends "layouts/base.volt" %}

{% block main %}
<style type="text/css">
  .title { background-color: #c0c0c0; }
  .w3-col:hover .title { background-color: lightblue; }
  .w3-col:hover .reading { background-color: lightcyan; }
</style>

{%- macro cell(row, key, unit) %}
  {%- set classes = '' %}
  {# if row['error'][key] is defined #}
    {#- set classes = 'w3-' ~ row['error'][key] #}
  {# endif #}
  <div class="w3-container w3-center w3-cell w3-padding {{ classes }}">{{ row[key] }} {{ unit }}</div>
{% endmacro %}

<div class="w3-container">
<div class="w3-row-padding" style="margin:0 -16px 10px">
{% set count = 0 %}
{% set x = 6 %}
{% for row in data['rows'] %}
  <div class="w3-col m2">
    <div class="w3-border w3-light-grey w3-center">

    {% set bg = "" %}
    {% if (row['error']['GCPR'] is defined AND row['error']['GCPR'] == 'red') %}
    {%   set bg = "w3-red" %}
    {% endif %}
    {% if (row['error']['inverters_generating'] is defined AND row['error']['inverters_generating'] == 'red') %}
    {%   set bg = "w3-red" %}
    {% endif %}

    <div class="w3-container w3-center w3-padding {{ bg }} title">
      <a href="/project/detail/{{ row['project_id'] }}" target="_blank"><b>{{ row[ 'project_name'] }}</b></a>
      <a href="/project/chart/{{ row['project_id'] }}" target="_blank" class="w3-right"><i class="fa fa-bar-chart"></i></a>
      <span class="w3-right">{{ row['project_size_ac'] }} kW&nbsp;&nbsp;</span>
      {% if row['camera'] is not empty or row['project_id'] == 9 %}
        <a href="/project/camera/{{ row['project_id'] }}" target="_blank" class="w3-left"><i class="fa fa-camera"></i></a>
      {% endif %}
    </div>
    <div class="w3-cell-row reading">
      {{ cell(row, 'GCPR', '') }}
      {{ cell(row, 'current_power', 'kW') }}
      {{ cell(row, 'irradiance', 'W/m<sup>2</sup>') }}
    </div>

    </div>
  </div>
  {% set count += 1 %}
  {% if count%x == 0 %}
</div>
<div class="w3-row-padding" style="margin:0 -16px 10px">
  {% endif %}
{% endfor %}

{% if count%x != 0 %}
  </div>
{% endif %}

</div>
{% endblock %}

{% block jscode %}
  function AutoRefresh(t) {
    setTimeout("location.reload(true);", t);
  }
  window.onload = AutoRefresh(1000*60*1);
{% endblock %}

{% block domready %}
{% endblock %}
