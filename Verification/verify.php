<?php
session_start();
// Assuming teacher_login_verify.php is inside a folder like 'teacher/' or 'Verification/'
require_once '../conn.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST['code']);

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE verification_code=? AND is_verified=0");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $update = $conn->prepare("UPDATE users SET is_verified=1, verification_code=NULL WHERE verification_code=?");
        $update->bind_param("s", $code);
        $update->execute();

        $message = "<p class='success'>✅ Email verified! You can now <a href='./login.php'>login</a>.</p>";
    } else {
        $message = "<p class='error'>❌ Invalid or expired verification code.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Email Verification | PeakBasa</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #74ABE2, #5563DE);
      height: 100vh;
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .container {
      background: white;
      border-radius: 20px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.2);
      width: 360px;
      padding: 30px;
      text-align: center;
    }

    h2 {
      color: #333;
      margin-bottom: 10px;
    }

    p {
      color: #666;
      font-size: 14px;
    }

    input[type="text"] {
      width: 80%;
      padding: 10px;
      margin-top: 15px;
      border: 1px solid #ccc;
      border-radius: 10px;
      font-size: 16px;
      outline: none;
    }

    button {
      background: #5563DE;
      color: white;
      border: none;
      border-radius: 10px;
      padding: 10px 25px;
      margin-top: 15px;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background: #3947c9;
    }

    .success {
      color: green;
      margin-top: 15px;
    }

    .error {
      color: red;
      margin-top: 15px;
    }

    a {
      color: #3947c9;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Verify Your Email</h2>
    <p>Enter the verification code sent to your email.</p>

    <form method="POST">
      <input type="text" name="code" placeholder="Enter code" required>
      <br>
      <button type="submit">Verify</button>
    </form>

    <?= $message ?>
  </div>
</body>
</html>
