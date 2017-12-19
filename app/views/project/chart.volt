{% extends "layouts/base.volt" %}

{% block jsfile %}
{{ javascript_include("/flot/jquery.flot.js") }}
{{ javascript_include("/flot/jquery.flot.time.js") }}
{{ javascript_include("/flot/jquery.flot.crosshair.js") }}
{{ javascript_include("/pickadate/picker.js") }}
{{ javascript_include("/pickadate/picker.date.js") }}
{{ javascript_include("/js/script.js") }}
{% endblock %}

{% block cssfile %}
  {{ stylesheet_link("/pickadate/themes/classic.css") }}
  {{ stylesheet_link("/pickadate/themes/classic.date.css") }}
{% endblock %}

{% block main %}
  <div class="w3-container">
    <div id="chart-content">
	  <div id="header">
	  	<h2>Power Production (kW) and Irradiance (W/m<sup>2</sup>)</h2>
	  </div>

	  <div>Project: {{ project.name }} ({{ date1 }})<span id="legend">Power=0, Irradiance=0</span></div>
      <div class="chart-container">
        <div id="placeholder1" class="chart-placeholder"></div>
      </div>

	  <form method="POST">
        <label>Select Date: </label>
        <input class="datepicker" name="date2" type="text" value="{{ date2 }}">
        <button type="submit">Refresh</button>
        {% if date2 %}
        <span id="legend">Power=0, Irradiance=0</span>
        {% endif %}
      </form>
      <div class="chart-container">
        <div id="placeholder2" class="chart-placeholder"></div>
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
#legend {
    float: right;
    background-color: rgb(54, 162, 235);
    color: white;
    padding: 3px 10px;
    border: 1px solid #fe0;
}
{% endblock %}

{% block jscode %}
var updateLegendTimeout = null;
var latestPosition = null;
var legend = $("#legend");
var valstr;

function updateLegend() {

    updateLegendTimeout = null;

    var pos = latestPosition;

    var axes = plot.getAxes();
    if (pos.x < axes.xaxis.min || pos.x > axes.xaxis.max ||
        pos.y < axes.yaxis.min || pos.y > axes.yaxis.max) {
        return;
    }

    var i, j, ts, vals = [], dataset = plot.getData();
    for (i = 0; i < dataset.length; ++i) {

        var series = dataset[i];

        // Find the nearest points, x-wise

        for (j = 0; j < series.data.length; ++j) {
            if (series.data[j][0] > pos.x) {
                break;
            }
        }

        // Now Interpolate

        var y,
            ts = series.data[j][0],
            p1 = series.data[j - 1],
            p2 = series.data[j];

        if (p1 == null) {
            y = p2[1];
        } else if (p2 == null) {
            y = p1[1];
        } else {
            y = p1[1] + (p2[1] - p1[1]) * (pos.x - p1[0]) / (p2[0] - p1[0]);
        }
        vals[i] = y.toFixed(0);
    }

    var date = new Date();
    date.setTime(ts);
    timeStr = date.toUTCString().substr(-12, 5);

    valstr = timeStr + " Power=" + vals[0] + ", " + "Irradiance=" + vals[1];
    legend.text(valstr);
}
{% endblock %}

{% block domready %}
var bar1 = {
    label: "Power Production",
    data: {{ kva1 }},
    color: "rgb(54, 162, 235)",
    shadowSize: 0,
    yaxis: 1,
    bars: { show: true, barWidth: 1, lineWidth: 1, fill: true }
}

var line1 = {
    label: "Irradiance",
    data: {{ irr1 }},
    color: "#c00000",
    shadowSize: 0,
    yaxis: 2,
    lines: { show: true, lineWidth: 1 }
}

var bar2 = {
    label: "Power Production",
    data: {{ kva2 }},
    color: "rgb(54, 162, 235)",
    shadowSize: 0,
    yaxis: 1,
    bars: { show: true, barWidth: 1, lineWidth: 1, fill: true }
}

var line2 = {
    label: "Irradiance",
    data: {{ irr2 }},
    color: "#c00000",
    shadowSize: 0,
    yaxis: 2,
    lines: { show: true, lineWidth: 1 }
}

var options = {
    series: {
        shadowSize: 0	// Drawing is faster without shadows
    },
    crosshair: {
        mode: "x"
    },
    grid: {
        hoverable: true,
        //clickable: true
        autoHighlight: false
    },
    yaxes: [ 
        { position: "left" },
        { position: "right" }
    ],
    xaxis: {
        mode: 'time',
        show: true
    }
}

plot = $.plot("#placeholder1", [ bar1, line1 ], options);
plot = $.plot("#placeholder2", [ bar2, line2 ], options);

$("<div id='tooltip'></div>").css({
    position: "absolute",
    display: "none",
    border: "1px solid #fdd",
    padding: "2px",
    "background-color": "#fee",
    opacity: 0.80
}).appendTo("body");

$(".chart-placeholder").bind("plothover", function (event, pos, item) {
    latestPosition = pos;
    if (!updateLegendTimeout) {
        updateLegendTimeout = setTimeout(updateLegend, 50);
    }
    /*
    if (item) {
        $("#tooltip").html(valstr)
            .css({top: item.pageY+5, left: item.pageX+5})
            .fadeIn(200);
    } else {
        $("#tooltip").hide();
    }
    */
});

$('.datepicker').pickadate({format: 'yyyy-mm-dd'});
{% endblock %}
