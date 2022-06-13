<?php

if (!isset($_SESSION)) {
    session_start();
}

class StoreAPIKey
{
    public function storeKey()
    {
        $key = $_POST['apiKey'];
        $secret = $_POST['apiSecret'];
        $email = $_POST['email'];

        $_SESSION['apiKey'] = $key;
        $_SESSION['apiSecret'] = $secret;
        $_SESSION['email'] = $email;
        $_SESSION['url'] = "https://stg-env.signingcloud.com";

        echo json_encode("200");
    }
}