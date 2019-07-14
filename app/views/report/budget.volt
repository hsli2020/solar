{% extends "layouts/base.volt" %}

{% block main %}
<style type="text/css">
  table, th, td { border: 1px solid #ddd; vertical-align: middle; }
  #report th { text-align: center; vertical-align: middle; }
  #report td { text-align: right; vertical-align: middle; }
  #report tr td:first-child{ text-align: left; }
</style>

<div class="w3-container">

<div class="w3-margin-bottom">
<form method="POST">
  <span class="w3-margin-right">Select Project: </span>
  <select id="project-list" class="w3-select w3-border" style="width: 20em;" name="month">
  {% for project in projects %}
  <option value="{{ project.id }}" {% if curprj == project.id %}selected{% endif %}>{{ project.name }}</option>
  {% endfor %}
  </select>
</form>
</div>

<table id="budget" class="w3-table w3-white w3-bordered w3-border w3-centered">
<tr>
  <th>Year</th>
  <th>Month</th>
  <th>Budget</th>
  <th>Insolation</th>
</tr>
{% for budget in budgets %}
<tr>
  <td>{{ budget['year'] }}</td>
  <td>{{ budget['month'] }}</td>
  <td>{{ budget['Budget'] }}</td>
  <td>{{ budget['IE_POA_Insolation'] }}</td>
</tr>
{% endfor %}
</table>
</div>
{% endblock %}

{% block jscode %}
{% endblock %}

{% block domready %}
$('#project-list').change(function() {
    window.location = '/report/budget/' + $(this).val();
})
{% endblock %}

