{% extends "layouts/base.volt" %}

{% block main %}
<style type="text/css">
  table, th, td { border: 1px solid #ddd; }
  #snapshot th { text-align: center; }
  #snapshot td { text-align: right; }
  #snapshot tr td:first-child{ text-align: left; }
</style>

{%- macro tablecell(row, key) %}
  <td{%- if row['error'][key] is defined %} class="w3-deep-orange"{%- endif %}>{{ row[key] }}</td>
{%- endmacro %}

<div class="w3-container">
<table id="snapshot" class="w3-table w3-white w3-bordered w3-border">
<tr>
  <th style="vertical-align: middle;">Site</th>
  <th style="vertical-align: middle;">GC PR</th>
  <th>Current<br>Power</th>
  <th>Irradiance<br>(W/m<sup>2</sup>)</th>
  <th>Inverters<br>Generating</th>
  <th>Devices<br>Communicating</th>
  <th>Last Com<br>(mins ago)</th>
</tr>
{% for row in data %}
<tr>
  {{ tablecell(row, 'project_name') }}
  {{ tablecell(row, 'GCPR') }}
  {{ tablecell(row, 'current_power') }}
  {{ tablecell(row, 'irradiance') }}
  {{ tablecell(row, 'inverters_generating') }}
  {{ tablecell(row, 'devices_communicating') }}
  {{ tablecell(row, 'last_com') }}
</tr>
{% endfor %}
</table>
</div>
{% endblock %}

{% block jscode %}
{% endblock %}

{% block domready %}
{% endblock %}
