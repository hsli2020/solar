{% extends "layouts/base.volt" %}

{% block main %}
<style type="text/css">
  table { border: 5px solid #eee !important; }
  table, th, td { border: 1px solid #ddd; }
  td a { text-decoration: none; font-weight: bold; }
  .w3-table td, .w3-table th { padding: 0; }
  .w3-table td:first-child, .w3-table th:first-child { padding: 0; }
  td:hover .title { background-color: lightblue; }
  td:hover .reading { background-color: lightcyan; }
</style>

{%- macro cell(row, key, unit) %}
  {%- set classes = '' %}
  {%- if row['error'][key] is defined %}
    {%- set classes = 'w3-' ~ row['error'][key] %}
  {%- endif %}
  <div class="w3-container w3-center w3-cell w3-padding {{ classes }}">{{ row[key] }} {{ unit }}</div>
{% endmacro %}

<div class="w3-container">
<table id="snapshot" class="w3-table w3-white w3-bordered w3-border">
{% set count = 0 %}
{% set x = 4 %}
<tr>
{% for row in data['rows'] %}
  <td>
    <div class="w3-container w3-center w3-padding title">
      <a href="/project/detail/{{ row['project_id'] }}" target="_blank">{{ row[ 'project_name'] }}</a>
      <a href="/project/chart/{{ row['project_id'] }}" target="_blank" class="w3-right"><i class="fa fa-bar-chart"></i></a>
      {% if row['camera'] is not empty or row['project_id'] == 9 %}
        <a href="/project/camera/{{ row['project_id'] }}" target="_blank" class="w3-right"><i class="fa fa-camera"></i>&nbsp;</a>
      {% endif %}
    </div>
    <div class="w3-cell-row reading">
      {{ cell(row, 'GCPR', '') }}
      {{ cell(row, 'current_power', 'kW') }}
      {{ cell(row, 'irradiance', 'W/m<sup>2</sup>') }}
    </div>
  </td>
  {% set count += 1 %}
  {% if count%x == 0 %}
    </tr>
    <tr>
  {% endif %}
{% endfor %}

{% for index in 1..(x-count%x) %}<td>&nbsp;</td>{% endfor %}
{% if count%x != 0 %}</tr>{% endif %}

</table>
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
