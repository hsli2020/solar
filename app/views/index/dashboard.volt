{% extends "layouts/base.volt" %}

{% block main %}
<style type="text/css">
  table, th, td { border: 1px solid #ddd; }
  #snapshot th { text-align: center; }
  #snapshot td { text-align: right; }
  #snapshot tr td:first-child{ text-align: left; }
  #statsbox .numval { font-size: 24px; text-align: right; }
  #statsbox .label  { font-size: 12px; text-align: right; }
  #statsbox .icon {
    font-size: 80px;
    color: rgba(0, 0, 0, 0.09);
    line-height: 0;
  }
  .bg-red  { background-color: #f56954 !important; }
  .bg-blue { background-color: #3c8dbc !important; }
  .bg-teal { background-color: #39cccc !important; }
  .bg-purple { background-color: #8F5E99 !important; }
  .w3-border { border: 5px solid #eee !important; }
  ul#breadcrumb { list-style: none; margin: 0; padding: 0; }
  ul#breadcrumb li { display: inline; }
</style>

<div id="statsbox" class="w3-row-padding w3-margin-bottom">

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

  <div class="w3-quarter">
    <div class="w3-container bg-blue w3-text-white">
      <div class="w3-left icon"><i class="fa fa-bar-chart"></i></div>
      <div class="w3-right w3-padding-12">
        <div class="numval">{{ data['total']['project_size_ac'] }}</div>
        <div class="label">Total Project Size KWAC</div>
      </div>
    </div>
  </div>
  <div class="w3-quarter">
    <div class="w3-container bg-teal w3-text-white">
      <div class="w3-left icon"><i class="fa fa-area-chart"></i></div>
      <div class="w3-right w3-padding-12">
        <div class="numval">{{ data['total']['current_power'] }}</div>
        <div class="label">Total Current Power</div>
      </div>
    </div>
  </div>
  <div class="w3-quarter">
    <div class="w3-container bg-purple w3-text-white">
      <div class="w3-left icon"><i class="fa fa-dashboard"></i></div>
      <div class="w3-right w3-padding-12">
        <div class="numval">{{ data['total']['average_irradiance'] }}</div>
        <div class="label">Average Irradiance, w/m<sup>2</sup></div>
      </div>
    </div>
  </div>
  <div class="w3-quarter">
    <div class="w3-container bg-red w3-text-white">
      <div class="w3-left icon"><i class="fa fa-line-chart"></i></div>
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
<table id="snapshot" class="w3-table w3-white w3-bordered w3-border">
<tr>
  <th style="vertical-align: middle;">Site</th>
  <th style="vertical-align: middle;">GC PI</th>
  <th>Project Size<br>(AC)</th>
  <th>Current Power<br>(kW)</th>
  <th>Irradiance<br>(W/m<sup>2</sup>)</th>
  <th>Inverters<br>Generating</th>
  <th>Devices<br>Communicating</th>
  <th>Data Received<br>(Time Stamp)</th>
</tr>
{% for row in data['rows'] %}
<tr>
  {{ tablecell(row, 'project_name',          '') }}
  {{ tablecell(row, 'GCPR',                  '') }}
  {{ tablecell(row, 'project_size_ac',       'w3-center') }}
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
