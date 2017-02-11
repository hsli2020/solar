<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <style>
    table { border-collapse: collapse; }
    table, td, th { border: 1px solid gray; padding: 5px 5px; }
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
  <td><?= implode("</td>\n  <td>", $data); ?></td>
</tr>
<?php } ?>
</table>

<p>The <b>Daily Report</b> is also attached in MS Excel format.</p>

</body>
</html>
