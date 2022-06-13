<?php

if (!isset($_SESSION)) {
    session_start();
}

class ManualSignDocument
{
    public function __construct()
    {
        $accessTokenClass = new AccessToken();
        $accessTokenClass->getAccessToken();
    }

    public function getSignUrl()
    {
        $apiKey = $_SESSION['apiKey'];
        $apiSecret = $_SESSION['apiSecret'];

        $email = $_SESSION['email'];
        //$contractNumber = $_POST['contractnum'];
        $contractNumber = '26CF454AD2C9DC21B5CE9650C3CB00E0';
        
        if (!empty($email) && !empty($contractNumber)) {
            $jsonData = array(
                    'signerInfo' =>
                    array(
                        'email' => $email
                    ),
                     'contractnum' => $contractNumber,
            );
        
            $jsonDataEncoded = json_encode($jsonData);
            $key = hash('sha256', $apiSecret, true);
            // encryption to get encrypted data
            $ciphertext = openssl_encrypt($jsonDataEncoded, "aes-256-ecb", $key, $options = 0, $iv = "");
            $data = base64_decode($ciphertext);
            $data = bin2hex($data);
            // integrity hmac calculation
            $mac = hash('sha256', $data . $apiSecret);

            $url = $_SESSION["url"] . '/signserver/v1/contract/signature/manual?accesstoken='. $_SESSION["accessToken"] . "&data=" . $data . "&mac=" . $mac;
            //$url = 'https://stg-env.signingcloud.com/signserver/v1/contract/signature/manual?accesstoken='. $_SESSION["accessToken"] . "&data=" . $data . "&mac=" . $mac;
            //echo $url;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POSTFIELDS, null);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
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
        
                        if (isset($obj->{'url'})) {
                            $signUrl = $obj->{'url'};
                            echo $signUrl;
                        }
                    }
                }
            }
        }
    }
}
