<?php

include __DIR__ . '/../public/init.php';

$url1 = 'http://10.206.8.71/?command=NewestRecord&table=Minute_Output';
$url2 = 'http://10.206.8.76/?command=NewestRecord&table=Minute_Output';

$values['envkit1'] = getValues($url1);
$values['envkit2'] = getValues($url2);

//print_r($values);

saveEnvKit('p7_mb_071_envkit', $values['envkit1']);
saveEnvKit('p7_mb_076_envkit', $values['envkit2']);

function saveEnvKit($table, $data)
{
    $di = \Phalcon\Di::getDefault();
    $db = $di->get('db');

	try {
		$db->insertAsDict($table, [
			'time'        => $data['Record Date'],
			'error'       => 0,
			'low_alarm'   => 0,
			'high_alarm'  => 0,
			'OAT'         => $data['AirTemp'],
			'PANELT'      => $data['BOMTemp_C'],
			'IRR'         => $data['CMP11_POA'],
		]);
	} catch(Exception $e) {
		echo $e->getMessage(), PHP_EOL;
	}
}

function getValues($url)
{
    #$fieldNames = [
    #    'Table Name: ',
    #    'Current Record: ',
    #    'Record Date: ',
    #    'CMP11_GHI_Avg',
    #    'CMP11_GHI',
    #    'CMP11_POA_Avg',
    #    'CMP11_POA',
    #    'AirTemp_Avg',
    #    'AirTemp',
    #    'BOMTemp_C_Avg',
    #    'BOMTemp_C',
    #    'WindSpd_AVG',
    #    'WindSpd',
    #    'WindDir_AVG',
    #    'WindDir_StDev',
    #    'GHI_FanSpd_RPS_Avg',
    #    'POA_FanSpd_RPS_Avg',
    #];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $html = curl_exec($ch);
    curl_close($ch);

    $lines = explode("\n", $html);
    //print_r($lines);

    $values = [];
    foreach ($lines as $line) {
        $line = strip_tags(str_replace('</th><td>', ':', $line));
        $fields = explode(':', $line, 2);
        if (count($fields) == 2) {
            $name = $fields[0];
            $value = $fields[1];
            $values[$name] = trim($value);
        }
    }

    $values['Record Date'] = gmdate('Y-m-d H:i:s', strtotime($values['Record Date']));
    //print_r($values);

    return $values;
}
