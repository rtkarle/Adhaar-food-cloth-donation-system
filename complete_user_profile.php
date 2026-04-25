<?php
session_start();
require "db.php";

if(!isset($_SESSION['google_email'])){
    header("Location: login.php");
    exit;
}

if(isset($_POST['role'])){

    $name  = $_SESSION['google_name'];
    $email = $_SESSION['google_email'];
    $role  = $_POST['role'];

    $randomPass = password_hash(uniqid(), PASSWORD_DEFAULT);

    // check if user exists
    $stmt = $conn->prepare("SELECT id FROM register WHERE email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows == 0){
        $stmt = $conn->prepare("INSERT INTO register(name,email,password,role,verified) VALUES (?,?,?,?,1)");
        $stmt->bind_param("ssss",$name,$email,$randomPass,$role);

        if(!$stmt->execute()){
            die("Insert Error: ".$stmt->error);
        }
    } else {
        $stmt = $conn->prepare("UPDATE register SET role=?, verified=1 WHERE email=?");
        $stmt->bind_param("ss",$role,$email);
        $stmt->execute();
    }

    $_SESSION['user'] = $email;
    $_SESSION['role'] = $role;

    if($role == "donor"){
        header("Location: donor_dashboard.php");
    } else {
        header("Location: volunteer_dashboard.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<body>
<h2>Select Role</h2>
<form method="POST">
    <button name="role" value="donor">I am Donor</button>
    <button name="role" value="volunteer">I am Volunteer</button>
</form>
</body>
</html>
