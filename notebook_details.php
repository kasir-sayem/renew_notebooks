<?php
session_start();
require_once "config/db_config.php";

// Get notebook ID from URL parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Redirect if no valid ID
if (!$id) {
    header("Location: notebooks.php");
    exit;
}

// Get notebook details
$sql = "SELECT n.*, p.manufacturer AS processor_manufacturer, p.type AS processor_type, o.osname 
        FROM notebook n 
        JOIN processor p ON n.processorid = p.id 
        JOIN opsystem o ON n.opsystemid = o.id 
        WHERE n.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Check if notebook exists
if ($result->num_rows === 0) {
    header("Location: notebooks.php");
    exit;
}

$notebook = $result->fetch_assoc();

// Get related notebooks (same manufacturer)
$related_sql = "SELECT n.id, n.manufacturer, n.type, n.price, n.pieces, n.display 
                FROM notebook n 
                WHERE n.manufacturer = ? AND n.id != ? 
                LIMIT 3";
$related_stmt = $conn->prepare($related_sql);
$related_stmt->bind_param("si", $notebook['manufacturer'], $id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($notebook['manufacturer'] . ' ' . $notebook['type']); ?> - ReNew Notebooks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include "includes/header.php"; ?>
    
    <div class="container py-5">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="notebooks.php">Notebooks</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($notebook['manufacturer'] . ' ' . $notebook['type']); ?></li>
            </ol>
        </nav>
        
        <div class="row">
            <div class="col-md-5 mb-4">
                <img src="assets/images/notebook-<?php echo $notebook['id']; ?>.jpg" 
                     class="img-fluid rounded" 
                     alt="<?php echo htmlspecialchars($notebook['manufacturer'] . ' ' . $notebook['type']); ?>"
                     onerror="this.src='assets/images/default_notebook.jpg'">
            </div>
            <div class="col-md-7">
                <h1 class="mb-3"><?php echo htmlspecialchars($notebook['manufacturer'] . ' ' . $notebook['type']); ?></h1>
                
                <div class="mb-3">
                    <span class="badge bg-<?php echo $notebook['pieces'] > 0 ? 'success' : 'danger'; ?> mb-2">
                        <?php echo $notebook['pieces'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                    </span>
                    <h3 class="text-primary">£<?php echo number_format($notebook['price'] / 100, 2); ?></h3>
                </div>
                
                <div class="mb-4">
                    <h5>Key Specifications:</h5>
                    <ul class="list-unstyled">
                        <li><strong>Processor:</strong> <?php echo htmlspecialchars($notebook['processor_manufacturer'] . ' ' . $notebook['processor_type']); ?></li>
                        <li><strong>Memory:</strong> <?php echo $notebook['memory']; ?> MB</li>
                        <li><strong>Hard Disk:</strong> <?php echo $notebook['harddisk']; ?> GB</li>
                        <li><strong>Display:</strong> <?php echo $notebook['display']; ?>"</li>
                        <li><strong>Graphics:</strong> <?php echo htmlspecialchars($notebook['videocontroller']); ?></li>
                        <li><strong>Operating System:</strong> <?php echo htmlspecialchars($notebook['osname']); ?></li>
                    </ul>
                </div>
                
                <div class="d-grid gap-2 col-6 mb-4">
                    <?php if($notebook['pieces'] > 0): ?>
                        <button type="button" class="btn btn-primary btn-lg">Add to Cart</button>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary btn-lg" disabled>Out of Stock</button>
                    <?php endif; ?>
                </div>
                
                <div class="alert alert-info">
                    <h5>ReNew Refurbishment Quality</h5>
                    <p>All our notebooks undergo a thorough inspection and refurbishment process to ensure they meet our high-quality standards. Each notebook is cleaned, tested, and restored to excellent working condition.</p>
                </div>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-12">
                <h3>Detailed Specifications</h3>
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th scope="row">Manufacturer</th>
                                    <td><?php echo htmlspecialchars($notebook['manufacturer']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Model</th>
                                    <td><?php echo htmlspecialchars($notebook['type']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Processor</th>
                                    <td><?php echo htmlspecialchars($notebook['processor_manufacturer'] . ' ' . $notebook['processor_type']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Memory</th>
                                    <td><?php echo $notebook['memory']; ?> MB</td>
                                </tr>
                                <tr>
                                    <th scope="row">Hard Disk</th>
                                    <td><?php echo $notebook['harddisk']; ?> GB</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th scope="row">Display</th>
                                    <td><?php echo $notebook['display']; ?>"</td>
                                </tr>
                                <tr>
                                    <th scope="row">Video Controller</th>
                                    <td><?php echo htmlspecialchars($notebook['videocontroller']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Operating System</th>
                                    <td><?php echo htmlspecialchars($notebook['osname']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">In Stock</th>
                                    <td><?php echo $notebook['pieces']; ?> units</td>
                                </tr>
                                <tr>
                                    <th scope="row">Price</th>
                                    <td>£<?php echo number_format($notebook['price'] / 100, 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Notebooks -->
        <?php if($related_result->num_rows > 0): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3>More from <?php echo htmlspecialchars($notebook['manufacturer']); ?></h3>
                <hr>
            </div>
            
            <?php while($related = $related_result->fetch_assoc()): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <img src="assets/images/notebook-<?php echo $related['id']; ?>.jpg" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($related['manufacturer'] . ' ' . $related['type']); ?>"
                         onerror="this.src='assets/images/default_notebook.jpg'">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($related['manufacturer'] . ' ' . $related['type']); ?></h5>
                        <p class="card-text">
                            <strong>Display:</strong> <?php echo $related['display']; ?>"<br>
                            <strong>Price:</strong> £<?php echo number_format($related['price'] / 100, 2); ?>
                        </p>
                    </div>
                    <div class="card-footer">
                        <a href="notebook_details.php?id=<?php echo $related['id']; ?>" class="btn btn-primary w-100">View Details</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include "includes/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>