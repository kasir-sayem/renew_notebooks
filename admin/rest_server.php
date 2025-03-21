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
    <title>REST Server - ReNew Notebooks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include "../includes/header.php"; ?>
    
    <div class="container py-5">
        <h1>RESTful API Server</h1>
        <p class="lead">This page provides information about the RESTful API available for notebook data.</p>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">API Endpoint</h5>
            </div>
            <div class="card-body">
                <p>The RESTful API is available at:</p>
                <div class="bg-light p-3 mb-3">
                    <code>http://localhost/renew-notebooks/services/rest_service.php</code>
                </div>
                
                <p>You can test this API using the <a href="rest_client.php">REST Client</a> provided in the admin area, or with tools like cURL and Postman.</p>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Available Endpoints</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>HTTP Method</th>
                                <th>Endpoint</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-success">GET</span></td>
                                <td><code>/rest_service.php</code></td>
                                <td>Returns all notebooks.</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">GET</span></td>
                                <td><code>/rest_service.php?id={id}</code></td>
                                <td>Returns a specific notebook by ID.</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">POST</span></td>
                                <td><code>/rest_service.php</code></td>
                                <td>Creates a new notebook.</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning text-dark">PUT</span></td>
                                <td><code>/rest_service.php?id={id}</code></td>
                                <td>Updates an existing notebook.</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-danger">DELETE</span></td>
                                <td><code>/rest_service.php?id={id}</code></td>
                                <td>Deletes a notebook.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Request & Response Examples</h5>
            </div>
            <div class="card-body">
                <h6>GET All Notebooks</h6>
                <pre class="bg-light p-3 mb-4">
GET /renew-notebooks/services/rest_service.php HTTP/1.1
Host: localhost
Accept: application/json
                </pre>
                
                <h6>Response</h6>
                <pre class="bg-light p-3 mb-4">
{
    "data": [
        {
            "id": "1",
            "manufacturer": "HP",
            "type": "COMPAQ 615 NX556EA",
            "display": "15.6",
            "memory": "1024",
            "harddisk": "160",
            "videocontroller": "ATi Mobility Radeon HD3200 256MB",
            "price": "951.20",
            "processorid": "1",
            "opsystemid": "1",
            "pieces": "0",
            "processor_manufacturer": "AMD",
            "processor_type": "Athlon 64 X2 QL64",
            "osname": "FreeDOS"
        },
        // ... more notebooks
    ]
}
                </pre>
                
                <h6>Create a New Notebook (POST)</h6>
                <pre class="bg-light p-3 mb-4">
POST /renew-notebooks/services/rest_service.php HTTP/1.1
Host: localhost
Content-Type: application/json

{
    "manufacturer": "Dell",
    "type": "Inspiron 15",
    "display": 15.6,
    "memory": 8192,
    "harddisk": 512,
    "videocontroller": "Intel HD Graphics",
    "price": 599.99,
    "processorid": 1,
    "opsystemid": 9,
    "pieces": 5
}
                </pre>
                
                <h6>Update a Notebook (PUT)</h6>
                <pre class="bg-light p-3 mb-4">
PUT /renew-notebooks/services/rest_service.php?id=1 HTTP/1.1
Host: localhost
Content-Type: application/json

{
    "price": 499.99,
    "pieces": 10
}
                </pre>
                
                <h6>Delete a Notebook (DELETE)</h6>
                <pre class="bg-light p-3 mb-4">
DELETE /renew-notebooks/services/rest_service.php?id=5 HTTP/1.1
Host: localhost
                </pre>
            </div>
        </div>
    </div>
    
    <?php include "../includes/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>