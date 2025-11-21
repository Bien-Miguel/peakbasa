<?php
session_start();
session_destroy();
// Redirects UP one level from Verification, then INTO Main, to welcome.php
header("Location: ../Main/welcome.php"); 
exit;
?>