{% extends "layouts/base.volt" %}

{% block main %}
<div class="w3-container w3-row-padding w3-margin-bottom">

<form method="POST">
<div class="w3-margin-bottom">
  <label>Date: </label>
  <select id="date-list" name="date" class="w3-margin-right">
    <option value="0">Select Date</option>
    {% for dt in dateList %}
    <option value="{{ dt }}" {% if dt==date %}selected{% endif %}>{{ dt }}</option>
    {% endfor %}
  </select>

  <label>Interval:</label>
  <select id="interval-list" name="interval" class="w3-margin-right">
    <option value="0">Select Interval</option>
    {% for val, str in intervals %}
    <option value="{{ val }}" {% if val==interval %}selected{% endif %}>{{ str }}</option>
    {% endfor %}
  </select>

  <input type="submit" value="Refresh">
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

{% for row in data %}
<tr>
  <td>00:00</td>

  <td>111</td>
  <td>111</td>
  <td>111</td>

  <td>222</td>
  <td>222</td>
  <td>222</td>

  <td>333</td>
  <td>333</td>
  <td>333</td>
</tr>
{% endfor %}
</table>

{% if data is empty %}
<p>Please select date, interval, and project 1-3, then click "Refresh".</p>
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
