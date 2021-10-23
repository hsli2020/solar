{% extends "layouts/public.volt" %}

{% block main %}
<div class="container">
  <div class="w3-modal" style="display: block;">
    <div class="w3-modal-content w3-card-8 w3-padding" style="width:100%;">
      <h2>{{ pageTitle }}</h2>

      <div class="w3-row">
        <div class="w3-col" style="width:120px">Project Name:</div>
        <div class="w3-rest">{{ project.name }}</div>
      </div>
      <div class="w3-row">
{#
        <div class="w3-col" style="width:120px">Inverter:</div>
        <div class="w3-rest">{{ inverter.name }}</div>
#}
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
          <th># of modules</th>
          <th>Solar Panel Module  rating [Wp]</th>
          <th>Raw (A) -- current 5 minute data</th>
          <th>Normalized (A)</th>
        </tr>

        {% set bgcolors = [
            'w3-pale-yellow',
            'w3-pale-green',
            'w3-pale-blue',
            'w3-sand',
            'w3-khaki',
            'w3-light-blue',
            'w3-blue-gray',
            'w3-lime',
            'w3-cyan',
            'w3-brown'
        ] %}
        {% set index = 0 %}
        {% set colorMap = [] %}

        {% for row in data %}
        {% set mr = row['module_rating'] %}

        {% if colorMap[mr] is empty %}
        {%   set colorMap[mr] = bgcolors[index] %}
        {%   set index = index+1 %}
        {% endif %}

        {% set bgcolor=colorMap[mr] %}
        <tr>
            <td class="{{ bgcolor }} w3-center">{{ row['name'] }}</td>
            <td class="{{ bgcolor }} w3-center">{{ row['num_strings'] }}</td>
            <td class="{{ bgcolor }} w3-center">{{ row['num_modules'] }}</td>
            <td class="{{ bgcolor }} w3-center">{{ row['module_rating'] }}</td>
            <td class="w3-center">{{ row['raw'] }}</td>
            <td class="w3-center">{{ row['normalized'] }}</td>
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
tr th:not(:first-child) { text-align: center; }
{% endblock %}
