<?php

$DC_Nameplate_Capacity = 13305; // project-specific
$AC_Nameplate_Capacity = 10000; // project-specific

$Module_Power_Coefficient = -0.43;
$Inverter_Efficiency = 0.98;
$Transformer_Loss = 0.015;
$Other_Loss = 0.020;

$Avg_Irradiance_POA = 594.816;  // avg 60 minutes
$Avg_Module_Temp = 37;          // PANELT
$Measured_Energy = 7483;        // sum 60 minutes

# Theoretical Output = (Sunshine IRR/1000) X DC Nameplate Capacity
#                    - Module Temperature Losses
#                    - inverter efficiency losses (limited to inverter nameplate)
#                    - transformer losses - other system losses,

$Maximum_Theory_Output = ($Avg_Irradiance_POA / 1000) * $DC_Nameplate_Capacity;

$Temperature_Losses = ($Maximum_Theory_Output * ($Module_Power_Coefficient * (25 - $Avg_Module_Temp))) / 1000.0;
$Inverter_Losses = (1 - $Inverter_Efficiency) * ($Maximum_Theory_Output - $Temperature_Losses);

if (($Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses) > $AC_Nameplate_Capacity)
    $Inverter_Clipping_Loss = $Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses - $AC_Nameplate_Capacity;
else
    $Inverter_Clipping_Loss = 0;

$Transformer_Losses  = ($Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses - $Inverter_Clipping_Loss) * $Transformer_Loss;
$Other_System_Losses = ($Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses - $Inverter_Clipping_Loss - $Transformer_Loss) * $Other_Loss;
$Total_Losses = ($Temperature_Losses + $Inverter_Losses + $Inverter_Clipping_Loss + $Transformer_Loss + $Other_System_Losses) / $Maximum_Theory_Output;
$Theoretical_Output = ($Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses - $Inverter_Clipping_Loss - $Transformer_Loss - $Other_System_Losses);

$GCS_Performance_Index = $Measured_Energy / $Theoretical_Output;

echo "DC_Nameplate_Capacity     = $DC_Nameplate_Capacity\n";
echo "AC_Nameplate_Capacity     = $AC_Nameplate_Capacity\n\n";

echo "Module_Power_Coefficient  = $Module_Power_Coefficient\n";
echo "Inverter_Efficiency       = $Inverter_Efficiency\n";
echo "Transformer_Loss          = $Transformer_Loss\n";
echo "Other_Loss                = $Other_Loss\n\n";

echo "Avg_Irradiance_POA        = $Avg_Irradiance_POA\n";
echo "Avg_Module_Temp           = $Avg_Module_Temp\n";
echo "Measured_Energy           = $Measured_Energy\n\n";

echo "Maximum_Theory_Output     = $Maximum_Theory_Output\n";
echo "Temperature_Losses        = $Temperature_Losses\n";
echo "Inverter_Losses           = $Inverter_Losses\n";
echo "Inverter_Clipping_Loss    = $Inverter_Clipping_Loss\n";
echo "Transformer_Losses        = $Transformer_Losses\n";
echo "Other_System_Losses       = $Other_System_Losses\n";
echo "Total_Losses              = $Total_Losses\n";
echo "Theoretical_Output        = $Theoretical_Output\n\n";

echo "GCS_Performance_Index     = $GCS_Performance_Index\n";
