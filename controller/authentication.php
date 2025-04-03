<?php
    ob_start();
    session_start();
    include '../utility.php';
    $utility = new request();
    
    if(isset($_POST['email']) && isset($_POST['password']))
    {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $isVerified = $utility->login($email, $password);

        if ($isVerified) {

            $_SESSION['email'] = $isVerified['email'];
            $_SESSION['role'] = $isVerified['role'];
            $_SESSION['department'] = $isVerified['department'];
            $_SESSION['name'] = $isVerified['fullName'];
            header("Location: ../index.php");
            exit();
        }
        else{
            header("Location: ../login.php");
            exit();
        }
    }
    else
    {
        header("Location: ../login.php");

    }
?>