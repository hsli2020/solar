{% extends "layouts/base.volt" %}

{% block main %}
<div class="w3-container">
  <div class="w3-row">
    <div class="w3-padding w3-border">
      <img src="/picture/gmshow/100" width="80%" class="campic">
      <p></p>
    </div>
  </div>
</div>
{% endblock %}

{% block csscode %}
.campic { margin: 0 auto; display: block; }
{% endblock %}

{% block jscode %}
var picture = 0;

function currentPicture(id) {
  showPicture(picture = id);
}

function nextPicture() {
  fetch('/ajax/nextpic/' + picture)
    .then(function(response) {
       return response.json();
    })
    .then(function(data) {
       console.log(data);
       if (data.status == 'OK') {
         showPicture(picture = data.picture.id);
       }
    })
    .catch(function(error) {
       console.log('Request failed', error)
    });
}

function prevPicture() {
  fetch('/ajax/prevpic/' + picture)
    .then(function(response) {
       return response.json();
    })
    .then(function(data) {
       console.log(data);
       if (data.status == 'OK') {
         showPicture(picture = data.picture.id);
       }
    })
    .catch(function(error) {
       console.log('Request failed', error)
    });
}

function showPicture(id) {
  var img = document.querySelector("#mySlide img");
  img.src = '/picture/show/' + id;
}
{% endblock %}

{% block domready %}
{% endblock %}
