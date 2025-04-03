<?php
    ob_start();
    session_start();
    include('inc/header.php');
    include 'utility.php';
    $utility = new request();
    $utility->checkLogin();
?>

<div class="wrapper">
    <?php include('./menus.php');?>
    <div class="main" id="content" data-role="<?php echo $_SESSION['department']?>">

    </div>
</div>

<?php include('inc/footer.php');?>