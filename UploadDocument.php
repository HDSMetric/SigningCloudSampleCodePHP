<?php

if (!isset($_SESSION)) {
    session_start();
}

class UploadDocument
{
    public function __construct()
    {
        $accessTokenClass = new AccessToken();
        $accessTokenClass->getAccessToken();
    }

    public function uploadDoc()
    {
        $apiKey = $_SESSION['apiKey'];
        $apiSecret = $_SESSION['apiSecret'];

        $email = $_POST['email'];
        $name = $_POST['name'];
        $_SESSION['name'] = $name;
        $file = $_FILES['file']['tmp_name'];
        $filename =$_FILES['file']['name'];
        $str_arr = explode (".", $filename); 

        $cfile = curl_file_create( $file, 'application/pdf');
        $uploadFileHash = hash_file("sha256", $file);

        $jsonData = array(
            'uploadFileHash' => $uploadFileHash,
            'type' => $str_arr[1],
            'contractInfo' =>
            array(
                'contractnum' => '',
                'signernum' => 1,
                'contractname' => 'test',
                'signerinfo' =>
                array(
                    0 =>
                    array(
                        // 'idcardnum' => "980101011234",
                        'phonesn' => '+60143645302',
                        'authtype' => 0 ,
                        'caprovide' =>'1',
                        'name' => $name,
                        'email' => $email,
                    ),
                ),
            ),
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
        $url = $_SESSION["url"] . '/signserver/v1/contract/file?accesstoken=' . $_SESSION["accessToken"] . "&data=" . $data . "&mac=" . $mac;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('uploadFile' => $cfile));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($ch);
        curl_close($ch);
        $obj = json_decode($result);

        if ($obj->{'result'} == 0) {
            echo json_encode("200");
        }
    }
}
