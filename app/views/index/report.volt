{% extends "layouts/base.volt" %}

{% block jsfile %}
{{ javascript_include("/flot/jquery.js") }}
{{ javascript_include("/flot/jquery.flot.js") }}
{{ javascript_include("/flot/jquery.flot.time.js") }}
{{ javascript_include("/flot/jquery.flot.navigate.js") }}
{{ javascript_include("/flot/jquery.flot.categories.js") }}
{{ javascript_include("/flot/jquery.flot.canvas.js") }}
{{ javascript_include("/flot/jquery.flot.crosshair.js") }}
{{ javascript_include("/flot/jquery.flot.errorbars.js") }}
{{ javascript_include("/flot/jquery.flot.fillbetween.js") }}
{{ javascript_include("/flot/jquery.flot.image.js") }}
{{ javascript_include("/flot/jquery.flot.pie.js") }}
{{ javascript_include("/flot/jquery.flot.resize.js") }}
{{ javascript_include("/flot/jquery.flot.selection.js") }}
{{ javascript_include("/flot/jquery.flot.stack.js") }}
{{ javascript_include("/flot/jquery.flot.symbol.js") }}
{{ javascript_include("/flot/jquery.flot.threshold.js") }}
{{ javascript_include("/js/script.js") }}
{% endblock %}

{% block main %}
  <div class="w3-container">
    <div id="content">
      <div class="demo-container">
        <div id="placeholder" class="demo-placeholder"></div>
      </div>
    </div>
  </div>
{% endblock %}

{% block jscode %}
{% endblock %}

{% block domready %}
  ajaxCall('/ajax/data', { prj: 1, dev: 'mb-031', col: 'kw' },
    function(data) {
      data = data.slice(-200);
      var options = {
        series: {
            bars: {	show: true,	barWidth: 10 },
            //lines: { show: true },
            shadowSize: 0
        },
        xaxis: {
            mode: 'time',
            panRange: [data[0][0], data[data.length-1][0]],
            zoomRange: [data[0][0], data[data.length-1][0]]
        }//,
        //yaxis: { panRange: [0, 500] },
        //zoom: { interactive: false },
        //pan: { interactive: true }
      };
      $.plot("#placeholder", [ data ], options);
    },
    function(message) {
      //showError(message);
    }
  );
{% endblock %}
