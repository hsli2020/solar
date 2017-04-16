{% extends "layouts/base.volt" %}

{% block main %}
<table id="report" class="w3-table w3-white w3-bordered w3-border w3-centered">
<tr>
  <th rowspan="2">Project</th>
  <th rowspan="2">Month-Year</th>
  <th colspan="2">Insolation</th>
  <th colspan="3">Energy Production</th>
  <th colspan="3">Performance</th>
</tr>
<tr>
  <th>Actual<br>[kWh/m<sup>2</sup>]</th>
  <th>Reference<br>[kWh/m<sup>2</sup>]</th>
  <th>Measured<br>[kWh]</th>
  <th>Expected<br>[kWh]</th>
  <th>Budget<br>[kWh]</th>
  <th>Actual<br>Budget</th>
  <th>Actual<br>Expected</th>
  <th>Weather<br>Performance</th>
</tr>

{% for data in report %}
<tr>
  <td>{{ data['Project_Name'] }}</td>
  <td>{{ data['Date'] }}</td>
  <td>{{ data['Insolation_Actual'] }}</td>
  <td>{{ data['Insolation_Reference'] }}</td>
  <td>{{ data['Energy_Measured'] }}</td>
  <td>{{ data['Energy_Expected'] }}</td>
  <td>{{ data['Energy_Budget'] }}</td>
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
