<?php

$data = fetchData();
$file = saveToFile($data);

uploadToFtpServer($file);
#unlink($file);
deleteOldFiles();

function fetchData()
{
    $url = 'http://172.28.200.27:3000/?command=dataquery&uri=dl:public&format=json&mode=most-recent';

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $text = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($text, true);

    $names = array_column($json['head']['fields'], 'name');
    $values = $json['data'][0]['vals'];
    $data = array_combine($names, $values);

    $data['time'] = substr($json['data'][0]['time'], 0, 10).' '
                  . substr($json['data'][0]['time'], 11, 6).'00';

    return $data;
}

function saveToFile($data)
{
    $title = [
        "time(UTC)",
        "error",
        "lowalarm",
        "highalarm",
        "OAT (Degrees C)",
        "PANELT (Degrees C)",
        "IRR (W/m^2)"
    ];

    $filename = sprintf("mb-x71.%X_1.log.csv", time());

    $fp = fopen($filename, 'w');
    fputcsv($fp, $title);
    fputcsv($fp, [
        $data['time'],
        0, 0, 0,
        $data['amb_temp'],
        $data['cts_temp(1)'],
        $data['srs_irrad(1)'],
    ]);
    fclose($fp);

    return $filename;
}

// Upload to FTP Server in GCS Office

function uploadToFtpServer($file)
{
    $ftphost = '75.98.194.226';
    $ftpuser = 'gcs';
    $ftppass = 'Gcs1234567$$$';

    $local_file = $file;
    $remote_file = "Draker_400GlenHill_001EC6054CCD/$file";

    $conn = ftp_connect($ftphost);
    ftp_login($conn, $ftpuser, $ftppass);
    ftp_pasv($conn, true);
    
    if (ftp_put($conn, $remote_file, $local_file, FTP_ASCII)) {
        echo "Successfully uploaded $local_file\n";
    } else {
        echo "There was a problem while uploading $local_file\n";
    }

    ftp_close($conn);
}

function deleteOldFiles()
{
    $files = glob("*.csv");
    $now   = time();

    foreach ($files as $file) {
        if (is_file($file)) {
            if ($now - filemtime($file) >= 60 * 60 * 2) { // 2 hours
                unlink($file);
            }
        }
    }
}