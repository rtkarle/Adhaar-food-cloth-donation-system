<?php
require __DIR__ . '/vendor/autoload.php';

$client = new Google_Client();

$client->setClientId("626415570779-24ehd9d4nr3cti5202s0amt7sqm26l6l.apps.googleusercontent.com");
$client->setClientSecret("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");

if($_SERVER['HTTP_HOST'] == "localhost"){
    $redirect = "http://localhost/adhaar/google_callback.php";
}else{
    $redirect = "https://soulserves.22web.org/google_callback.php";
}

$client->setRedirectUri($redirect);

$client->addScope("email");
$client->addScope("profile");

?>
