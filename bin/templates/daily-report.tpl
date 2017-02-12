<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <style>
    table { border-collapse: collapse; }
    table, td, th { border: 1px solid gray; padding: 5px 5px; text-align: center; }
  </style>
</head>
<body>

<p>Following is the <b>Daily Solar Energy Production Report</b></p>

<table>
<tr>
  <th>No.</th>
  <th>Project Name</th>
  <th>Date</th>
  <th>Capacity AC</th>
  <th>Capacity DC</th>
  <th>Budget</th>
  <th>Expected</th>
  <th>Mesured Production</th>
  <th>Measured POA Insolation</th>
  <th>IE POA Insolation</th>
  <th>Actual /Budget</th>
  <th>Actual /Expected</th>
  <th>Weather Performance</th>
</tr>

<?php $index = 1; ?>
<?php foreach ($report as $data) { ?>
<tr>
  <td><?= $index++; ?></td>
  <td><?= $data['project_Name']; ?></td>
  <td><?= $data['date']; ?></td>
  <td><?= $data['capacity_AC']; ?></td>
  <td><?= $data['capacity_DC']; ?></td>
  <td><?= $data['budget']; ?></td>
  <td><?= $data['expected']; ?></td>
  <td><?= $data['measured_Production']; ?></td>
  <td><?= $data['measured_Insolation']; ?></td>
  <td><?= $data['IE_POA_Insolation']; ?></td>
  <td><?= $data['actual_Budget']; ?></td>
  <td><?= $data['actual_Expected']; ?></td>
  <td><?= $data['weather_Performance']; ?></td>
</tr>
<?php } ?>
</table>

<p>The <b>Daily Report</b> is also attached in MS Excel format.</p>

</body>
</html>
