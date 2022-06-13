<?php

if (!isset($_SESSION)) {
    session_start();
}

class VerifyDocument
{
    public function __construct()
    {
        $accessTokenClass = new AccessToken();
        $accessTokenClass->getAccessToken();
    }

    public function verifyDoc()
    {
        $apiKey = $_SESSION['apiKey'];
        $apiSecret = $_SESSION['apiSecret'];
        // $apiKey = '75A9750537B30BE01F37';   
        // $apiSecret = 'A26483D36F334803795B1DA914629446109F3893';

        $file = $_FILES['file']['tmp_name'];
        $filename = $_FILES['file']['name'];
        $cfile = curl_file_create( $file, 'application/pdf' , $filename);
        $verifyFileHash = hash_file("sha256", $file);


        $jsonData = array(
            'verifyFileHash' => $verifyFileHash
        );

        $jsonDataEncoded = json_encode($jsonData);

        $key = hash('sha256', $apiSecret, true);
        // encryption to get encrypted data
        $ciphertext = openssl_encrypt($jsonDataEncoded, "aes-256-ecb", $key, $options = 0, $iv = "");
        $data = base64_decode($ciphertext);
        $data = bin2hex($data);
        // integrity hmac calculation
        $mac = hash('sha256', $data . $apiSecret);

        // data for POST method upload document
        $url = $_SESSION["url"] . '/signserver/v1/contract/signature/verify?accesstoken=' . $_SESSION["accessToken"] . "&data=" . $data . "&mac=" . $mac;
        // echo var_dump($url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('verifyFile' => $cfile));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        
        // echo var_dump($url);

        $result = curl_exec($ch);
        curl_close($ch);
        $obj = json_decode($result);
        // echo var_dump($obj);
        if ($obj->{'result'} == 0) {
            echo json_encode("200");
        } else if ($obj->{'result'} == 91) {
            echo json_encode("400");
        } 
    }
}
