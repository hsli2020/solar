{% extends "layouts/base.volt" %}

{% block main %}
<style type="text/css">
  table, th, td { border: 1px solid #ddd; vertical-align: middle; }
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
<span class="w3-margin-right">Select Month: </span>
<select id="month-list" style="width: 10em;">
{% for m in monthList %}
  <option value="{{ m }}"{% if m == month %}selected{% endif %}>{{ m }}</option>
{% endfor %}
</select>
</div>
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
</div>
{% endblock %}

{% block jscode %}
{% endblock %}

{% block domready %}
$('#month-list').change(function() {
    window.location = '/report/monthly/' + $(this).val();
})
{% endblock %}
