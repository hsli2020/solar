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

<p>Following is the Monthly Solar Energy Production Report of <b><?= $date; ?></b></p>

<table>
<tr>
  <th rowspan="2">Project</th>
  <th rowspan="2">Month-Year</th>
  <th colspan="2">Insolation</th>
  <th colspan="3">Energy Production</th>
  <th colspan="3">Performance</th>
</tr>
<tr>
  <th>Actual<br>[kWh/m<sup>2</sup>]</th>
  <th>Reference<br>[kWh/m<sup>2</sup>]</th>
  <th>Measured<br>[kWh]</th>
  <th>Expected<br>[kWh]</th>
  <th>Budget<br>[kWh]</th>
  <th>Actual<br>Budget</th>
  <th>Actual<br>Expected</th>
  <th>Weather<br>Performance%</th>
</tr>

<?php $index = 1; ?>
<?php foreach ($report as $data) { ?>
<tr>
  <td><?= $data['Project_Name']; ?></td>
  <td><?= $data['Date']; ?></td>
  <td><?= $data['Insolation_Actual']; ?></td>
  <td><?= $data['Insolation_Reference']; ?></td>
  <td><?= $data['Energy_Expected']; ?></td>
  <td><?= $data['Energy_Measured']; ?></td>
  <td><?= $data['Energy_Budget']; ?></td>
  <td><?= $data['Actual_Budget']; ?></td>
  <td><?= $data['Actual_Expected']; ?></td>
  <td><?= $data['Weather_Performance']; ?></td>
</tr>
<?php } ?>
</table>

<p>The Monthly Report is also attached in Microsoft Excel format.</p>

</body>
</html>
