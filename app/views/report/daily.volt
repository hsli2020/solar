{% extends "layouts/base.volt" %}

{% block main %}
<table id="report" class="w3-table w3-white w3-bordered w3-border w3-centered">
<tr>
  <th></th>
  <th colspan="2"></th>
  <th colspan="2">Budget Production</th>
  <th colspan="2">Month-to-Date</th>
  <th colspan="3">Daily Energy Production</th>
  <th colspan="3">Month-to-date Performance</th>
</tr>
<tr>
  <th rowspan="2">No.</th>
  <th rowspan="2">Project Name</th>
  <th rowspan="2">Date</th>
  <th colspan="2">Capacity<br>(kW)</th>
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
{% endblock %}

{% block jscode %}
{% endblock %}

{% block domready %}
{% endblock %}
