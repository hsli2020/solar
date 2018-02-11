{% extends "layouts/public.volt" %}

{% block main %}
{%- macro Green1_Red0(val) %}
  {% if val == 1 %}
    <img src="/img/green.png" width="40">
  {% elseif val == 0 %}
    <img src="/img/red.png" width="40">
  {% endif %}
{%- endmacro %}

{%- macro GreenClose1_RedOpen0(val) %}
  {% if val == 1 %}
    <img src="/img/green-close.png" width="40">
  {% elseif val == 0 %}
    <img src="/img/red-open.png" width="40">
  {% endif %}
{%- endmacro %}

{%- macro Green0_Red1(val) %}
  {% if val == 0 %}
    <img src="/img/green.png" width="40">
  {% elseif val == 1 %}
    <img src="/img/red.png" width="40">
  {% endif %}
{%- endmacro %}

<div class="container">
  <div class="w3-modal" style="display: block;">
    <div class="w3-modal-content w3-card-8 w3-padding" style="width:100%;">
      <h2>Dashboard</h2>

      <table class="w3-table w3-margin-top">
        {% if data is not empty %}
        <tr class="w3-light-gray">
          <th>Project</th>
          <th>Generator<br>Status</th>
          <th>Generator<br>Power (kW)</th>
          <th>Store Load<br>kW</th>
          <th>Hours Until Next<br>Maintenance</th>
          <th>Generator Breaker<br>Closed</th>
          <th>Main Breaker<br>Closed</th>
          <th>SEL Com<br>Status</th>
          <th>EZ Com<br>Status</th>
          <th>ACMG Com<br>Status</th>
          <th>EMCP<br>Status</th>
        </tr>
        {% for row in data %}
        <tr>
          <td>Whitby</th>
          <td>{{ Green1_Red0(row['Genset_status']) }}</th>
          <td>{{ row['Total_gen_power'] }}</th>
          <td>{{ row['Total_mains_pow'] }}</th>
          <td>{{ row['Hrs_until_maint'] }}</th>
          <td>{{ GreenClose1_RedOpen0(row['D12_Gen_Closed']) }}</th>
          <td>{{ GreenClose1_RedOpen0(row['D11_Main_Closed']) }}</th>
          <td>{{ Green1_Red0(row['SEL_Com_Status']) }}</th>
          <td>{{ Green1_Red0(row['EZ_Com_Status']) }}</th>
          <td>{{ Green1_Red0(row['ACMG_Com_Status']) }}</th>
          <td>{{ Green1_Red0(row['EMCP_Status']) }}</th>
        </tr>
        {% endfor %}
      </table>
      {% endif %}
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
    </div>
    <p>&nbsp;</p>
  </div>
</div>
{% endblock %}

{% block csscode %}
table, th, td { border: 1px solid #ddd; }
.w3-modal { padding: 20px; }
.w3-table td { padding: 3px; vertical-align: middle; }
.w3-table th { padding: 3px; vertical-align: middle; }
tr td:not(:first-child) { text-align: center; }
tr th:not(:first-child) { text-align: center; }
{% endblock %}
