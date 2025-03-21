<?php
// A direct test script to verify password hashing and verification

// The password we want to test
$password = "admin123";

// Create a hash
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: $password<br>";
echo "Generated hash: $hash<br>";
echo "Verification result: " . (password_verify($password, $hash) ? "PASS" : "FAIL") . "<br><br>";

// Test against our known hash
$known_hash = '$2y$10$6fLOMZ8FGFf4GK/Vdrf1de/ysAJNcg/ugMv.nZhx55b3t0xJ5ZRzm';
echo "Known hash: $known_hash<br>";
echo "Verification against known hash: " . (password_verify($password, $known_hash) ? "PASS" : "FAIL") . "<br>";
?>