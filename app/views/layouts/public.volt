<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <!--<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">-->

  <title>{% block title %}{% if pageTitle is defined %}{{ pageTitle }}{% endif %}{% endblock %}</title>

  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

  <link rel="stylesheet" href="http://www.w3schools.com/lib/w3.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
  <link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">

  <style>
    html,body,h1,h2,h3,h4,h5 {font-family: "Raleway", sans-serif}
  </style>

  {{ stylesheet_link("/css/style.css") }}
</head>
<body class="w3-light-grey">

  <div class="w3-main" style="margin-top:43px;">
    {% block main %}{% endblock %}
  </div>

  {% block jsfile %}{% endblock %}
  {% block jscode %}{% endblock %}

  <script type="text/javascript">
    $(document).ready(function() {
      {% block domready %}{% endblock %}
    });
  </script>
</body>
</html>
