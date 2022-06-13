<?php

if (!isset($_SESSION)) {
    session_start();
}

class UploadSignature
{
    public function __construct()
    {
        $accessTokenClass = new AccessToken();
        $accessTokenClass->getAccessToken();
    }

    public function uploadSignImg()
    {
        // $apiKey = $_SESSION['apiKey'];
        // $apiSecret = $_SESSION['apiSecret'];
        $apiKey = '75A9750537B30BE01F37';   
        $apiSecret = 'A26483D36F334803795B1DA914629446109F3893';

        $email = $_POST['email'];
        $file = $_FILES['file']['tmp_name'];
        $img =  bin2hex(file_get_contents($file));

        $jsonData = array(
            'img' => $img,
            'transparency' => '0',
            'signer' =>
            array(
                'email' => $email
                //'name' => $name,
                //'phonesn' => $phonesn
            )
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
        $url = $_SESSION["url"] . '/signserver/v1/user/signimg?accesstoken=' . $_SESSION["accessToken"] . "&data=" . $data . "&mac=" . $mac;
         $ch = curl_init($url);
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_POST, 1);
         curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

         $result = curl_exec($ch);
         curl_close($ch);
         $obj = json_decode($result);

        //echo var_dump($url);

         if ($obj->{'result'} == 0) {
             echo json_encode("200");
         }
    }
}
