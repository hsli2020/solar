{% extends "layouts/public.volt" %}

{% block main %}
<div class="container">
  <div class="w3-modal" style="display: block;">
    <div class="w3-modal-content w3-card-8 w3-padding" style="max-width:350px">
      <p style="text-align: center;">
        <img src="/img/gcs-logo-name-223x38.png">
      </p>

      <form class="w3-container" method="POST">
        <div class="w3-section">
          <label><b>Username</b></label>
          <input class="w3-input w3-border w3-margin-bottom" placeholder="Enter Username" name="username" required autofocus type="text">

          <label><b>Email</b></label>
          <input class="w3-input w3-border w3-margin-bottom" placeholder="Enter Email" name="email" type="text">

          <label><b>Password</b></label>
          <input class="w3-input w3-border w3-margin-bottom" placeholder="Enter Password" name="password" required type="password">

          <input type="hidden" name="{{ security.getTokenKey() }}" value="{{ security.getToken() }}"/>

          <button class="w3-btn-block w3-green w3-section w3-padding" type="submit">Add New User</button>
        </div>
      </form>
    </div>
  </div>
</div>
{% endblock %}

{% block csscode %}
{% endblock %}
