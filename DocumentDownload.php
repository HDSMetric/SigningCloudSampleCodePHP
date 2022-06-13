<?php

if (!isset($_SESSION)) {
    session_start();
}

$apiSecret = $_SESSION['apiSecret'];

$contractnum = $_GET['contractnum'];
$jsonData = array(
    'contractnum' => $contractnum
);

$jsonDataEncoded = json_encode($jsonData);
$key = hash('sha256', $apiSecret, true);
// encryption to get encrypted data
$ciphertext = openssl_encrypt($jsonDataEncoded, "aes-256-ecb", $key, $options = 0, $iv = "");
$data = base64_decode($ciphertext);
$data = bin2hex($data);
// integrity hmac calculation
$mac = hash('sha256', $data . $apiSecret);

//GET request
$url = $_SESSION["url"] . '/signserver/v1/contract/file?accesstoken=' . $_SESSION['accessToken'] . "&data=" . $data . "&mac=" . $mac;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

$result = curl_exec($ch);
curl_close($ch);

$obj = json_decode($result);

//echo var_dump($obj);

if ($obj->{'result'} == 0) {
    $data = $obj->{'data'};
    $mac = $obj->{'mac'};

    if (isset($data) && isset($mac)) {
        $calcmac = hash('sha256', $data . $apiSecret);

        if (hash_equals($mac, $calcmac)) {
            // data integrity check pass
            // start decrypt
            $key = hash('sha256', $apiSecret, true);
            $data = hex2bin($data);
            $data = base64_encode($data);

            $original_plaintext = openssl_decrypt($data, "aes-256-ecb", $key, $options = 0, $iv = "");
            $obj = json_decode($original_plaintext);

            $pdfdata = $obj->{'pdfdata'};

            $pack = pack("H*", $pdfdata);
            // if using local XAMPP , need to remove /tmp/ path below.
            // else for server configuration , please include /tmp/
            $file = fopen("/tmp/".$contractnum.".pdf", "w");
            fwrite($file, $pack);
            fclose($file);
            
            if (file_exists("/tmp/".$contractnum.".pdf")) {
    
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.$contractnum.'.pdf');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize("/tmp/".$contractnum.".pdf"));
                flush();
                readfile("/tmp/".$contractnum.".pdf");
                die();
            }

            unlink("/tmp/".$contractnum.".pdf");

            /* file_put_contents($contractnum. '.pdf', $pack);
            header('Content-type: application/pdf');
            header('Content-Disposition: attachment; filename=' . $contractnum . '.pdf'); */
        }
    }
}
