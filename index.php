
<?php

include('rms.php');

$object = new rms();

if(!$object->Is_set_up_done())
{
    header("location:".$object->base_url."register.php");
}

if($object->is_login())
{
    header("location:".$object->base_url."dashboard.php");
}



?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Restaurant Management System using PHP - Login</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    
    <link rel="stylesheet" type="text/css" href="vendor/parsley/parsley.css"/>

</head>

<body class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-5 col-lg-5 col-md-6">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="p-5">
                                    <div class="text-center">
                                        <?php
                                        if(isset($_SESSION['success']))
                                        {
                                            echo $_SESSION['success'];
                                            unset($_SESSION['success']);
                                        }
                                        ?>
                                        <span id="error"></span>
                                        <h1 class="h4 text-gray-900 mb-4">Restaurant Management System</h1>
                                    </div>
                                    <form method="post" id="login_form">
                                        <div class="form-group">
                                            <input type="text" name="user_email" id="user_email" class="form-control" required data-parsley-type="email" data-parsley-trigger="keyup" placeholder="Enter Email Address..." />
                                        </div>
                                        <div class="form-group">
                                            <input type="password" name="user_password" id="user_password" class="form-control" required  data-parsley-trigger="keyup" placeholder="Password" />
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" name="login_button" id="login_button" class="btn btn-primary btn-user btn-block">Login</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <script type="text/javascript" src="vendor/parsley/dist/parsley.min.js"></script>

</body>

</html>

<script>

$(document).ready(function(){

    $('#login_form').parsley();

    $('#login_form').on('submit', function(event){
        event.preventDefault();
        if($('#login_form').parsley().isValid())
        {       
            $.ajax({
                url:"login_action.php",
                method:"POST",
                data:$(this).serialize(),
                dataType:'json',
                beforeSend:function()
                {
                    $('#login_button').attr('disabled', 'disabled');
                    $('#login_button').val('wait...');
                },
                success:function(data)
                {
                    $('#login_button').attr('disabled', false);
                    if(data.error != '')
                    {
                        $('#error').html(data.error);
                        $('#login_button').val('Login');
                    }
                    else
                    {
                        window.location.href = "<?php echo $object->base_url; ?>dashboard.php";
                    }
                }
            })
        }
    });

});

</script>