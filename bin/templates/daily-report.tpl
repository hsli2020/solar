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

<p>Following is the Daily Solar Energy Production Report of <b><?= $date; ?></b></p>

<table>
<tr>
  <!-- <th rowspan="2">No.</th> -->
  <th rowspan="2">Project Name</th>
  <!-- <th rowspan="2">Date</th> -->
  <th colspan="2">Capacity<br>(kW)</th>
  <th>Monthly Budget</th>
  <th>IE POA Insolation</th>
  <th>Total Insolation</th>
  <th>Total Energy</th>
  <th>Daily Expected</th>
  <th>Daily Production</th>
  <th>Measured POA Insolation</th>
  <th>Actual /Budget</th>
  <th>Actual /Expected</th>
  <th>Weather Performance</th>
</tr>
<tr>
  <th>AC</th>
  <th>DC</th>
  <th>kWh</th>
  <th>kWh/m<sup>2</sup></th>
  <th>kWh/m<sup>2</sup></th>
  <th>kWh</th>
  <th>kWh</th>
  <th>kWh</th>
  <th>kWh/m<sup>2</sup></th>
  <th>%</th>
  <th>%</th>
  <th>%</th>
</tr>

<?php $index = 1; ?>
<?php foreach ($report as $data) { ?>
<tr>
  <!-- <td><?= $index++; ?></td> -->
  <td><?= $data['Project_Name']; ?></td>
  <!-- <td><?= $data['Date']; ?></td> -->
  <td><?= $data['Capacity_AC']; ?></td>
  <td><?= $data['Capacity_DC']; ?></td>
  <td><?= $data['Monthly_Budget']; ?></td>
  <td><?= $data['IE_Insolation']; ?></td>
  <td><?= $data['Total_Insolation']; ?></td>
  <td><?= $data['Total_Energy']; ?></td>
  <td><?= $data['Daily_Expected']; ?></td>
  <td><?= $data['Daily_Production']; ?></td>
  <td><?= $data['Measured_Insolation']; ?></td>
  <td><?= $data['Actual_Budget']; ?></td>
  <td><?= $data['Actual_Expected']; ?></td>
  <td><?= $data['Weather_Performance']; ?></td>
</tr>
<?php } ?>
</table>

<p>The Daily Report is also attached in Microsoft Excel format.</p>

</body>
</html>
