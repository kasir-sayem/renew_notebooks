<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header("location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOAP Server - ReNew Notebooks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include "../includes/header.php"; ?>
    
    <div class="container py-5">
        <h1>SOAP Server</h1>
        <p class="lead">This page provides information about the SOAP service available for notebook data.</p>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">SOAP Service Endpoint</h5>
            </div>
            <div class="card-body">
                <p>The SOAP service is available at:</p>
                

                <div class="bg-light p-3 mb-3">
                    <code>http://localhost/renew-notebooks/services/soap_service.php</code>
                </div>
                
                <p>You can test this service using the <a href="soap_client.php">SOAP Client</a> provided in the admin area.</p>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Available Methods</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Method</th>
                                <th>Parameters</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>getAllNotebooks()</code></td>
                                <td>None</td>
                                <td>Returns all notebooks in the database.</td>
                            </tr>
                            <tr>
                                <td><code>getNotebookById($id)</code></td>
                                <td><code>id</code> (integer)</td>
                                <td>Returns a specific notebook by its ID.</td>
                            </tr>
                            <tr>
                                <td><code>getNotebooksByManufacturer($manufacturer)</code></td>
                                <td><code>manufacturer</code> (string)</td>
                                <td>Returns all notebooks from a specific manufacturer.</td>
                            </tr>
                            <tr>
                                <td><code>getAllProcessors()</code></td>
                                <td>None</td>
                                <td>Returns all processors in the database.</td>
                            </tr>
                            <tr>
                                <td><code>getAllOperatingSystems()</code></td>
                                <td>None</td>
                                <td>Returns all operating systems in the database.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Implementation Example</h5>
            </div>
            <div class="card-body">
                <p>Here's a PHP example of how to connect to this SOAP service:</p>
                <pre class="bg-light p-3">
&lt;?php
try {
    // Create SOAP client
    $client = new SoapClient(null, array(
        'location' => 'http://localhost/renew-notebooks/services/soap_service.php',
        'uri'      => 'http://localhost/renew-notebooks/services/soap_service.php',
        'trace'    => 1
    ));
    
    // Call a method
    $notebooks = $client->getAllNotebooks();
    
    // Process the results
    print_r($notebooks);
    
} catch (SoapFault $e) {
    echo "Error: " . $e->getMessage();
}
?&gt;
                </pre>
            </div>
        </div>
    </div>
    
    <?php include "../includes/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>