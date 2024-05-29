<?php
ob_start();
require("../account/php/db.php");
session_start();
if (!isset($_GET['uid'])) {
  header('location: index.php');


}

$uid = $_GET['uid'];
$sql = "SELECT * FROM users WHERE uid = '$uid'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_all($result, MYSQLI_ASSOC);


if(isset($_POST['update'])){
    $balance = $_POST['balance'];
    $password = $_POST['password'];
    $bonus = $_POST['bonus'];
    $withdrawal = $_POST['withdrawals'];
    $deposit = $_POST['deposit'];
    $date = $_POST['date'];
    $sql = "UPDATE users SET balance= $balance, password= '$password', bonus= $bonus, totalwithdrawal= $withdrawal , totaldeposit= $deposit, date='$date' WHERE uid = '$uid'";
    if (mysqli_query($conn, $sql)) {
       header('location: index.php');
    }
   
}
if(isset($_POST['delete'])){
    $sql = "DELETE FROM users WHERE uid = '$uid'";
    if (mysqli_query($conn, $sql)) {
       header('location: index.php');
    }
}
?>
<!doctype html>
<html lang="en">

 
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>User Details</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
    <link href="assets/vendor/fonts/circular-std/style.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/libs/css/style.css">
    <link rel="stylesheet" href="assets/vendor/fonts/fontawesome/css/fontawesome-all.css">
</head>

<body>
    <!-- ============================================================== -->
    <!-- main wrapper -->
    <!-- ============================================================== -->
    <div class="dashboard-main-wrapper">
         <!-- ============================================================== -->
        <!-- navbar -->
        <!-- ============================================================== -->
         
        <!-- ============================================================== -->
        <!-- end navbar -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- left sidebar -->
        <!-- ============================================================== -->
      
        <!-- ============================================================== -->
        <!-- end left sidebar -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- wrapper  -->
        <!-- ============================================================== -->
        <div class="dashboard-wrapper">
            <div class="container-fluid  dashboard-content">
                <!-- ============================================================== -->
                <!-- pageheader -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"><?php echo $user['0']['name'];?> Details </h2>
                            <p class="pageheader-text">Proin placerat ante duiullam scelerisque a velit ac porta, fusce sit amet vestibulum mi. Morbi lobortis pulvinar quam.</p>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Users</a></li>
                                        <li class="breadcrumb-item active" aria-current="page"><?php echo $user['0']['name'];?></li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- end pageheader -->
                <!-- ============================================================== -->
             
                    <div class="row">
                        <!-- ============================================================== -->
                        <!-- validation form -->
                        <!-- ============================================================== -->
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                            <div class="card">
                                <h5 class="card-header"><?php echo $user['0']['name'];?> account details</h5>
                                <div class="card-body">
                                    <form  method="post" action="">
                                        <div class="row">
                                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 ">
                                                <label for="validationCustom01">name</label>
                                                <input type="text" class="form-control" id="validationCustom01" placeholder="First name" value="<?php echo $user['0']['name'];?>" required>
                                                <div class="valid-feedback">
                                                
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 ">
                                                <label for="validationCustom01">email</label>
                                                <input type="text" class="form-control" id="validationCustom01" placeholder="email address" value="<?php echo $user['0']['email'];?>" required>
                                                <div class="valid-feedback">
                                                
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 ">
                                                <label for="validationCustom01">phone number</label>
                                                <input type="text" class="form-control" id="validationCustom01" placeholder="phone nmuber" value="<?php echo $user['0']['phone'];?>" required>
                                                <div class="valid-feedback">
                                                
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 ">
                                                <label for="validationCustom01">password</label>
                                                <input type="text" class="form-control" id="validationCustom01" placeholder="password" name="password" value="<?php echo $user['0']['password'];?>" required>
                                                <div class="valid-feedback">
                                                
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 ">
                                                <label for="validationCustom01">Balance</label>
                                                <input type="text" class="form-control" id="validationCustom01"name="balance" placeholder="balance" value="<?php echo $user['0']['balance'];?>" required>
                                                <div class="valid-feedback">
                                                
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 ">
                                                <label for="validationCustom01">Total Withdrawals</label>
                                                <input type="text" class="form-control" id="validationCustom01" name = "withdrawals" placeholder="withdrawals" value="<?php echo $user['0']['totalwithdrawal'];?>" required>
                                                <div class="valid-feedback">
                                                
                                                </div>
                                            </div>
                                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 ">
                                                <label for="validationCustom01">Total Deposits</label>
                                                <input type="text" class="form-control" id="validationCustom01" name="deposit" placeholder="deposits" value="<?php echo $user['0']['totaldeposit'];?>" required>
                                                <div class="valid-feedback">
                                                
                                                </div>
                                            </div>
                                           
                                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 ">
                                                <label for="validationCustom01">Bonus</label>
                                                <input type="text" class="form-control" id="validationCustom01" name= "bonus" placeholder="Bonus" value="<?php echo $user['0']['bonus'];?>" required>
                                                <div class="valid-feedback">
                                                
                                                </div>
                                            </div>
                                            
                                            
                                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 ">
                                                <label for="validationCustom01">Registration Date</label>
                                                <input type="text" class="form-control" id="validationCustom01" placeholder="date" name='date' value="<?php echo $user['0']['date'];?>" required>
                                                <div class="valid-feedback">
                                                
                                                </div>
                                            </div>
                                            
                                            
                                        
                                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 ">
                                                <input type="submit" class="btn btn-primary" name="update"  value="Update User"><span style="flex:5;"><pre>                         </pre></span>
                                                 <input type="submit" class="btn btn-primary" name="delete"  value="Delete User">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- ============================================================== -->
                        <!-- end validation form -->
                        <!-- ============================================================== -->
                    </div>
                  
           
            </div>
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
           
            <!-- ============================================================== -->
            <!-- end footer -->
            <!-- ============================================================== -->
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- end main wrapper -->
    <!-- ============================================================== -->
    <!-- Optional JavaScript -->
    <script src="assets/vendor/jquery/jquery-3.3.1.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.js"></script>
    <script src="assets/vendor/slimscroll/jquery.slimscroll.js"></script>
    <script src="assets/vendor/parsley/parsley.js"></script>
    <script src="assets/libs/js/main-js.js"></script>
    <script>
    $('#form').parsley();
    </script>
    <script>
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.getElementsByClassName('needs-validation');
            // Loop over them and prevent submission
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
    </script>
</body>
 
</html>