{% extends "layouts/public.volt" %}

{% block main %}
<div class="container">
  <div class="w3-modal" style="display: block;">
    <div class="w3-modal-content w3-card-8 w3-padding" style="max-width:900px">
      <h2>Detailed (Site Information)</h2>

      <table class="w3-table">
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

      <table class="w3-table w3-margin-top">
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
          <td>AE Inverter</td>
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
          <td>Inverter 1</td>
          <td>AC Capacity, kW</td>
          <td>100</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>Inverter 2</td>
          <td>AC Capacity, kW</td>
          <td>200</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>Inverter 3</td>
          <td>AC Capacity, kW</td>
          <td>300</td>
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

        <tr class="w3-light-gray">
          <th>Inverter Data</th>
          <th colspan="3">Current Reading</th>
        </tr>

        <tr>
          <td>&nbsp;</td>
          <td>Power, kW</td>
          <td>{{ details['inverter']['power'] }}</td>
          <td>&nbsp;</td>
        </tr>

        <tr>
          <td>&nbsp;</td>
          <td>Status</td>
          <td>{{ details['inverter']['status'] }}</td>
          <td>&nbsp;</td>
        </tr>

        <tr>
          <td>&nbsp;</td>
          <td>Fault Code</td>
          <td>{{ details['inverter']['fault'] }}</td>
          <td>&nbsp;</td>
        </tr>

        <tr>
          <td>&nbsp;</td>
          <td>VLA, Volts</td>
          <td>{{ details['inverter']['vla'] }}</td>
          <td>&nbsp;</td>
        </tr>

        <tr>
          <td>&nbsp;</td>
          <td>VLB, Volts</td>
          <td>{{ details['inverter']['vlb'] }}</td>
          <td>&nbsp;</td>
        </tr>

        <tr>
          <td>&nbsp;</td>
          <td>VLC, Volts</td>
          <td>{{ details['inverter']['vlc'] }}</td>
          <td>&nbsp;</td>
        </tr>

        <tr>
          <td>&nbsp;</td>
          <td>VLN, Volts</td>
          <td>{{ details['inverter']['vln'] }}</td>
          <td>&nbsp;</td>
        </tr>

        <tr class="w3-light-gray">
          <th>Weather Station</th>
          <th colspan="3">Current Reading</th>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>Insolation, kW/m<sup>2</sup></td>
          <td>&nbsp;</td>
          <td>{{ details['envkit']['inso'] }}</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>Ambient Temperature, C</td>
          <td>&nbsp;</td>
          <td>{{ details['envkit']['oat'] }}</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>Back of Module Temperature, C</td>
          <td>&nbsp;</td>
          <td>{{ details['envkit']['panelt'] }}</td>
        </tr>

        <tr class="w3-light-gray">
          <th>Meter Generation</th>
          <th colspan="3">Current Reading</th>
        </tr>

        <tr>
          <td>&nbsp;</td>
          <td>kW Delivered</td>
          <td>&nbsp;</td>
          <td>{{ details['genmeter']['kw-del'] }}</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>kW Received</td>
          <td>&nbsp;</td>
          <td>{{ details['genmeter']['kw-rec'] }}</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>kVar</td>
          <td>&nbsp;</td>
          <td>{{ details['genmeter']['kvar'] }}</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>VLA, Volts</td>
          <td>&nbsp;</td>
          <td>{{ details['genmeter']['vla'] }}</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>VLB, Volts</td>
          <td>&nbsp;</td>
          <td>{{ details['genmeter']['vlb'] }}</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>VLC, Volts</td>
          <td>&nbsp;</td>
          <td>{{ details['genmeter']['vlc'] }}</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>VLN, Volts</td>
          <td>&nbsp;</td>
          <td>{{ details['genmeter']['vln'] }}</td>
        </tr>
      </table>
      <br>

    </div>
    <p>&nbsp;</p>
  </div>
</div>
{% endblock %}

{% block csscode %}
.w3-modal { padding: 20px; }
.w3-table td { padding: 0; }
.w3-table th { padding: 8px 0; }
{% endblock %}
