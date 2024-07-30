<?php
session_start();
session_unset();
// session_distroy();
header("location:index.php");
echo"hello";

?>