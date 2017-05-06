{% extends "layouts/base.volt" %}

{% block main %}
<style type="text/css">
  table, th, td { border: 1px solid #ddd; }
  #report th { text-align: center; vertical-align: middle; }
  #report td { text-align: right; vertical-align: middle; }
  #report tr td:first-child{ text-align: left; }
  .w3-border { border: 5px solid #eee !important; }
  #statsbox .icon {
    font-size: 80px;
    color: rgba(0, 0, 0, 0.09);
    line-height: 0;
  }
  ul#breadcrumb { list-style: none; margin: 0; padding: 0; }
  ul#breadcrumb li { display: inline; }
</style>

<div id="statsbox" class="w3-row-padding w3-margin-bottom">
  <div class="w3-row-padding">
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
</div>

<div class="w3-container">
<div class="w3-margin-bottom">
<span class="w3-margin-right">Select Date: </span>
<select id="date-list" style="width: 10em;">
{% for d in dateList %}
  <option value="{{ d }}"{% if d == date %}selected{% endif %}>{{ d }}</option>
{% endfor %}
</select>
</div>
<table id="report" class="w3-table w3-white w3-bordered w3-border w3-centered">
<tr>
  <th rowspan="3">No.</th>
  <th rowspan="3">Project Name</th>
  <th rowspan="3">Date</th>
  <th colspan="2" rowspan="2">Capacity<br>(kW)</th>
  <th colspan="2">Budget Production</th>
  <th colspan="2">Month-to-Date</th>
  <th colspan="3">Daily Energy Production</th>
  <th colspan="3">Month-to-date Performance</th>
</tr>
<tr>
  <th>Monthly Budget</th>
  <th>IE POA Insolation</th>
  <th>Total Energy</th>
  <th>Total Insolation</th>
  <th>Daily Expected</th>
  <th>Measured Production</th>
  <th>Measured POA Insolation</th>
  <th>Actual /Budget</th>
  <th>Actual /Expected</th>
  <th>Weather Performance</th>
</tr>
<tr>
  <th>AC</th>
  <th>DC</th>
  <th>kWh</th>
  <th>kWh/m<sup>2</sup></th>
  <th>kWh</th>
  <th>kWh/m<sup>2</sup></th>
  <th>kWh</th>
  <th>kWh</th>
  <th>kWh/m<sup>2</sup></th>
  <th>%</th>
  <th>%</th>
  <th>%</th>
</tr>

{% for data in report %}
<tr>
  <td>{{ loop.index }}</td>
  <td>{{ data['Project_Name'] }}</td>
  <td>{{ data['Date'] }}</td>
  <td>{{ data['Capacity_AC'] }}</td>
  <td>{{ data['Capacity_DC'] }}</td>
  <td>{{ data['Monthly_Budget'] }}</td>
  <td>{{ data['IE_Insolation'] }}</td>
  <td>{{ data['Total_Energy'] }}</td>
  <td>{{ data['Total_Insolation'] }}</td>
  <td>{{ data['Daily_Expected'] }}</td>
  <td>{{ data['Measured_Production'] }}</td>
  <td>{{ data['Measured_Insolation'] }}</td>
  <td>{{ data['Actual_Budget'] }}</td>
  <td>{{ data['Actual_Expected'] }}</td>
  <td>{{ data['Weather_Performance'] }}</td>
</tr>
{% endfor %}
</table>
</div>
{% endblock %}

{% block jscode %}
{% endblock %}

{% block domready %}
$('#date-list').change(function() {
    window.location = '/report/daily/' + $(this).val();
})
{% endblock %}
