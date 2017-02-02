<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <!--<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">-->

  <title>{% block title %}{% if pageTitle is defined %}{{ pageTitle }}{% endif %}{% endblock %}</title>

  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

  {{ stylesheet_link("/css/w3.css") }}
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
  <link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">

  <style>
    html,body,h1,h2,h3,h4,h5 {font-family: "Segoe UI",Arial,sans-serif}
  </style>

  {{ stylesheet_link("/css/style.css") }}
</head>
<body class="w3-light-grey">
  {# include "partials/sidebar.volt" #}
  {% include "partials/navbar.volt" %}

  <div class="w3-main" style="margin-top:43px;">
    <!-- Header -->
    <header class="w3-container" style="padding-top:22px">
        <h5><b><i class="fa fa-dashboard"></i> {{ pageTitle }}</b></h5>
    </header>

    {% block main %}{% endblock %}

    <!-- Footer -->
    <footer class="w3-container w3-padding-16 w3-light-grey" style="text-align:center">
      {% block footer %}{% endblock %}
      <img src="/img/GCS.png" style="margin-right: 50px">
      <img src="/img/Firelight.png">
    </footer>
  </div>

  <!-- Overlay effect when opening sidenav on small screens -->
  <div class="w3-overlay w3-hide-large w3-animate-opacity" onclick="w3_close()" style="cursor:pointer" title="close side menu"></div>

  <script type='text/javascript' src='/js/jquery-2.1.0.min.js'></script>
  {% block jsfile %}{% endblock %}
  {% block jscode %}{% endblock %}

  <script type="text/javascript">
    $(document).ready(function() {
      {% block domready %}{% endblock %}
    });
  </script>

  <script type="text/javascript">
    // Script to open and close sidenav
    function w3_open() {
      document.getElementsByClassName("w3-sidenav")[0].style.display = "block";
      document.getElementsByClassName("w3-overlay")[0].style.display = "block";
    }
     
    function w3_close() {
      document.getElementsByClassName("w3-sidenav")[0].style.display = "none";
      document.getElementsByClassName("w3-overlay")[0].style.display = "none";
    }
    function AutoRefresh(t) {
      setTimeout("location.reload(true);", t);
    }
    window.onload = AutoRefresh(1000*60*5);
  </script>
</body>
</html>
