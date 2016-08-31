{% extends "layouts/base.volt" %}

{% block main %}
<style type="text/css">
  table, th, td { border: 1px solid #ddd; }
</style>
<div class="w3-container">
<table class="w3-table w3-white w3-bordered w3-border">
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
<tr>
  <td>125 Bermondsey</td>
  <td>{{ data[1]['EnvKit']['time'] }}</td>
  <td>{{ data[1]['Inverter'][1] }}</td>
  <td>{{ data[1]['Inverter'][2] }}</td>
  <td>{{ data[1]['Inverter'][3] }}</td>
  <td>{{ data[1]['EnvKit']['IRR'] }}</td>
  <td>{{ data[1]['EnvKit']['OAT'] }}</td>
  <td>{{ data[1]['EnvKit']['PANELT'] }}</td>
  <td>{{ data[1]['GenMeter']['kva'] }}</td>
  <td>{{ data[1]['GenMeter']['vln_a'] }}</td>
  <td>{{ data[1]['GenMeter']['vln_b'] }}</td>
  <td>{{ data[1]['GenMeter']['vln_c'] }}</td>
</tr>
<tr>
  <td>1935 Drew</td>
  <td>{{ data[2]['EnvKit']['time'] }}</td>
  <td>{{ data[2]['Inverter'][1] }}</td>
  <td>{{ data[2]['Inverter'][2] }}</td>
  <td>{{ data[2]['Inverter'][3] }}</td>
  <td>{{ data[2]['EnvKit']['IRR'] }}</td>
  <td>{{ data[2]['EnvKit']['OAT'] }}</td>
  <td>{{ data[2]['EnvKit']['PANELT'] }}</td>
  <td>{{ data[2]['GenMeter']['kva'] }}</td>
  <td>{{ data[2]['GenMeter']['vln_a'] }}</td>
  <td>{{ data[2]['GenMeter']['vln_b'] }}</td>
  <td>{{ data[2]['GenMeter']['vln_c'] }}</td>
</tr>
</table>
</div>
{% endblock %}

{% block jscode %}
{% endblock %}

{% block domready %}
{% endblock %}
