<?php
// Database connection
require_once "config/db_config.php";

// CSV file path - update this to your actual file path
$csvFile = 'data/notebook.csv';

// Function to normalize data from CSV
function normalizeValue($value) {
    // Replace comma with dot in decimal values
    $value = str_replace(',', '.', $value);
    // Trim whitespace
    $value = trim($value);
    return $value;
}

// Check if file exists
if (!file_exists($csvFile)) {
    die("Error: CSV file not found at $csvFile");
}

// Read CSV data
$csv = file_get_contents($csvFile);
$lines = explode("\n", $csv);
$headers = explode("\t", $lines[0]);

// Prepare SQL statement
$sql = "INSERT INTO notebook (manufacturer, type, display, memory, harddisk, videocontroller, price, processorid, opsystemid, pieces) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

// Bind parameters
$stmt->bind_param("ssdiisiiii", $manufacturer, $type, $display, $memory, $harddisk, $videocontroller, $price, $processorid, $opsystemid, $pieces);

// Skip header row and process data
$rowCount = 0;
$successCount = 0;
$errorCount = 0;

// Optional: empty the table first
// $conn->query("TRUNCATE TABLE notebook");
// $conn->query("ALTER TABLE notebook AUTO_INCREMENT = 1");

for ($i = 1; $i < count($lines); $i++) {
    if (empty(trim($lines[$i]))) {
        continue; // Skip empty lines
    }
    
    $rowCount++;
    $data = explode("\t", $lines[$i]);
    
    // Ensure we have all columns
    if (count($data) < 10) {
        echo "Error: Line $i has incomplete data<br>";
        $errorCount++;
        continue;
    }
    
    // Assign values to parameters
    $manufacturer = trim($data[0]);
    $type = trim($data[1]);
    $display = normalizeValue($data[2]);
    $memory = (int)$data[3];
    $harddisk = (int)$data[4];
    $videocontroller = trim($data[5]);
    $price = (int)$data[6];
    $processorid = (int)$data[7];
    $opsystemid = (int)$data[8];
    $pieces = (int)$data[9];
    
    // Execute statement
    if ($stmt->execute()) {
        $successCount++;
    } else {
        echo "Error inserting row $i: " . $stmt->error . "<br>";
        $errorCount++;
    }
}

// Close statement and connection
$stmt->close();
$conn->close();

// Output results
echo "<h3>Import Complete</h3>";
echo "Total rows processed: $rowCount<br>";
echo "Successfully imported: $successCount<br>";
echo "Errors: $errorCount<br>";
?>