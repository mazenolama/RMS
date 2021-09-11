<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');
 header('Content-Type: application/json');
//logout.php

session_start();

session_destroy();

header("location:index.php");

?>