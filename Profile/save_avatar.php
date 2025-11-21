<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Assuming teacher_login_verify.php is inside a folder like 'teacher/' or 'Verification/'
require_once '../conn.php';
$username = $_SESSION['username'];

if (isset($_POST['avatar'])) {
    $avatar = $_POST['avatar'];
    $conn->query("UPDATE users SET photo='$avatar' WHERE username='$username'");
}

header("Location: profilemain.php");
exit;
?> 