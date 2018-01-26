{% extends "layouts/public.volt" %}

{% block main %}
<div class="container">
  <div class="w3-modal" style="display: block;">
    <div class="w3-modal-content w3-card-8 w3-padding" style="max-width:900px">
      <h2>Detailed Site Information</h2>

      <table class="w3-table compact">
        <tr>
          <td width="20%">Project Name:</td>
          <td width="80%">{{ details['project_name'] }}</td>
        </tr>
        <tr>
          <td>Month-Year</td>
          <td>{{ today }}</td>
        </tr>
        <tr>
          <td>Time:</td>
          <td>{{ now }}</td>
        </tr>
      </table>

      <table class="w3-table w3-margin-top compact">
        <tr class="w3-light-gray">
          <th colspan="4">Project Details</th>
        </tr>
        <tr>
          <td width="20%">&nbsp;</td>
          <td width="35%">AC Size, kW</td>
          <td width="25%">{{ details['ac_size'] }}</td>
          <td width="20%">&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>DC Size, kW</td>
          <td>{{ details['dc_size'] }}</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>Address</td>
          <td>{{ details['address'] }}</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>Inverter Type</td>
          <td>{{ details['inverter_type'] }}</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>Number of Inverters</td>
          <td>{{ details['num_of_inverters'] }}</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>Number of EnvKit</td>
          <td>{{ details['num_of_envkits'] }}</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>Number of GenMeter</td>
          <td>{{ details['num_of_genmeters'] }}</td>
          <td>&nbsp;</td>
        </tr>

        <tr class="w3-light-gray">
          <th>Production Detail</th>
          <th colspan="3">Historical Reading</th>
        </tr>

        <tr>
          <td>&nbsp;</td>
          <td>Yesterday's Total Production, kWh</td>
          <td>&nbsp;</td>
          <td>{{ details['yesterday']['prod'] }}</td>
        </tr>

        <tr>
          <td>&nbsp;</td>
          <td>Yesterday's Total Insolation, kW/m<sup>2</sup></td>
          <td>&nbsp;</td>
          <td>{{ details['yesterday']['inso'] }}</td>
        </tr>

        <tr>
          <td>&nbsp;</td>
          <td>Month-to-date's Total Production, kWh</td>
          <td>&nbsp;</td>
          <td>{{ details['month-to-date']['prod'] }}</td>
        </tr>

        <tr>
          <td>&nbsp;</td>
          <td>Month-to-date's Total Insolation, kW/m<sup>2</sup></td>
          <td>&nbsp;</td>
          <td>{{ details['month-to-date']['inso'] }}</td>
        </tr>

        <tr>
          <td>&nbsp;</td>
          <td>Today's Total Production, kWh</td>
          <td>&nbsp;</td>
          <td>{{ details['today']['prod'] }}</td>
        </tr>

        <tr>
          <td>&nbsp;</td>
          <td>Today's Total Insolation, kW/m<sup>2</sup></td>
          <td>&nbsp;</td>
          <td>{{ details['today']['inso'] }}</td>
        </tr>
      </table>

<!-- dev start -->

      <table class="w3-table w3-margin-top">
        <tr class="w3-light-gray">
          <th>Meter Generation</th>
          <th>Current Reading</th>
          <th colspan="5"></th>
        </tr>
        <tr style="border-top: 1px solid lightgray;">
          <th>Meter Number</th>
          <th>kW Delivered</th>
          <th>kW Received</th>
          <th>kVar</th>
          <th>VLA, Volts</th>
          <th>VLB, Volts</th>
          <th>VLC, Volts</th>
        </tr>
        {% for code, genmeter in details['genmeters'] %}
        <tr data-code="{{ code }}" style="border-top: 1px solid lightgray;">
          <td>Meter {{ loop.index }}</td>
          <td>{{ genmeter['kw-del'] }}</td>
          <td>{{ genmeter['kw-rec'] }}</td>
          <td>{{ genmeter['kvar'] }}</td>
          <td>{{ genmeter['vla'] }}</td>
          <td>{{ genmeter['vlb'] }}</td>
          <td>{{ genmeter['vlc'] }}</td>
        </tr>
        {% endfor %}
      </table>

      <table class="w3-table w3-margin-top">
        <tr class="w3-light-gray">
          <th>Weather Station</th>
          <th>Current Reading</th>
          <th colspan="5"></th>
        </tr>
        <tr style="border-top: 1px solid lightgray;">
          <th>Weather Station Number</th>
          <th>Insolation, kW/m<sup>2</sup></th>
          <th>Ambient Temperature, C</th>
          <th>Back of Module Temperature, C</th>
          <th colspan="3"></th>
        </tr>
        {% for code, envkit in details['envkits'] %}
        <tr data-code="{{ code }}" style="border-top: 1px solid lightgray;">
          <td>WS {{ loop.index }}</td>
          <td>{{ envkit['inso'] }}</td>
          <td>{{ envkit['oat'] }}</td>
          <td>{{ envkit['panelt'] }}</td>
          <td colspan="3"></th>
        </tr>
        {% endfor %}
      </table>

      <table class="w3-table w3-margin-top">
        {% if details['inverters'] is not empty %}
        <tr class="w3-light-gray">
          <th>Inverter Data</th>
          <th>Current Reading</th>
          <th colspan="5"></th>
        </tr>
        {% endif %}

        <tr data-code="{{ code }}" style="border-top: 1px solid lightgray;">
          <th>Inverter Number</th>
          <th>Power, kW</th>
          <th>VLA, Volts</th>
          <th>VLB, Volts</th>
          <th>VLC, Volts</th>
          <th>Status</th>
{#        <th>Fault Code</th> #}
        </tr>

        {% for code, inverter in details['inverters'] %}
        <tr data-code="{{ code }}" style="border-top: 1px solid lightgray;">
          <td>Inverter {{ loop.index }}
          {% if inverter['combiner'] is not empty %}
          <a style="text-decoration: none;" class="w3-text-red w3-border w3-border-red" href="/project/combiner/{{ inverter['combiner'] }}" target="_blank">Combiner</a>
          {% endif %}
          </td>
          <td>{{ inverter['power'] }}</td>
          <td>{{ inverter['vla'] }}</td>
          <td>{{ inverter['vlb'] }}</td>
          <td>{{ inverter['vlc'] }}</td>
          <td>{{ inverter['status'] }}</td>
{#        <td>{{ inverter['fault'] }}</td> #}
        </tr>
        {% endfor %}
      </table>
      <br>

    </div>
    <p>&nbsp;</p>
  </div>
</div>
{% endblock %}

{% block csscode %}
.w3-modal { padding: 20px; }
.w3-table.compact td { padding: 0; }
.w3-table.compact th { padding: 8px 0; }
{% endblock %}
