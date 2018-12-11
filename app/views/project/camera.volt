{% extends "layouts/base.volt" %}

{% block main %}
<div class="w3-container">
  <div class="w3-row">
    {% for picture in pictures %}
    <div class="w3-half w3-padding w3-border">
      <img src="/picture/show/{{ picture['id'] }}" width="100%">
      <p>[ {{ project.name }} ] - {{ picture['camera'] }}<span class="w3-right">{{ picture['createdon'] }}</span></p>
    </div>
    {% endfor %}
  </div>
</div>
{% endblock %}

{% block cssfile %}
{% endblock %}

{% block jsfile %}
{% endblock %}

{% block domready %}
{% endblock %}
