{% extends "layouts/base.volt" %}

{% block main %}
<div class="w3-container">
  <div class="w3-row">
    <table id="snapshot" class="w3-table w3-white w3-bordered w3-border">
    <tr>
      <th class="w3-light-grey"><i class="fa fa-globe"></i> Source</th>
      <td id="dir">{{ picture['dir'] }}</td>
      <th class="w3-light-grey"><i class="fa fa-camera"></i> Camera</th>
      <td id="filename">{{ picture['filename'] }}</td>
      <td id="picnum"># {{ picture['id'] }}</td>
    </tr>
    </table>
    <div class="w3-padding w3-border" id="my-slide">
      <img src="/picture/gmshow/{{ picture['id'] }}" width="80%" class="campic">
    </div>
  </div>
</div>
{% endblock %}

{% block csscode %}
.campic { margin: 0 auto; display: block; }
table { border: 5px solid #eee !important; margin-bottom: 10px; }
table, th, td { border: 1px solid #ddd; }
{% endblock %}

{% block jscode %}
var id = {{ picture['id'] }};
var seq = {{ picture['seq'] }};
var dir = "{{ picture['dir'] }}";
var filename = "{{ picture['filename'] }}";

function nextPicture() {
  seq = parseInt(seq) + 1;
  //console.log(id, seq);
  fetch('/ajax/nextgmpic/' + seq)
    .then(function(response) {
       return response.json();
    })
    .then(function(pic) {
       //console.log(pic);
       if (pic.status == 'OK') {
         id = pic.data.id;
         seq = pic.data.seq;
         dir = pic.data.dir;
         filename = pic.data.filename;
         showPicture(id);
       }
    })
    .catch(function(error) {
       console.log('Request failed', error)
    });
}

function showPicture(id) {
  var img = document.querySelector("#my-slide img");
  img.src = '/picture/gmshow/' + id;

  var e = document.querySelector("#dir");
  e.innerText = dir;

  e = document.querySelector("#picnum");
  e.innerText = '# ' + id;

  e = document.querySelector("#filename");
  e.innerText = filename;
}
{% endblock %}

{% block domready %}
  setInterval(function() { nextPicture(); }, 5*1000);
{% endblock %}
