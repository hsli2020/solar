{% extends "layouts/public.volt" %}

{% block main %}
<div class="container">
  <div class="w3-modal" style="display: block;">
    <div class="w3-modal-content w3-card-8 w3-padding" style="width:100%;">
      <h2>Combiner</h2>

      <div class="w3-row">
        <div class="w3-col" style="width:120px">Project Name:</div>
        <div class="w3-rest">{{ project.name }}</div>
      </div>
      <div class="w3-row">
        <div class="w3-col" style="width:120px">Inverter:</div>
{#      <div class="w3-rest">{{ inverter.name }}</div> #}
      </div>
      <div class="w3-row">
        <div class="w3-col m1" style="width:120px">Combiner:</div>
{#
        <div class="w3-col m2">{{ combiner.name }}</div>
        <div class="w3-col m3"><a href="/project/exportcombiner/{{ project.id }}/{{ combiner.code }}" target="_blank">Download</a></div>
#}
      </div>

      {% if data is not empty %}
      <table class="w3-table w3-margin-top">
        <tr class="w3-light-gray">
          <th>Combiner Box</th>
          <th># of strings</th>
          <th>Solar Panel Module  rating [Wp]</th>
          <th>Raw (A) -- current 5 minute data</th>
          <th>Normalized (A)</th>
        </tr>
        {% for row in data %}
        <tr>
            <td class="w3-pale-yellow w3-center">{{ row['name'] }}</td>
            <td class="w3-pale-yellow w3-center">{{ row['num_strings'] }}</td>
            <td class="w3-pale-yellow w3-center">{{ row['module_rating'] }}</td>
            <td class="w3-right-align">3.97</td>
            <td class="w3-right-align">0.4</td>
        </tr>
        {% endfor %}
      </table>
      {% endif %}
      <p>&nbsp;</p>
    </div>
    <p>&nbsp;</p>
  </div>
</div>
{% endblock %}

{% block csscode %}
table, th, td { border: 1px solid #ddd; }
.w3-modal { padding: 20px; }
w3-table td { padding: 3px; }
w3-table th { padding: 3px; }
tr td:not(:first-child) { text-align: right; }
tr th:not(:first-child) { text-align: right; }
{% endblock %}
