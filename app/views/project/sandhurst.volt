{% extends "layouts/public.volt" %}

{% block main %}
<div class="container">
  <div class="w3-modal" style="display: block;">
    <div class="w3-modal-content w3-card-8 w3-padding" style="width:100%;">
      <h2>Combiner</h2>

      <div class="w3-row">
        <div class="w3-col" style="width:200px">Project Name:</div>
        <div class="w3-rest">{{ project.name }}</div>
      </div>
      <div class="w3-row">
        <div class="w3-col" style="width:200px">Inverter:</div>
        <div class="w3-rest">&nbsp;</div>
      </div>
      <div class="w3-row">
        <div class="w3-col" style="width:200px">Combiner Current Avg:</div>
        <div class="w3-col m2">&nbsp;</div>
        <div class="w3-col m2"><a href="/project/dumpdata/{{ project.id }}/{{ devcode }}" target="_blank">Download</a></div>
      </div>

      {% if data is not empty %}
      <table class="w3-table w3-margin-top w3-hoverable">
        <tr class="w3-light-gray">
		  <td>Time</td>
		  <td>idc01</td>
		  <td>idc02</td>
		  <td>idc03</td>
		  <td>idc04</td>
		  <td>idc05</td>
		  <td>idc06</td>
		  <td>idc07</td>
		  <td>idc08</td>
		  <td>idc09</td>
		  <td>idc10</td>
		  <td>idc11</td>
		  <td>idc12</td>
		  <td>idc13</td>
		  <td>idc14</td>
		  <td>idc15</td>
		</tr>

		{% for row in data %}
		<tr>
		  <td>{{ row['time']  }}</td>
		  <td>{{ row['idc01'] }}</td>
		  <td>{{ row['idc02'] }}</td>
		  <td>{{ row['idc03'] }}</td>
		  <td>{{ row['idc04'] }}</td>
		  <td>{{ row['idc05'] }}</td>
		  <td>{{ row['idc06'] }}</td>
		  <td>{{ row['idc07'] }}</td>
		  <td>{{ row['idc08'] }}</td>
		  <td>{{ row['idc09'] }}</td>
		  <td>{{ row['idc10'] }}</td>
		  <td>{{ row['idc11'] }}</td>
		  <td>{{ row['idc12'] }}</td>
		  <td>{{ row['idc13'] }}</td>
		  <td>{{ row['idc14'] }}</td>
		  <td>{{ row['idc15'] }}</td>
		</tr>
		{% endfor %}
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
.w3-table td { padding: 5px 3px; }
.w3-table th { padding: 5px 3px; }
tr td:not(:first-child) { text-align: right; }
tr th:not(:first-child) { text-align: right; }
{% endblock %}
