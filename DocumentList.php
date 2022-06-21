<?php

if (!isset($_SESSION)) {
    session_start();
}

class DocumentList
{
    public function __construct()
    {
        $accessTokenClass = new AccessToken();
        $accessTokenClass->getAccessToken();
    }

    public function getDocumentList($contractState)
    {
        $apiKey = $_SESSION['apiKey'];
        $apiSecret = $_SESSION['apiSecret'];

        $startIndex = "0";
        $pageSize = "0";
        $rDetail = "2";

        $jsonData = array(
            'startIndex' => $startIndex ,
            'pageSize' => $pageSize ,
            'rDetail' => $rDetail ,
            'contractState' => $contractState
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
        $url = $_SESSION["url"] . '/signserver/v1/contract/list?accesstoken=' . $_SESSION['accessToken'] . "&data=" . $data . "&mac=" . $mac;
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($ch);
        curl_close($ch);
        $obj = json_decode($result);

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
                    $contractList = $obj->{'contractList'};
                     echo json_encode($contractList);
                }
            }
        }
    }
}
