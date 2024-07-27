<?php
session_start();
session_unset();
// session_distroy();
header("location:home.php");
echo"hello";

?>