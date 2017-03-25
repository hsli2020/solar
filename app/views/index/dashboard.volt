{% extends "layouts/base.volt" %}

{% block main %}
<style type="text/css">
  table, th, td { border: 1px solid #ddd; }
  #snapshot th { text-align: center; }
  #snapshot td { text-align: right; }
  #snapshot tr td:first-child{ text-align: left; }
</style>

{%- macro tablecell(row, key, align) %}
  {%- set classes = align %}
  {%- if row['error'][key] is defined %}
    {%- set classes = classes ~ " w3-deep-orange" %}
  {%- endif %}
  <td class="{{ classes }}">{{ row[key] }}</td>
{% endmacro %}

<div class="w3-container">
<table id="snapshot" class="w3-table w3-white w3-bordered w3-border">
<tr>
  <th style="vertical-align: middle;">Site</th>
  <th style="vertical-align: middle;">GC PR</th>
  <th>Current Power<br>(kW)</th>
  <th>Irradiance<br>(W/m<sup>2</sup>)</th>
  <th>Inverters<br>Generating</th>
  <th>Devices<br>Communicating</th>
  <th>Data Received<br>(Time Stamp)</th>
</tr>
{% for row in data %}
<tr>
  {{ tablecell(row, 'project_name',          '') }}
  {{ tablecell(row, 'GCPR',                  '') }}
  {{ tablecell(row, 'current_power',         '') }}
  {{ tablecell(row, 'irradiance',            '') }}
  {{ tablecell(row, 'inverters_generating',  'w3-center') }}
  {{ tablecell(row, 'devices_communicating', 'w3-center') }}
  {{ tablecell(row, 'last_com',              'w3-center') }}
</tr>
{% endfor %}
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
