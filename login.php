<?php 
    session_start();
    include('inc/header.php');
?>
  <link rel="stylesheet" href="./css/login.css">
<?php include('./inc/container.php'); ?>
        <!--Title-->
        <div  class="title">
            <h1>Welcome to RFQ System</h1>
        </div>

        <!--Login Form-->
        <div class="glass-card">
            <div class="glass-card-body">
                <div class="mb-3">
                    <h2>Login Your Account</h2>
                </div>
                <form action="./controller/authentication.php" method="post" id="loginForm">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" aria-describedby="passHelp" required>
                        <div id="passHelp" class="form-text">Never share your password with anyone else.</div>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    </div>
                </form>
            </div>
        </div>
<?php include('inc/footer.php');?>