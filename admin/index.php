<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header("location: ../auth/login.php");
    exit;
}

require_once "../config/db_config.php";

// Get counts for dashboard
$notebook_count_sql = "SELECT COUNT(*) as count FROM notebook";
$notebook_count_result = $conn->query($notebook_count_sql);
$notebook_count = $notebook_count_result->fetch_assoc()['count'];

$stock_count_sql = "SELECT SUM(pieces) as count FROM notebook";
$stock_count_result = $conn->query($stock_count_sql);
$stock_count = $stock_count_result->fetch_assoc()['count'];

$user_count_sql = "SELECT COUNT(*) as count FROM users";
$user_count_result = $conn->query($user_count_sql);
$user_count = $user_count_result->fetch_assoc()['count'];

$os_count_sql = "SELECT COUNT(*) as count FROM opsystem";
$os_count_result = $conn->query($os_count_sql);
$os_count = $os_count_result->fetch_assoc()['count'];

// Recent notebooks
$recent_notebooks_sql = "SELECT n.*, p.manufacturer AS processor_manufacturer, p.type AS processor_type, o.osname 
                         FROM notebook n 
                         JOIN processor p ON n.processorid = p.id 
                         JOIN opsystem o ON n.opsystemid = o.id 
                         ORDER BY n.id DESC LIMIT 5";
$recent_notebooks_result = $conn->query($recent_notebooks_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ReNew Notebooks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include "../includes/header.php"; ?>
    
    <div class="container py-5">
        <h1>Admin Dashboard</h1>
        <p class="lead">Welcome to the ReNew Notebooks Admin Panel</p>
        
        <!-- Dashboard Stats -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">Total Notebooks</h6>
                                <h3 class="mb-0"><?php echo $notebook_count; ?></h3>
                            </div>
                            <i class="bi bi-laptop fs-1"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="notebooks.php" class="text-white">View Details</a>
                        <i class="bi bi-arrow-right text-white"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">Total Stock</h6>
                                <h3 class="mb-0"><?php echo $stock_count; ?></h3>
                            </div>
                            <i class="bi bi-box-seam fs-1"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="notebooks.php" class="text-white">View Details</a>
                        <i class="bi bi-arrow-right text-white"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">Total Users</h6>
                                <h3 class="mb-0"><?php echo $user_count; ?></h3>
                            </div>
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="#" class="text-white">View Details</a>
                        <i class="bi bi-arrow-right text-white"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-danger text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">Operating Systems</h6>
                                <h3 class="mb-0"><?php echo $os_count; ?></h3>
                            </div>
                            <i class="bi bi-windows fs-1"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="#" class="text-white">View Details</a>
                        <i class="bi bi-arrow-right text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Notebooks -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Recent Notebooks</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Manufacturer</th>
                                <th>Type</th>
                                <th>Processor</th>
                                <th>OS</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($notebook = $recent_notebooks_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $notebook['id']; ?></td>
                                    <td><?php echo htmlspecialchars($notebook['manufacturer']); ?></td>
                                    <td><?php echo htmlspecialchars($notebook['type']); ?></td>
                                    <td><?php echo htmlspecialchars($notebook['processor_manufacturer'] . ' ' . $notebook['processor_type']); ?></td>
                                    <td><?php echo htmlspecialchars($notebook['osname']); ?></td>
                                    <td>£<?php echo number_format($notebook['price'] / 100, 2); ?></td>
                                    <td><?php echo $notebook['pieces']; ?></td>
                                    <td>
                                        <a href="notebooks.php?edit=<?php echo $notebook['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-end">
                <a href="notebooks.php" class="btn btn-primary">View All Notebooks</a>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Web Services</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="soap_client.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                SOAP Client
                                <i class="bi bi-arrow-right"></i>
                            </a>
                            <a href="rest_client.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                REST Client
                                <i class="bi bi-arrow-right"></i>
                            </a>
                            <a href="../mnb_service.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                MNB Exchange Rates
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Utilities</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="../pdf_generator.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                PDF Generator
                                <i class="bi bi-arrow-right"></i>
                            </a>
                            <a href="../notebooks.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                View Public Site
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include "../includes/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>