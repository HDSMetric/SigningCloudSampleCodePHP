<?php

if (!isset($_SESSION)) {
    session_start();
}

class AutoSignDocument
{
    public function __construct()
    {
        $accessTokenClass = new AccessToken();
        $accessTokenClass->getAccessToken();
    }

    public function autoSign()
    {
        $apiKey = $_SESSION['apiKey'];
        $apiSecret = $_SESSION['apiSecret'];
        $email = $_SESSION['email'];
        $contractNumber = $_POST['contractnum'];
        $name = $_SESSION['name'];
        $jsonData = array(
            'signerInfo' =>
            array(
                'email' => $email,
                'name' => $name,
                'signkeyword' => 'Sign'
            ),
            'contractnum' => $contractNumber,
        );

        // 'callUrl' => 'https://demo.securemetric.com:447/apicallback',

        $jsonDataEncoded = json_encode($jsonData);

        $key = hash('sha256', $apiSecret, true);
        // encryption to get encrypted data
        $ciphertext = openssl_encrypt($jsonDataEncoded, "aes-256-ecb", $key, $options = 0, $iv = "");
        $data = base64_decode($ciphertext);
        $data = bin2hex($data);
        // integrity hmac calculation
        $mac = hash('sha256', $data . $apiSecret);

        $url = $_SESSION["url"] . '/signserver/v1/contract/signature/automatic?accesstoken=' . $_SESSION['accessToken'] . "&data=" . $data . "&mac=" . $mac;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, null);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($ch);
        curl_close($ch);
        $obj = json_decode($result);

        if ($obj->{'result'} == 0) {
            echo "200";
        }
    }
}
