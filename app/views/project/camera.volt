{% extends "layouts/base.volt" %}

{% block main %}
<div class="w3-container">
  <div class="w3-row">
    {% for picture in pictures %}
    <div class="w3-half w3-padding w3-border">
      <img src="/picture/show/{{ picture['id'] }}" width="100%" class="campic" onclick="openModal(); currentPicture({{ picture['id'] }})">
      <p>[ {{ project.name }} ] - {{ picture['camera'] }}<span class="w3-right">{{ picture['id'] }}</span></p>
    </div>
    {% endfor %}
  </div>
</div>

<div id="myModal" class="modal">
  <span class="close" onclick="closeModal()">&times;</span>
  <div class="modal-content">
    <div id="mySlide">
      <img src="" style="width:100%">
    </div>
    <a class="prev" onclick="prevPicture()">&#10094;</a>
    <a class="next" onclick="nextPicture()">&#10095;</a>
  </div>
</div>
{% endblock %}

{% block csscode %}
.campic { cursor: pointer; }
.modal {
  display: none;
  position: fixed;
  z-index: 99;
  padding-top: 80px;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: black;
}

.modal-content {
  position: relative;
  background-color: #fefefe;
  margin: auto;
  padding: 0;
  width: 90%;
  max-width: 1280px;
}

.close {
  color: white;
  position: absolute;
  top: 10px;
  right: 25px;
  font-size: 35px;
  font-weight: bold;
}

.close:hover, .close:focus {
  color: #999;
  text-decoration: none;
  cursor: pointer;
}

.mySlides { display: none; }

.prev, .next {
  cursor: pointer;
  position: absolute;
  top: 50%;
  width: auto;
  padding: 16px;
  margin-top: -50px;
  color: white;
  font-weight: bold;
  font-size: 20px;
  transition: 0.6s ease;
  border-radius: 0 3px 3px 0;
  user-select: none;
  -webkit-user-select: none;
  background-color: #888;
}
.next { right: 0; border-radius: 3px 0 0 3px; }
.prev:hover, .next:hover { background-color: rgba(0, 0, 0, 0.8); }
{% endblock %}

{% block jscode %}
var project = 9;
var camera  = 1;
var picture = 0;

function openModal() {
  document.getElementById('myModal').style.display = "block";
}

function closeModal() {
  document.getElementById('myModal').style.display = "none";
}

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

{% block cssfile %}
{% endblock %}

{% block jsfile %}
{% endblock %}

{% block domready %}
{% endblock %}
