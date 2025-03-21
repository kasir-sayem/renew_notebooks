<?php
// Database connection
$db_server = 'localhost';
$db_username = 'root'; // Update if needed
$db_password = ''; // Update if needed
$db_name = 'renew_notebooks';

// Create connection
$conn = new mysqli($db_server, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to database successfully<br>";

// Retrieve all users
$result = $conn->query("SELECT id, username, password, role FROM users");

echo "User table contains " . $result->num_rows . " rows:<br><br>";

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"] . "<br>";
        echo "Username: " . $row["username"] . "<br>";
        echo "Role: " . $row["role"] . "<br>";
        echo "Password hash: " . $row["password"] . "<br>";
        echo "Test with 'admin123': " . (password_verify('admin123', $row["password"]) ? "PASS" : "FAIL") . "<br><br>";
    }
} else {
    echo "0 results";
}

// Try creating a fresh admin user directly
$username = "directadmin";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$email = "directadmin@example.com";
$role = "admin";

$sql = "INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $username, $password, $email, $role);

if ($stmt->execute()) {
    echo "New admin user created successfully<br>";
    echo "Username: directadmin<br>";
    echo "Password: admin123<br>";
} else {
    echo "Error creating user: " . $conn->error;
}

$conn->close();
?>