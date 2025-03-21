<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header("location: ../auth/login.php");
    exit;
}

require_once "../config/db_config.php";

// Initialize variables
$response = null;
$method = 'GET';
$id = '';
$url = 'http://localhost/renew-notebooks/services/rest_service.php';
$payload = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $method = $_POST['method'];
    $id = $_POST['id'];
    $payload = $_POST['payload'];
    
    // Build URL
    $request_url = $url;
    if (!empty($id) && ($method == 'GET' || $method == 'PUT' || $method == 'DELETE')) {
        $request_url .= '?id=' . urlencode($id);
    }
    
    // Initialize cURL session
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $request_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    // Set request headers
    $headers = ['Content-Type: application/json'];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // Set request body for POST and PUT
    if ($method == 'POST' || $method == 'PUT') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    }
    
    // Execute cURL request
    $response = curl_exec($ch);
    
    // Get response info
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    
    // Close cURL session
    curl_close($ch);
    
    // Format response for display
    if (!empty($curl_error)) {
        $formatted_response = "cURL Error: " . $curl_error;
    } else {
        $formatted_response = json_decode($response, true);
    }
    
    $response = [
        'status' => $status_code,
        'body' => $formatted_response
    ];
}

// Get processors for dropdown
$processors_sql = "SELECT id, manufacturer, type FROM processor ORDER BY manufacturer, type";
$processors_result = $conn->query($processors_sql);

// Get operating systems for dropdown
$os_sql = "SELECT id, osname FROM opsystem ORDER BY osname";
$os_result = $conn->query($os_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REST Client - ReNew Notebooks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include "../includes/header.php"; ?>
    
    <div class="container py-5">
        <h1>REST API Client</h1>
        <p class="lead">Test the notebook RESTful API with this client.</p>
        
        <!-- API Request Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">API Request</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="rest_client.php">
                    <div class="mb-3">
                        <label for="method" class="form-label">HTTP Method</label>
                        <select class="form-select" id="method" name="method" required>
                            <option value="GET" <?php echo $method == 'GET' ? 'selected' : ''; ?>>GET</option>
                            <option value="POST" <?php echo $method == 'POST' ? 'selected' : ''; ?>>POST</option>
                            <option value="PUT" <?php echo $method == 'PUT' ? 'selected' : ''; ?>>PUT</option>
                            <option value="DELETE" <?php echo $method == 'DELETE' ? 'selected' : ''; ?>>DELETE</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="id-group">
                        <label for="id" class="form-label">Notebook ID (for GET single, PUT, DELETE)</label>
                        <input type="number" class="form-control" id="id" name="id" value="<?php echo $id; ?>">
                    </div>
                    
                    <div class="mb-3" id="payload-group">
                        <label for="payload" class="form-label">Request Body (JSON for POST, PUT)</label>
                        <textarea class="form-control" id="payload" name="payload" rows="10"><?php echo $payload; ?></textarea>
                    </div>
                    
                    <div class="mb-3" id="payload-template-group">
                        <label class="form-label">Payload Templates</label>
                        <div class="d-grid gap-2 d-md-block">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="create-template">Create Template</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="update-template">Update Template</button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
        </div>
        
        <!-- API Response -->
        <?php if($response): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">API Response</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Status Code: </strong>
                    <span class="badge <?php echo $response['status'] >= 200 && $response['status'] < 300 ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo $response['status']; ?>
                    </span>
                </div>
                <div class="mb-3">
                    <strong>Response Body:</strong>
                </div>
                <pre class="bg-light p-3"><?php echo json_encode($response['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include "../includes/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show/hide form fields based on method
        document.getElementById('method').addEventListener('change', function() {
            const method = this.value;
            const idGroup = document.getElementById('id-group');
            const payloadGroup = document.getElementById('payload-group');
            const payloadTemplateGroup = document.getElementById('payload-template-group');
            
            if (method === 'GET') {
                idGroup.style.display = 'block';
                payloadGroup.style.display = 'none';
                payloadTemplateGroup.style.display = 'none';
            } else if (method === 'DELETE') {
                idGroup.style.display = 'block';
                payloadGroup.style.display = 'none';
                payloadTemplateGroup.style.display = 'none';
            } else if (method === 'POST') {
                idGroup.style.display = 'none';
                payloadGroup.style.display = 'block';
                payloadTemplateGroup.style.display = 'block';
            } else if (method === 'PUT') {
                idGroup.style.display = 'block';
                payloadGroup.style.display = 'block';
                payloadTemplateGroup.style.display = 'block';
            }
        });
        
        // Create template buttons
        document.getElementById('create-template').addEventListener('click', function() {
            const template = {
                "manufacturer": "HP",
                "type": "New Model XYZ",
                "display": 15.6,
                "memory": 8192,
                "harddisk": 512,
                "videocontroller": "NVIDIA GeForce GTX 1650",
                "price": 799.99,
                "processorid": 1,
                "opsystemid": 9,
                "pieces": 10
            };
            
            document.getElementById('payload').value = JSON.stringify(template, null, 2);
        });
        
        document.getElementById('update-template').addEventListener('click', function() {
            const template = {
                "manufacturer": "HP",
                "type": "Updated Model XYZ",
                "price": 849.99,
                "pieces": 15
            };
            
            document.getElementById('payload').value = JSON.stringify(template, null, 2);
        });
        
        // Trigger change event on page load
        document.getElementById('method').dispatchEvent(new Event('change'));
    </script>
</body>
</html>