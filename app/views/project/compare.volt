{% extends "layouts/base.volt" %}

{% block main %}
<div class="w3-container w3-row-padding w3-margin-bottom">

<form method="POST">
<div class="w3-margin-bottom">
  <input class="datepicker" name="startTime" required type="text" placeholder="Start time" value="{{ startTime }}">
  <input class="datepicker" name="endTime" required type="text" placeholder="End time" value="{{ endTime }}">

  <select id="interval-list" name="interval" class="w3-margin-right">
    <option value="0">Select Interval</option>
    {% for val, str in intervals %}
    <option value="{{ val }}" {% if val==interval %}selected{% endif %}>{{ str }}</option>
    {% endfor %}
  </select>

  <label><input type="checkbox" name="nozero" value="1" {%if nozero %}checked{% endif %}> Hide rows when kw=0</input></label>

  <input type="submit" value="Refresh" style="margin-left: 20px;">
</div>

<table id="report" class="w3-table w3-white w3-bordered w3-border w3-centered">
<tr>
  <th rowspan="3" class="vcenter">Time</th>

  <th colspan="3">
    <select id="project1-list" name="project1" class="w3-margin-right">
      <option value="0" selected>Select Project</option>
      {% for project in projects %}
      <option value="{{ project.id }}" {% if project.id==project1 %}selected{% endif %}>{{ project.name }}</option>
      {% endfor %}
    </select>
  </th>

  <th colspan="3">
    <select id="project2-list" name="project2" class="w3-margin-right">
      <option value="0" selected>Select Project</option>
      {% for project in projects %}
      <option value="{{ project.id }}" {% if project.id==project2 %}selected{% endif %}>{{ project.name }}</option>
      {% endfor %}
    </select>
  </th>

  <th colspan="3">
    <select id="project3-list" name="project3" class="w3-margin-right">
      <option value="0" selected>Select Project</option>
      {% for project in projects %}
      <option value="{{ project.id }}" {% if project.id==project3 %}selected{% endif %}>{{ project.name }}</option>
      {% endfor %}
    </select>
  </th>
</tr>

<tr>
  <th>Inverter</th>
  <th>EnvKit</th>
  <th>GenMeter</th>

  <th>Inverter</th>
  <th>EnvKit</th>
  <th>GenMeter</th>

  <th>Inverter</th>
  <th>EnvKit</th>
  <th>GenMeter</th>
</tr>

<tr>
  <th>KW</th>
  <th>IRR</th>
  <th>KWH</th>

  <th>KW</th>
  <th>IRR</th>
  <th>KWH</th>

  <th>KW</th>
  <th>IRR</th>
  <th>KWH</th>
</tr>

{% for time, row in data %}
<tr>
  <td>{{ time }}</td>

  <td>{{ row['project1']['kw'] }}</td>
  <td>{{ row['project1']['irr'] }}</td>
  <td>{{ row['project1']['kwh'] }}</td>

  <td>{{ row['project2']['kw'] }}</td>
  <td>{{ row['project2']['irr'] }}</td>
  <td>{{ row['project2']['kwh'] }}</td>

  <td>{{ row['project3']['kw'] }}</td>
  <td>{{ row['project3']['irr'] }}</td>
  <td>{{ row['project3']['kwh'] }}</td>
</tr>
{% endfor %}
</table>

{% if data is empty %}
<p>Please select start time, end time, interval, and project 1-3, then click "Refresh".</p>
{% endif %}

</form>
</div>
{% endblock %}

{% block csscode %}
  table, th, td { border: 1px solid #ddd; }
  th.vcenter { vertical-align: middle; }
  #date-list, #interval-list { width: 10em; }
  #project1-list, #project2-list, #project3-list { width: 100%; border: none; }
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
