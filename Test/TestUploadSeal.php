<?php


        $apiKey = '';   
        $apiSecret = '';

        // $file = $_FILES['file']['tmp_name'];
        // $cfile = curl_file_create($file, 'application/pdf');
        // $verifyFileHash = hash_file("sha256", $file);


        $jsonData = array(
            'img' => 'ffd8ff',
            'transparency' => '0',
            'signer' => array(
                'email' => 'gs58330@student.upm.edu.my'
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
        $url = $_SESSION["url"] . '/signserver/v1/contract/signature/verify?accesstoken=' . $_SESSION["accessToken"] . "&data=" . $data . "&mac=" . $mac;
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

        if ($obj->{'result'} == 0) {
            echo json_encode("200");
        } elseif ($obj->{'result'} == 91) {
            echo json_encode("400");
        }
