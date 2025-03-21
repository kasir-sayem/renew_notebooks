<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header("location: ../auth/login.php");
    exit;
}

require_once "../config/db_config.php";

// Initialize variables
$errors = [];
$success_message = '';

// Get processors for dropdown
$processors_sql = "SELECT id, manufacturer, type FROM processor ORDER BY manufacturer, type";
$processors_result = $conn->query($processors_sql);
$processors = [];
while ($row = $processors_result->fetch_assoc()) {
    $processors[$row['id']] = $row['manufacturer'] . ' ' . $row['type'];
}

// Get operating systems for dropdown
$os_sql = "SELECT id, osname FROM opsystem ORDER BY osname";
$os_result = $conn->query($os_sql);
$operating_systems = [];
while ($row = $os_result->fetch_assoc()) {
    $operating_systems[$row['id']] = $row['osname'];
}

// Process notebook form submission (create/update)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_notebook'])) {
    // Collect and validate form data
    $notebook_id = isset($_POST['notebook_id']) ? $_POST['notebook_id'] : null;
    $manufacturer = trim($_POST['manufacturer']);
    $type = trim($_POST['type']);
    $display = floatval($_POST['display']);
    $memory = intval($_POST['memory']);
    $harddisk = intval($_POST['harddisk']);
    $videocontroller = trim($_POST['videocontroller']);
    $price = floatval($_POST['price']) * 100; // Convert to cents
    $processorid = intval($_POST['processorid']);
    $opsystemid = intval($_POST['opsystemid']);
    $pieces = intval($_POST['pieces']);
    
    // Validate form data
    if (empty($manufacturer)) {
        $errors[] = "Manufacturer is required.";
    }
    
    if (empty($type)) {
        $errors[] = "Type is required.";
    }
    
    if ($display <= 0) {
        $errors[] = "Display size must be greater than 0.";
    }
    
    if ($memory <= 0) {
        $errors[] = "Memory must be greater than 0.";
    }
    
    if ($harddisk <= 0) {
        $errors[] = "Hard disk size must be greater than 0.";
    }
    
    if (empty($videocontroller)) {
        $errors[] = "Video controller is required.";
    }
    
    if ($price <= 0) {
        $errors[] = "Price must be greater than 0.";
    }
    
    if ($processorid <= 0 || !array_key_exists($processorid, $processors)) {
        $errors[] = "Please select a valid processor.";
    }
    
    if ($opsystemid <= 0 || !array_key_exists($opsystemid, $operating_systems)) {
        $errors[] = "Please select a valid operating system.";
    }
    
    if ($pieces < 0) {
        $errors[] = "Pieces cannot be negative.";
    }
    
    // If no errors, save notebook
    if (empty($errors)) {
        if ($notebook_id) {
            // Update existing notebook
            $sql = "UPDATE notebook SET 
                    manufacturer = ?,
                    type = ?,
                    display = ?,
                    memory = ?,
                    harddisk = ?,
                    videocontroller = ?,
                    price = ?,
                    processorid = ?,
                    opsystemid = ?,
                    pieces = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdiisiiiii", $manufacturer, $type, $display, $memory, $harddisk, $videocontroller, $price, $processorid, $opsystemid, $pieces, $notebook_id);
            
            if ($stmt->execute()) {
                $success_message = "Notebook updated successfully.";
            } else {
                $errors[] = "Error updating notebook: " . $stmt->error;
            }
        } else {
            // Create new notebook
            $sql = "INSERT INTO notebook (manufacturer, type, display, memory, harddisk, videocontroller, price, processorid, opsystemid, pieces) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdiisiiii", $manufacturer, $type, $display, $memory, $harddisk, $videocontroller, $price, $processorid, $opsystemid, $pieces);
            
            if ($stmt->execute()) {
                $success_message = "Notebook created successfully.";
                // Clear form data for new entry
                $notebook_id = $manufacturer = $type = $videocontroller = '';
                $display = $memory = $harddisk = $price = $processorid = $opsystemid = $pieces = 0;
            } else {
                $errors[] = "Error creating notebook: " . $stmt->error;
            }
        }
    }
}

// Process delete request
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    
    // Delete notebook
    $sql = "DELETE FROM notebook WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $success_message = "Notebook deleted successfully.";
    } else {
        $errors[] = "Error deleting notebook: " . $stmt->error;
    }
}

// Get notebook for editing
$notebook_for_edit = null;
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    
    $sql = "SELECT * FROM notebook WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $notebook_for_edit = $result->fetch_assoc();
    }
}

// Pagination variables
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Get total number of notebooks
$total_sql = "SELECT COUNT(*) as total FROM notebook";
$total_result = $conn->query($total_sql);
$total_records = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get notebooks for the current page
$notebooks_sql = "SELECT n.*, p.manufacturer AS processor_manufacturer, p.type AS processor_type, o.osname 
                  FROM notebook n 
                  JOIN processor p ON n.processorid = p.id 
                  JOIN opsystem o ON n.opsystemid = o.id 
                  ORDER BY n.manufacturer, n.type 
                  LIMIT $offset, $records_per_page";
$notebooks_result = $conn->query($notebooks_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notebooks - ReNew Notebooks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include "../includes/header.php"; ?>
    
    <div class="container py-5">
        <h1>Manage Notebooks</h1>
        
        <!-- Display error messages -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Display success message -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Notebook Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?php echo $notebook_for_edit ? 'Edit Notebook' : 'Add New Notebook'; ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="notebooks.php">
                    <?php if ($notebook_for_edit): ?>
                        <input type="hidden" name="notebook_id" value="<?php echo $notebook_for_edit['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="manufacturer" class="form-label">Manufacturer</label>
                            <input type="text" class="form-control" id="manufacturer" name="manufacturer" required 
                                   value="<?php echo isset($notebook_for_edit['manufacturer']) ? htmlspecialchars($notebook_for_edit['manufacturer']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="type" class="form-label">Type</label>
                            <input type="text" class="form-control" id="type" name="type" required 
                                   value="<?php echo isset($notebook_for_edit['type']) ? htmlspecialchars($notebook_for_edit['type']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="display" class="form-label">Display Size (inches)</label>
                            <input type="number" step="0.1" class="form-control" id="display" name="display" required 
                                   value="<?php echo isset($notebook_for_edit['display']) ? $notebook_for_edit['display'] : ''; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="memory" class="form-label">Memory (MB)</label>
                            <input type="number" class="form-control" id="memory" name="memory" required 
                                   value="<?php echo isset($notebook_for_edit['memory']) ? $notebook_for_edit['memory'] : ''; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="harddisk" class="form-label">Hard Disk (GB)</label>
                            <input type="number" class="form-control" id="harddisk" name="harddisk" required 
                                   value="<?php echo isset($notebook_for_edit['harddisk']) ? $notebook_for_edit['harddisk'] : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="videocontroller" class="form-label">Video Controller</label>
                            <input type="text" class="form-control" id="videocontroller" name="videocontroller" required 
                                   value="<?php echo isset($notebook_for_edit['videocontroller']) ? htmlspecialchars($notebook_for_edit['videocontroller']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="price" class="form-label">Price (£)</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required 
                                   value="<?php echo isset($notebook_for_edit['price']) ? $notebook_for_edit['price'] / 100 : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="processorid" class="form-label">Processor</label>
                            <select class="form-select" id="processorid" name="processorid" required>
                                <option value="">Select Processor</option>
                                <?php foreach ($processors as $id => $name): ?>
                                    <option value="<?php echo $id; ?>" <?php echo (isset($notebook_for_edit['processorid']) && $notebook_for_edit['processorid'] == $id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="opsystemid" class="form-label">Operating System</label>
                            <select class="form-select" id="opsystemid" name="opsystemid" required>
                                <option value="">Select Operating System</option>
                                <?php foreach ($operating_systems as $id => $name): ?>
                                    <option value="<?php echo $id; ?>" <?php echo (isset($notebook_for_edit['opsystemid']) && $notebook_for_edit['opsystemid'] == $id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="pieces" class="form-label">Pieces in Stock</label>
                            <input type="number" class="form-control" id="pieces" name="pieces" required 
                                   value="<?php echo isset($notebook_for_edit['pieces']) ? $notebook_for_edit['pieces'] : '0'; ?>">
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="submit" name="save_notebook" class="btn btn-primary">
                            <?php echo $notebook_for_edit ? 'Update Notebook' : 'Add Notebook'; ?>
                        </button>
                        <?php if ($notebook_for_edit): ?>
                            <a href="notebooks.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Notebooks Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Notebook List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Manufacturer</th>
                                <th>Type</th>
                                <th>Display</th>
                                <th>Memory</th>
                                <th>Hard Disk</th>
                                <th>Processor</th>
                                <th>OS</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($notebook = $notebooks_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $notebook['id']; ?></td>
                                    <td><?php echo htmlspecialchars($notebook['manufacturer']); ?></td>
                                    <td><?php echo htmlspecialchars($notebook['type']); ?></td>
                                    <td><?php echo $notebook['display']; ?>"</td>
                                    <td><?php echo $notebook['memory']; ?> MB</td>
                                    <td><?php echo $notebook['harddisk']; ?> GB</td>
                                    <td><?php echo htmlspecialchars($notebook['processor_manufacturer'] . ' ' . $notebook['processor_type']); ?></td>
                                    <td><?php echo htmlspecialchars($notebook['osname']); ?></td>
                                    <td>£<?php echo number_format($notebook['price'] / 100, 2); ?></td>
                                    <td><?php echo $notebook['pieces']; ?></td>
                                    <td>
                                        <a href="notebooks.php?edit=<?php echo $notebook['id']; ?>" class="btn btn-sm btn-primary mb-1">Edit</a>
                                        <a href="notebooks.php?delete=<?php echo $notebook['id']; ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Are you sure you want to delete this notebook?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mt-4">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include "../includes/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js> <script/>
    </body>
</html>