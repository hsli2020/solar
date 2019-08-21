<div class="w3-container w3-top w3-black w3-medium" style="z-index:4">
<ul class="w3-navbar">
  <li><a href="/" class="w3-hover-teal">Home</a></li>

  <li class="w3-dropdown-hover">
    <a href="javascript:;" class="w3-hover-teal">Dashboard <i class="fa fa-caret-down"></i></a>
    <div class="w3-dropdown-content w3-white w3-card-4">
      <a href="/dashboard/full" class="w3-hover-teal">Full View</a>
      <a href="/dashboard/compact" class="w3-hover-teal">Compact View</a>
      <hr style="margin: 0;">
      <a href="/dashboard/sites/all" class="w3-hover-teal">All Sites</a>
      <a href="/dashboard/sites/gm" class="w3-hover-teal">GM Sites</a>
      <a href="/dashboard/sites/rooftop" class="w3-hover-teal">Rooftop Sites</a>
    </div>
  </li>

  <li class="w3-dropdown-hover">
    <a href="javascript:;" class="w3-hover-teal">Report <i class="fa fa-caret-down"></i></a>
    <div class="w3-dropdown-content w3-white w3-card-4">
      <a href="/report/daily" class="w3-hover-teal">Daily Report</a>
      <a href="/report/monthly" class="w3-hover-teal">Monthly Report</a>
      {% if auth['role'] == 1 -%}
      <hr style="margin:0.5em;">
      <a href="/report/budget" class="w3-hover-teal">Monthly Budgets</a>
      {% endif -%}
    </div>
  </li>

  <li class="w3-dropdown-hover">
    <a href="javascript:;" class="w3-hover-teal">Tools <i class="fa fa-caret-down"></i></a>
    <div class="w3-dropdown-content w3-white w3-card-4">
      <a href="/project/export" class="w3-hover-teal">Data Exporting</a>
      <a href="/project/exportdaily" class="w3-hover-teal">Daily Data Exporting</a>
      <hr style="margin: 0;">
      <a href="/project/compare" class="w3-hover-teal">Analytic Tool</a>
{#
      <hr style="margin:0.5em;">
      <a href="#" class="w3-hover-teal">User Settings</a>
      <a href="#" class="w3-hover-teal">Smart Alert Settings</a>
#}
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
