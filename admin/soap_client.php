<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header("location: ../auth/login.php");
    exit;
}

require_once "../config/db_config.php";

$result = null;
$error = null;

// Process SOAP request
if($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Create SOAP client
        $client = new SoapClient(null, array(
            'location' => 'http://localhost/renew-notebooks/services/soap_service.php',
            'uri'      => 'http://localhost/renew-notebooks/services/soap_service.php',
            'trace'    => 1
        ));
        
        // Call the requested method
        $method = $_POST['method'];
        $params = isset($_POST['params']) ? $_POST['params'] : array();
        
        switch($method) {
            case 'getAllNotebooks':
                $result = $client->getAllNotebooks();
                break;
            case 'getNotebookById':
                $result = $client->getNotebookById($params['id']);
                break;
            case 'getNotebooksByManufacturer':
                $result = $client->getNotebooksByManufacturer($params['manufacturer']);
                break;
            case 'getAllProcessors':
                $result = $client->getAllProcessors();
                break;
            case 'getAllOperatingSystems':
                $result = $client->getAllOperatingSystems();
                break;
            default:
                $error = "Unknown method: $method";
        }
    } catch(SoapFault $e) {
        $error = "SOAP Fault: " . $e->getMessage();
    } catch(Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOAP Client - ReNew Notebooks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include "../includes/header.php"; ?>
    
    <div class="container py-5">
        <h1>SOAP Client</h1>
        <p class="lead">Test the notebook SOAP service with this client.</p>
        
        <!-- SOAP Request Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">SOAP Request</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="soap_client.php">
                    <div class="mb-3">
                        <label for="method" class="form-label">Method</label>
                        <select class="form-select" id="method" name="method" required>
                            <option value="getAllNotebooks">getAllNotebooks()</option>
                            <option value="getNotebookById">getNotebookById($id)</option>
                            <option value="getNotebooksByManufacturer">getNotebooksByManufacturer($manufacturer)</option>
                            <option value="getAllProcessors">getAllProcessors()</option>
                            <option value="getAllOperatingSystems">getAllOperatingSystems()</option>
                        </select>
                    </div>
                    
                    <!-- Parameters for getNotebookById -->
                    <div class="mb-3 param-group" id="params-getNotebookById" style="display: none;">
                        <label for="param-id" class="form-label">ID</label>
                        <input type="number" class="form-control" id="param-id" name="params[id]">
                    </div>
                    
                    <!-- Parameters for getNotebooksByManufacturer -->
                    <div class="mb-3 param-group" id="params-getNotebooksByManufacturer" style="display: none;">
                        <label for="param-manufacturer" class="form-label">Manufacturer</label>
                        <input type="text" class="form-control" id="param-manufacturer" name="params[manufacturer]">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
        </div>
        
        <!-- SOAP Response -->
        <?php if($result !== null || $error !== null): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">SOAP Response</h5>
            </div>
            <div class="card-body">
                <?php if($error !== null): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php else: ?>
                    <pre class="bg-light p-3"><?php print_r($result); ?></pre>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include "../includes/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show/hide parameter inputs based on selected method
        document.getElementById('method').addEventListener('change', function() {
            // Hide all parameter groups
            document.querySelectorAll('.param-group').forEach(function(el) {
                el.style.display = 'none';
            });
            
            // Show parameter group for selected method
            const selectedMethod = this.value;
            const paramGroup = document.getElementById('params-' + selectedMethod);
            if (paramGroup) {
                paramGroup.style.display = 'block';
            }
        });
        
        // Trigger change event on page load
        document.getElementById('method').dispatchEvent(new Event('change'));
    </script>
</body>
</html>