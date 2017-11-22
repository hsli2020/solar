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
        <div class="w3-rest">&nbsp;</div>
      </div>
      <div class="w3-row">
        <div class="w3-col" style="width:120px">Combiner:</div>
        <div class="w3-rest">&nbsp;</div>
      </div>

      <table class="w3-table w3-margin-top">
        {% if data is not empty %}
        <tr class="w3-light-gray">
          <th>Time</th>
          <th>CB_1</th>
          <th>CB_2</th>
          <th>CB_3</th>
          <th>CB_4</th>
          <th>CB_5</th>
          <th>CB_6</th>
          <th>CB_7</th>
          <th>CB_8</th>
          <th>CB_9</th>
          <th>CB_10</th>
          <th>CB_11</th>
          <th>CB_12</th>
          <th>CB_13</th>
          <th>CB_14</th>
          <th>CB_15</th>
          <th>CB_16</th>
          <th>CB_17</th>
          <th>CB_18</th>
          <th>CB_19</th>
          <th>CB_20</th>
          <th>CB_21</th>
          <th>CB_22</th>
        </tr>
        {% for row in data %}
        <tr>
          <td>{{ row['time'] }}</th>
          <td>{{ row['CB_1'] }}</th>
          <td>{{ row['CB_2'] }}</th>
          <td>{{ row['CB_3'] }}</th>
          <td>{{ row['CB_4'] }}</th>
          <td>{{ row['CB_5'] }}</th>
          <td>{{ row['CB_6'] }}</th>
          <td>{{ row['CB_7'] }}</th>
          <td>{{ row['CB_8'] }}</th>
          <td>{{ row['CB_9'] }}</th>
          <td>{{ row['CB_10'] }}</th>
          <td>{{ row['CB_11'] }}</th>
          <td>{{ row['CB_12'] }}</th>
          <td>{{ row['CB_13'] }}</th>
          <td>{{ row['CB_14'] }}</th>
          <td>{{ row['CB_15'] }}</th>
          <td>{{ row['CB_16'] }}</th>
          <td>{{ row['CB_17'] }}</th>
          <td>{{ row['CB_18'] }}</th>
          <td>{{ row['CB_19'] }}</th>
          <td>{{ row['CB_20'] }}</th>
          <td>{{ row['CB_21'] }}</th>
          <td>{{ row['CB_22'] }}</th>
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
.w3-table th { padding: 3px; }
tr td:not(:first-child) { text-align: right; }
tr th:not(:first-child) { text-align: right; }
{% endblock %}
