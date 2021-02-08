{% extends "layouts/base.volt" %}

{% block main %}

<div class="w3-container">
  {% if rows is not empty %}
  <form class="" method="POST" autocomplete="off">
    <div class="w3-row-padding">
      <div class="w3-quarter">
        <input class="w3-input w3-border datepicker" name="start-time" required type="text" placeholder="Start Date">
      </div>
      <div class="w3-quarter">
        <input class="w3-input w3-border datepicker" name="end-time" required type="text" placeholder="End Date">
      </div>
      <div class="w3-quarter">
        <button class="w3-btn-block w3-indigo" type="submit">Download</button>
      </div>
    </div>
  </form>

  <table class="w3-table w3-margin-top w3-hoverable">
    <tr class="w3-light-gray">
      <td>Time (UTC)</td>
      <td>Battary Voltage</td>
      <td>Site Temperatures</td>
    </tr>

    {% for row in rows %}
    <tr>
      <td>{{ row['time']  }}</td>
      <td>{{ row['battery_volt'] }}</td>
      <td>{{ row['site_temp'] }}</td>
    </tr>
    {% endfor %}
  </table>
  <p>&nbsp;</p>
  {% endif %}
</div>
{% endblock %}

{% block csscode %}
table { border: 5px solid #eee !important; margin-bottom: 10px; }
table, th, td { border: 1px solid #ddd; }
{% endblock %}

{% block jscode %}
{% endblock %}

{% block cssfile %}
  {{ stylesheet_link("/datetimepicker/jquery.datetimepicker.min.css") }}
{% endblock %}

{% block jsfile %}
  {{ javascript_include("/datetimepicker/jquery.datetimepicker.full.min.js") }}
{% endblock %}

{% block domready %}
  $('.datepicker').datetimepicker({format: 'Y-m-d H:i', timepicker:true, step: 30});
{% endblock %}
