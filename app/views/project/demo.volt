{% extends "layouts/public.volt" %}

{% block main %}
<div class="container">
  <div class="w3-modal" style="display: block;">
    <div class="w3-modal-content w3-card-8 w3-padding" style="width:100%;">
      <h2>Dashboard</h2>

      <table class="w3-table w3-margin-top">
        {% if data is not empty %}
        <tr class="w3-light-gray">
          <th>Project</th>
          <th>Generator Status</th>
          <th>Generator Power<br>kW</th>
          <th>Store Load<br>kW</th>
          <th>Hours Until Next Maintenance</th>
          <th>Generator Breaker<br>Closed</th>
          <th>Main Breaker Closed</th>
          <th>86G Lockout<br>Status</th>
          <th>86M Lockout<br>Status</th>
          <th>52G Breaker<br>Status</th>
          <th>52M Breaker<br>Status</th>
        </tr>
        {% for row in data %}
        <tr>
          <td>{{ row['project'] }}</th>
          <td>{{ row['Genset_status'] }}</th>
          <td>{{ row['Total_gen_power'] }}</th>
          <td>{{ row['Total_mains_pow'] }}</th>
          <td>{{ row['Hrs_until_maint'] }}</th>
          <td>{{ row['D12_Gen_Closed'] }}</th>
          <td>{{ row['D11_Main_Closed'] }}</th>
          <td>{{ row['_86GLockoutTrip'] }}</th>
          <td>{{ row['_86MLockoutTr_1'] }}</th>
          <td>{{ row['_52GBrkr_Trip'] }}</th>
          <td>{{ row['_52MBrkr_Trip'] }}</th>
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
