{% extends "layouts/base.volt" %}

{% block main %}
<div class="w3-container w3-row-padding w3-margin-bottom">

<form method="POST">
<div class="w3-margin-bottom">
  <input id="starttime" class="datepicker" name="startTime" required type="text" placeholder="Start time" value="{{ startTime }}">
  <input id="endtime" class="datepicker" name="endTime" required type="text" placeholder="End time" value="{{ endTime }}">

  <select id="interval-list" name="interval" class="w3-margin-right">
    <option value="0">Select Interval</option>
    {% for val, str in intervals %}
    <option value="{{ val }}" {% if val==interval %}selected{% endif %}>{{ str }}</option>
    {% endfor %}
  </select>

  <div class="w3-dropdown-hover w3-margin-left">
    <button type="button">Select Projects <i class="fa fa-caret-down"></i></button>
    <div class="w3-dropdown-content w3-border w3-padding" style="width:240px;overflow-y:scroll;height:25em;">
    {% for project in allProjects %}
      <div class="w3-bar-item">
        <input type="checkbox" name="projects[]" value="{{ project.id }}"
          {% if project.selected %}checked{% endif %}>
        <label>{{ project.name }}</label>
      </div>
    {% endfor %}
    </div>
  </div>

  <input type="submit" value="Refresh" class="w3-margin-left">
  <input type="submit" value="Export" class="w3-margin-left" name="export">
</div>
</form>

{% if data is not empty %}
<table id="report" class="w3-table w3-white w3-bordered w3-border w3-centered">
<tr>
  <th rowspan="3" class="vcenter">Time</th>

  {% for project in projects %}
    <th colspan="3">{{ allProjects[project].name }}</th>
  {% endfor %}
</tr>
<tr>
  {% for project in projects %}
    <th>Inverter</th>
    <th>EnvKit</th>
    <th>GenMeter</th>
  {% endfor %}
</tr>
<tr>
  {% for project in projects %}
    <th>KW</th>
    <th>IRR</th>
    <th>KWH</th>
  {% endfor %}
</tr>

{% for time, row in data %}
<tr>
  <td>{{ time }}</td>
  {% for prj, vals in row %}
    <td>{{ vals['kw'] }}</td>
    <td>{{ vals['irr'] }}</td>
    <td>{{ vals['kwh'] }}</td>
  {% endfor %}
</tr>
{% endfor %}
</table>
{% else %}
  <p>Please select start time, end time, interval, and projects, then click "Refresh".</p>
{% endif %}

</div>
{% endblock %}

{% block csscode %}
  table, th, td { border: 1px solid #ddd; }
  th.vcenter { vertical-align: middle; }
  #date-list, #interval-list { width: 10em; }
  #project1-list, #project2-list, #project3-list { width: 100%; border: none; }
{% endblock %}

{% block cssfile %}
  {{ stylesheet_link("/datetimepicker/jquery.datetimepicker.min.css") }}
{% endblock %}

{% block jsfile %}
  {{ javascript_include("/datetimepicker/jquery.datetimepicker.full.min.js") }}
{% endblock %}

{% block domready %}
  $('.datepicker').datetimepicker({format: 'Y-m-d H:i', timepicker:true, step: 30});

  $('form').submit(function() {
    var valid = true;

    if ($('#starttime').val() == '') {
        $('#starttime').css({border: '1px solid red'});
        valid = false;
    }

    if ($('#endtime').val() == '') {
        $('#endtime').css({border: '1px solid red'});
        valid = false;
    }

    if ($('#interval-list').val() == '0') {
        $('#interval-list').css({border: '1px solid red'});
        valid = false;
    }

    if (!valid) {
        return false;
    }
  });
{% endblock %}
