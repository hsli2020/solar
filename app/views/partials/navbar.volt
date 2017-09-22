<div class="w3-container w3-top w3-black w3-medium" style="z-index:4">
<ul class="w3-navbar w3-card-2 ">
  <li><a href="/dashboard" class="w3-hover-teal">Home</a></li>
  <li><a href="/dashboard" class="w3-hover-teal">Dashboard</a></li>

  <li class="w3-dropdown-hover">
    <a href="javascript:;" class="w3-hover-teal">Report <i class="fa fa-caret-down"></i></a>
    <div class="w3-dropdown-content w3-white w3-card-4">
      <a href="/report/daily" class="w3-hover-teal">Daily Report</a>
      <a href="/report/monthly" class="w3-hover-teal">Monthly Report</a>
    </div>
  </li>

  <li class="w3-dropdown-hover">
    <a href="javascript:;" class="w3-hover-teal">Tools <i class="fa fa-caret-down"></i></a>
    <div class="w3-dropdown-content w3-white w3-card-4">
      <a href="/project/export" class="w3-hover-teal">Data Exporting</a>
      <a href="#" class="w3-hover-teal">Analytic Tool</a>
      <hr style="margin:0.5em;">
      <a href="#" class="w3-hover-teal">Data Range</a>
    </div>
  </li>

  <li class="w3-dropdown-hover w3-right">
    <a href="javascript:;" class="w3-hover-teal">Profile <i class="fa fa-caret-down"></i></a>
    <div class="w3-dropdown-content w3-white w3-card-4" style="right:0">
      <a href="#" class="w3-hover-teal">Settings</a>
      {% if auth['role'] == 1 -%}
      <a href="/user/add" class="w3-hover-teal">Add New User</a>
      {% endif -%}
      <a href="/user/change-password" class="w3-hover-teal">Change Password</a>
      <a href="/user/logout" class="w3-hover-teal">Log out</a>
    </div>
  </li>

  <li class="w3-right"><a href="#" class="w3-hover-teal">{{ auth['username'] }}</a></li>
</ul>
</div>
