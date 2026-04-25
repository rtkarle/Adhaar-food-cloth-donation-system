<?php
session_start();
require "google_config.php";
require "db.php";

error_reporting(E_ALL);
ini_set('display_errors',1);

if(!isset($_GET['code'])){
    header("Location: login.php");
    exit;
}

$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

if(isset($token['error'])){
    die("Google Login Failed");
}

$client->setAccessToken($token['access_token']);

$google_oauth = new Google_Service_Oauth2($client);
$user = $google_oauth->userinfo->get();

$email = $user->email;
$name  = $user->name;

/* check user */
$stmt = $conn->prepare("SELECT role FROM register WHERE email=?");
$stmt->bind_param("s",$email);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows > 0){

    $row = $res->fetch_assoc();

    if($row['role'] == "donor"){
        $_SESSION['user'] = $email;
        $_SESSION['role'] = "donor";
        header("Location: donor_dashboard.php");
        exit;
    }
    elseif($row['role'] == "volunteer"){
        $_SESSION['user'] = $email;
        $_SESSION['role'] = "volunteer";
        header("Location: volunteer_dashboard.php");
        exit;
    }
}

/* New user OR role NULL */
$_SESSION['google_name'] = $name;
$_SESSION['google_email'] = $email;

header("Location: complete_user_profile.php");
exit;
