{% extends "layouts/base.volt" %}

{% block main %}
<style type="text/css">
  table, th, td { border: 1px solid #ddd; }
  #project th { text-align: center; }
  #project td { text-align: right; }
  #project tr td:first-child{ text-align: left; }
</style>

<div class="w3-container">
<table id="project" class="w3-table w3-white w3-bordered w3-border w3-centered">
<tr>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
  <td colspan="3" class="w3-center">Inverter(kW)</td>
  <td colspan="3" class="w3-center">Environmental Kit</td>
  <td colspan="4" class="w3-center">GenMeter</td>
</tr>
<tr>
  <th>Project</th>
  <th>Time</th>
  <th>Inverter 1</th>
  <th>Inverter 2</th>
  <th>Inverter 3</th>
  <th>IRR(w/m2)</th>
  <th>OAT</th>
  <th>PanelTemp</th>
  <th>kVa</th>
  <th>vin A</th>
  <th>vin B</th>
  <th>vin C</th>
</tr>
{% for row in data %}
<tr>
  <td>{{ row['name'] }}</td>
  <td>{{ row['EnvKit']['time'] }}</td>
  {% if row['Inverter'][0] is defined %}<td>{{ row['Inverter'][0]['kw'] }}</td>{% else %}<td>&nbsp;</td>{% endif %}
  {% if row['Inverter'][1] is defined %}<td>{{ row['Inverter'][1]['kw'] }}</td>{% else %}<td>&nbsp;</td>{% endif %}
  {% if row['Inverter'][2] is defined %}<td>{{ row['Inverter'][2]['kw'] }}</td>{% else %}<td>&nbsp;</td>{% endif %}
  <td>{{ row['EnvKit']['IRR'] }}</td>
  <td>{{ row['EnvKit']['OAT'] }}</td>
  <td>{{ row['EnvKit']['PANELT'] }}</td>
  <td>{{ row['GenMeter']['kva'] }}</td>
  <td>{{ row['GenMeter']['vinA'] }}</td>
  <td>{{ row['GenMeter']['vinB'] }}</td>
  <td>{{ row['GenMeter']['vinC'] }}</td>
</tr>
{% endfor %}
</table>
</div>
{% endblock %}

{% block jscode %}
{% endblock %}

{% block domready %}
{% endblock %}
