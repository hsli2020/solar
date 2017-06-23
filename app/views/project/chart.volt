{% extends "layouts/base.volt" %}

{% block jsfile %}
{{ javascript_include("/flot/jquery.flot.js") }}
{{ javascript_include("/flot/jquery.flot.time.js") }}
{{ javascript_include("/js/script.js") }}
{% endblock %}

{% block main %}
  <div class="w3-container">
    <div id="chart-content">
	  <div id="header">
	  	<h2>Power Production (kW) and Irradiance (W/m<sup>2</sup>)</h2>
	  </div>
      <div class="chart-container">
        <div id="placeholder" class="chart-placeholder"></div>
      </div>
    </div>
  </div>
{% endblock %}

{% block csscode %}
h2 {
    border: 1px solid lightgrey;
    background-color: rgb(240,240,240);
    color: black;
    padding: 5px;
    text-align: center;
}
{% endblock %}

{% block jscode %}
function getRandomData() {
    var data = [], totalPoints = 288;

    if (data.length > 0)
        data = data.slice(1);

    // Do a random walk
    while (data.length < totalPoints) {
        var prev = data.length > 0 ? data[data.length - 1] : 50,
            y = prev + Math.random() * 10 - 5;

        if (y < 0) {
            y = 0;
        } else if (y > 100) {
            y = 100;
        }

        data.push(y);
    }

    // Zip the generated y values with the x values
    var res = [];
    for (var i = 0; i < data.length; ++i) {
        res.push([i, data[i]])
    }

    return res;
}
{% endblock %}

{% block domready %}
var bar = {
    label: "Power Production",
    data: {{ kva }},
    color: "rgb(54, 162, 235)",
    shadowSize: 0,
    yaxis: 1,
    bars: { show: true, barWidth: 1, lineWidth: 1, fill: true }
}

var line = {
    label: "Irradiance",
    data: {{ irr }},
    color: "#c00000",
    shadowSize: 0,
    yaxis: 2,
    lines: { show: true, lineWidth: 1 }
}

var options = {
    series: {
        shadowSize: 0	// Drawing is faster without shadows
    },
    yaxes: [ 
        { position: "left" },
        { position: "right" }
    ],
    xaxis: { show: true }
}

$.plot("#placeholder", [ bar, line ], options);
{% endblock %}
