{% extends "layouts/base.volt" %}

{% block main %}
<style type="text/css">
  table { border: 5px solid #eee !important; }
  table, th, td { border: 1px solid #ddd; }
  #snapshot th { text-align: center; vertical-align: middle; }
  #snapshot td { text-align: right; vertical-align: middle; }
  #snapshot tr td:first-child{ text-align: left; }
  #statsbox .numval { font-size: 24px; text-align: right; }
  #statsbox .label  { font-size: 12px; text-align: right; }
  #statsbox .icon {
    font-size: 80px;
    line-height: 0;
  }
  .bg-box1 { border: 5px solid #eee; }
  .bg-box2 { border: 5px solid #eee; }
  .bg-box3 { border: 5px solid #eee; }
  .bg-box4 { border: 5px solid #eee; }
</style>

<div id="statsbox" class="w3-row-padding w3-margin-bottom">
  <div class="w3-col" style="width:20%">
    <div class="w3-container bg-box1">
      <div class="w3-right w3-padding-12">
        <div class="numval">{{ data['total']['project_size_ac'] }}</div>
        <div class="label">Total Project Size KWAC</div>
      </div>
    </div>
  </div>
  <div class="w3-col" style="width:20%">
    <div class="w3-container bg-box1">
      <div class="w3-right w3-padding-12">
        <div class="numval">{{ data['rows'] | length }}</div>
        <div class="label">Total Number of Projects</div>
      </div>
    </div>
  </div>
  <div class="w3-col" style="width:20%">
    <div class="w3-container bg-box2">
      <div class="w3-right w3-padding-12">
        <div class="numval">{{ data['total']['current_power'] }}</div>
        <div class="label">Total Current Power</div>
      </div>
    </div>
  </div>
  <div class="w3-col" style="width:20%">
    <div class="w3-container bg-box3">
      <div class="w3-right w3-padding-12">
        <div class="numval">{{ data['total']['average_irradiance'] }}</div>
        <div class="label">Average Irradiance, w/m<sup>2</sup></div>
      </div>
    </div>
  </div>
  <div class="w3-col" style="width:20%">
    <div class="w3-container bg-box4">
      <div class="w3-right w3-padding-12">
        <div class="numval">{{ data['total']['performance'] }}</div>
        <div class="label">Production, Performance %</div>
      </div>
    </div>
  </div>
</div>

{%- macro tablecell(row, key, align) %}
  {%- set classes = align %}
  {%- if row['error'][key] is defined %}
    {%- set classes = classes ~ ' w3-' ~ row['error'][key] %}
  {%- endif %}
  <td class="{{ classes }}">{{ row[key] }}</td>
{% endmacro %}

<div class="w3-container">
<table id="snapshot" class="w3-table w3-white w3-bordered w3-border table-sortable">
<thead>
<tr>
  <th style="vertical-align: middle;" class="sortcol">Site</th>
  <th style="vertical-align: middle;">Chart</th>
  <th style="vertical-align: middle;" class="sortcol">GC PI</th>
  <th class="sortcol">Project Size<br>(AC)</th>
  <th class="sortcol">Current Power<br>(kW)</th>
  <th class="sortcol">Irradiance<br>(W/m<sup>2</sup>)</th>
  <th class="sortcol">Ambient<br>Temperature (C°)</th>
  <th class="sortcol">Inverters<br>Generating</th>
  <th class="sortcol">Devices<br>Communicating</th>
  <th class="sortcol">Data Received<br>(Time Stamp)</th>
</tr>
</thead>
<tbody>
{% for row in data['rows'] %}
<tr>
  <td>
    <a href="/project/detail/{{ row['project_id'] }}" target="_blank">{{ row[ 'project_name'] }}</a>

    {% if row['camera_link'] is not empty %}
      <a href="{{ row['camera_link'] }}" target="_blank" class="w3-right"><i class="fa fa-camera"></i></a>
    {% endif %}

    {% if host != 'GCS-AWS-New' %}
      {% if row['camera'] is not empty %}
        <a href="/project/camera/{{ row['project_id'] }}" target="_blank" class="w3-right"><i class="fa fa-camera"></i></a>
      {% endif %}

      {% if row['project_id'] == 45 %}
        {% if auth['id'] != 10 %} {# northwind is not allowed to see newboro4 #}
          <a href="/project/newboro4" target="_blank" class="w3-right"><i class="fa fa-camera"></i></a>
        {% endif %}
      {% endif %}
    {% endif %}
  </td>
  <td class="w3-center"><a href="/project/chart/{{ row['project_id'] }}" target="_blank"><i class="fa fa-bar-chart"></i></a></td>
  {{ tablecell(row, 'GCPR',                  '') }}
  {{ tablecell(row, 'project_size_ac',       'w3-center') }}
  {{ tablecell(row, 'current_power',         '') }}
  {{ tablecell(row, 'irradiance',            '') }}
  {{ tablecell(row, 'temperature',           'w3-center') }}
  {{ tablecell(row, 'inverters_generating',  'w3-center') }}
  {{ tablecell(row, 'devices_communicating', 'w3-center') }}
  {{ tablecell(row, 'last_com',              'w3-center') }}
</tr>
{% endfor %}
</tbody>
</table>
</div>
{% endblock %}

{% block jsfile %}
<script type="text/javascript" src="/js/script.js"></script>
{% endblock %}

{% block jscode %}
tableSortable();

function AutoRefresh(t) {
  setTimeout("location.reload(true);", t);
}
window.onload = AutoRefresh(1000*60*1);
{% endblock %}

{% block domready %}
{% endblock %}
