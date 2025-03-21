<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once "config/db_config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReNew Notebooks - Refurbished Laptops</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include "includes/header.php"; ?>
    
    <!-- Hero Section -->
    <div class="bg-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h1>Welcome to ReNew Notebooks</h1>
                    <p class="lead">We sell refurbished laptops at favorable prices in Nowhereland's capital.</p>
                    <a href="notebooks.php" class="btn btn-light mt-3">Browse Our Collection</a>
                </div>
                <div class="col-md-5">
                    <img src="assets/images/hero-laptop.png" alt="Laptop" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Featured Products -->
    <div class="container py-5">
        <h2 class="text-center mb-4">Featured Notebooks</h2>
        <div class="row">
            <?php
            // Get featured notebooks (those with stock available)
            $sql = "SELECT n.*, p.manufacturer AS processor_manufacturer, p.type AS processor_type, o.osname 
                    FROM notebook n 
                    JOIN processor p ON n.processorid = p.id 
                    JOIN opsystem o ON n.opsystemid = o.id 
                    WHERE n.pieces > 0 
                    LIMIT 3";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="assets/images/notebook-<?php echo $row['id']; ?>.jpg" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($row['manufacturer'] . ' ' . $row['type']); ?>"
                                 onerror="this.src='assets/images/default_notebook.jpg'">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['manufacturer'] . ' ' . $row['type']); ?></h5>
                                <p class="card-text">
                                    <strong>Processor:</strong> <?php echo htmlspecialchars($row['processor_manufacturer'] . ' ' . $row['processor_type']); ?><br>
                                    <strong>Memory:</strong> <?php echo $row['memory']; ?> MB<br>
                                    <strong>Hard Disk:</strong> <?php echo $row['harddisk']; ?> GB<br>
                                    <strong>Operating System:</strong> <?php echo htmlspecialchars($row['osname']); ?>
                                </p>
                                <h5 class="mt-2">£<?php echo number_format($row['price'] / 100, 2); ?></h5>
                            </div>
                            <div class="card-footer">
                                <a href="notebook_details.php?id=<?php echo $row['id']; ?>" class="btn btn-primary w-100">View Details</a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='col-12'><p class='text-center'>No notebooks in stock.</p></div>";
            }
            ?>
        </div>
    </div>
    
    <!-- About Section -->
    <div class="bg-light py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-8 mx-auto text-center">
                    <h2>About ReNew Ltd.</h2>
                    <p class="lead">
                        ReNew Ltd. specializes in selling refurbished notebooks at favorable prices in the capital of Nowhereland. 
                        Our mission is to extend the life cycle of electronic devices and make quality technology accessible to everyone.
                    </p>
                    <p>
                        All our notebooks undergo a thorough inspection and refurbishment process to ensure they meet our high-quality standards. 
                        We provide a wide range of options from various manufacturers with different specifications to meet diverse customer needs.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <?php include "includes/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>