<?php
$host = 'localhost';
$dbname = 'dbginned3rbaqb';
$username = 'uac1gp3zeje8t';
$password = 'hk8ilpc7us2e';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error, 0);
        die("Database connection failed. Please contact the administrator.");
    }
    $conn->set_charset("utf8mb4");

    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows == 0) {
        error_log("Users table does not exist.", 0);
        die("Database error: Users table not found. Please run schema.sql to create tables.");
    }
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage(), 0);
    die("Database connection error. Please contact the administrator.");
}
?>
