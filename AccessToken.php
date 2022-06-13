<?php

if (!isset($_SESSION)) {
    session_start();
}

class AccessToken
{
    public function getAccessToken()
    {
        $apiKey = $_SESSION['apiKey'];
        $apiSecret = $_SESSION['apiSecret'];

        $url = $_SESSION["url"] . '/signserver/v1/accesstoken?client_id=' . $apiKey . "&state=" . bin2hex(random_bytes(6));
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

                    if (isset($obj->{'at'})) {
                        $_SESSION['accessToken'] = $obj->{'at'};
                    }
                    
                }
            }
        }
    }
}
