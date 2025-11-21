<?php
/**
 * Database Connection Configuration (conn.php)
 * * Purpose: Centralizes database connection details for easy maintenance
 * and quick deployment changes (Localhost -> Live Host).
 *
 * NOTE: When deploying, you ONLY need to change the values below.
 */

// ===========================================
// --- 1. CONFIGURE DATABASE CREDENTIALS ---
// ===========================================

// Define the database constants
define('DB_HOST', 'localhost'); // Usually 'localhost' locally and on the live host
define('DB_USER', 'u967494580_basa');      // Local: 'root' | Live Host: Your assigned DB username
define('DB_PASS', 'Hannipham13!');          // Local: Often empty or 'root' | Live Host: Your assigned DB password
define('DB_NAME', 'u967494580_peak');  // The name of your database

// ===========================================
// --- 2. ESTABLISH CONNECTION ---
// ===========================================

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // Stop script execution and display a user-friendly error
    // In a production environment, you would log this error instead of displaying $conn->connect_error
    die("Database Connection Failed: " . $conn->connect_error);
}

// Optional: Set character set to UTF-8 for proper handling of all characters
$conn->set_charset("utf8");

// The global $conn object is now available to any file that requires this file.
// Example usage in other files: require_once '../conn.php';
?>
