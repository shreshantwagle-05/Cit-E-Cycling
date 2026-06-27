<?php
// Start session for authentication state
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection variables
$servername = "localhost";
$username = "root";
$password = "";
$database = "cycling";
$port = "3306";

try {
    // Create PDO connection
    $conn = new PDO(
        "mysql:host=$servername;port=$port;dbname=$database",
        $username,
        $password
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
}

// Authentication function
function db_authenticate($conn, $username, $password) {
    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->execute([$username]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['password'] == $password) {
        return true;
    }

    return false;
}

// Return participants list for admin dashboard (minimal, safe implementation)
function db_get_participants() {
    global $conn;

    if (!isset($conn)) {
        return [];
    }

    try {
        $stmt = $conn->prepare("SELECT * FROM participant");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// Indicate whether the app is using a mock/session DB (always false when connected)
function db_is_mock() {
    return false;
}
?>