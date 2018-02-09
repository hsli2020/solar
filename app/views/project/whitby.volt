{% extends "layouts/public.volt" %}

{% block main %}
<div class="container">
  <div class="w3-modal" style="display: block;">
    <div class="w3-modal-content w3-card-8 w3-padding" style="width:100%;">
      <h2>GCP Whitby</h2>

      <div class="w3-row">
        <div class="w3-col" style="width:120px">Project Name:</div>
        <div class="w3-rest">Whitby</div>
      </div>

      <table class="w3-table w3-margin-top">
        {% if data is not empty %}
        <tr class="w3-light-gray">
          <th>Time</th>
          <th>EZ<br>Com<br>Status</th>
          <th>SEL<br>Com<br>Status</th>
          <th>ACMG<br>Com<br>Status</th>
          <th>EMCP<br>Status</th>
          <th>Genset</th>
          <th>Total<br>gen<br>power</th>
          <th>Breaker<br>Status</th>
          <th>Hrs<br>until<br>maint</th>
          <th>Gen<br>Hrs</th>
          <th>S Total<br>Main<br>po</th>
          <th>S Total<br>Main<br>Re</th>
          <th>S Total<br>Gen<br>KWHR</th>
          <th>S Total<br>Main<br>KWhr</th>
          <th>Total<br>Mains<br>pow</th>
          <th>Total<br>Gen<br>ReacP</th>
          <th>Gen<br>Real<br>Er</th>
        </tr>
        {% for row in data %}
        <tr>
          <td>{{ row['ltime'] }}</th>
          <td>{{ row['EZ_Com_Status'] }}</th>
          <td>{{ row['SEL_Com_Status'] }}</th>
          <td>{{ row['ACMG_Com_Status'] }}</th>
          <td>{{ row['EMCP_Status'] }}</th>
          <td>{{ row['Genset_status'] }}</th>
          <td>{{ row['Total_gen_power'] }}</th>
          <td>{{ row['Breaker_Status'] }}</th>
          <td>{{ row['Hrs_until_maint'] }}</th>
          <td>{{ row['Gen_Hrs'] }}</th>
          <td>{{ row['S_Total_Main_po'] }}</th>
          <td>{{ row['S_Total_Main_Re'] }}</th>
          <td>{{ row['S_Totl_Gen_KWHR'] }}</th>
          <td>{{ row['S_Tot_Main_KWhr'] }}</th>
          <td>{{ row['Total_mains_pow'] }}</th>
          <td>{{ row['Total_gen_ReacP'] }}</th>
          <td>{{ row['Gen_Real_Er'] }}</th>
        </tr>
        {% endfor %}
      </table>
      {% endif %}
    </div>
    <p>&nbsp;</p>
  </div>
</div>
{% endblock %}

{% block csscode %}
table, th, td { border: 1px solid #ddd; }
.w3-modal { padding: 20px; }
.w3-table td { padding: 3px; }
.w3-table th { padding: 3px; vertical-align: middle; }
tr td:not(:first-child) { text-align: right; }
tr th:not(:first-child) { text-align: center; }
{% endblock %}
