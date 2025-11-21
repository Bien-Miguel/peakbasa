<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>PeakBasa - Choose Role</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #e3f2fd; margin:0; }
        .container {
            width: 400px; margin: 100px auto; background: #fff;
            padding: 30px; border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            text-align: center;
        }
        h2 { color: #ec5757; margin-bottom: 25px; }
        .role-btn {
            display: block; width: 80%; margin: 10px auto; padding: 15px;
            background: #ec5757; color: white; border-radius: 10px;
            text-decoration: none; font-size: 1.1rem; font-weight: bold;
            transition: 0.3s;
        }
        .role-btn:hover { background: #c04161ff; }
        .back-link {
            margin-top: 20px;
            display: block;
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .back-link:hover { color: #ec5757; }
    </style>
</head>
<body class="centered">
    <div class="container">
        <h2>Choose Your Role</h2>
        <a href="Verification/register.php?role=student" class="role-btn">👩‍🎓 Register as Student</a>
        <a href="Verification/teacher_register.php" class="role-btn">👨‍🏫 Register as Teacher</a>
        
        <a href="Verification/login.php" class="back-link">Already have an account? Login here</a>
    </div>
</body>
</html>

