<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Update with your actual username
define('DB_PASSWORD', ''); // Update with your actual password
define('DB_NAME', 'renew_notebooks');

// Global connection variable
global $conn;

// Create a persistent connection function
function getDbConnection() {
    global $conn;
    
    
    if ($conn instanceof mysqli && !$conn->connect_errno) {
        return $conn;
    }
    
    
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    
    register_shutdown_function(function() use ($conn) {
        // This prevents the connection from being closed automatically
        if ($conn instanceof mysqli) {
            
        }
    });
    
    return $conn;
}


$conn = getDbConnection();
?>