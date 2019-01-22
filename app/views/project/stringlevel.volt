{% extends "layouts/public.volt" %}

{% block main %}
<div class="container">
  <div class="w3-modal" style="display: block;">
    <div class="w3-modal-content w3-card-8 w3-padding" style="width:100%;">
      <h2>String Level Combiner</h2>

      <div class="w3-row">
        <div class="w3-col" style="width:200px">Project Name:</div>
        <div class="w3-rest">{{ project.name }}</div>
      </div>
      <div class="w3-row">
        <div class="w3-col" style="width:200px">Combiner:</div>
        <div class="w3-rest">&nbsp;</div>
      </div>
{#
      <div class="w3-row">
        <div class="w3-col" style="width:200px"></div>
        <div class="w3-rest">&nbsp;</div>
      </div>
#}
      {% if data is not empty %}
      <table class="w3-table w3-margin-top w3-hoverable">
        <tr>
            <td width="30%">Total Current Inst</td>
            <td width="30%">{{ data['Total_Current_Inst'] }}</td>
            <td width="30%">(Amps)</td>
        <tr></tr>
            <td>Total Current Ave</td>
            <td>{{ data['Total_Current_Ave'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>Average Current Inst)</td>
            <td>{{ data['Average_Current_Inst'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>Average Current Ave</td>
            <td>{{ data['Average_Current_Ave'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>Alarm Channel</td>
            <td>{{ data['Alarm_Channel'] }}</td>
            <td>&nbsp;</td>
        <tr></tr>
            <td>Alarm Age</td>
            <td>{{ data['Alarm_Age'] }}</td>
            <td>&nbsp;</td>
        <tr></tr>
            <td>String 12</td>
            <td>{{ data['String_12'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 12 Ave</td>
            <td>{{ data['String_12_Ave'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 11</td>
            <td>{{ data['String_11'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 11 Ave</td>
            <td>{{ data['String_11_Ave'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 10</td>
            <td>{{ data['String_10'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 10 Ave</td>
            <td>{{ data['String_10_Ave'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 9</td>
            <td>{{ data['String_9'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 9 Ave</td>
            <td>{{ data['String_9_Ave'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 8</td>
            <td>{{ data['String_8'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 8 Ave</td>
            <td>{{ data['String_8_Ave'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 7</td>
            <td>{{ data['String_7'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 7 Ave</td>
            <td>{{ data['String_7_Ave'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 6</td>
            <td>{{ data['String_6'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 6 Ave</td>
            <td>{{ data['String_6_Ave'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 5</td>
            <td>{{ data['String_5'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 5 Ave</td>
            <td>{{ data['String_5_Ave'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 4</td>
            <td>{{ data['String_4'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 4 Ave</td>
            <td>{{ data['String_4_Ave'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 3</td>
            <td>{{ data['String_3'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 3 Ave</td>
            <td>{{ data['String_3_Ave'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 2</td>
            <td>{{ data['String_2'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 2 Ave</td>
            <td>{{ data['String_2_Ave'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 1</td>
            <td>{{ data['String_1'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>String 1 Ave</td>
            <td>{{ data['String_1_Ave'] }}</td>
            <td>(Amps)</td>
        <tr></tr>
            <td>PCB Temp</td>
            <td>{{ data['PCB_Temp'] }}</td>
            <td>(Degrees F)</td>
        <tr></tr>
            <td>Power Supply</td>
            <td>{{ data['Power_Supply'] }}</td>
            <td>(Volts)</td>
        <tr></tr>
            <td>Analog Input 1</td>
            <td>{{ data['Analog_Input_1'] }}</td>
            <td>&nbsp;</td>
        <tr></tr>
      </table>
      <p>&nbsp;</p>
      {% endif %}
    </div>
  </div>
</div>
{% endblock %}

{% block csscode %}
table, th, td { border: 1px solid #ddd; }
.w3-modal { padding: 20px; }
.w3-table td { padding: 2px; }
.w3-table th { padding: 2px; }
tr td:not(:first-child) { text-align: center; }
tr th:not(:first-child) { text-align: center; }
{% endblock %}
