<?php
session_start();
require_once "config/db_config.php";

// Pagination variables
$records_per_page = 12;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Filter variables
$manufacturer_filter = isset($_GET['manufacturer']) ? $_GET['manufacturer'] : '';
$os_filter = isset($_GET['os']) ? $_GET['os'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';

// Build query
$where_clauses = [];
$params = [];
$types = '';

if (!empty($manufacturer_filter)) {
    $where_clauses[] = "n.manufacturer = ?";
    $params[] = $manufacturer_filter;
    $types .= 's';
}

if (!empty($os_filter)) {
    $where_clauses[] = "n.opsystemid = ?";
    $params[] = $os_filter;
    $types .= 'i';
}

if (!empty($min_price)) {
    $where_clauses[] = "n.price >= ?";
    $params[] = $min_price * 100; // Convert to cents
    $types .= 'i';
}

// if (!empty($max_price)) {
//     $where_clauses[] = "n.price <= ?";
//     $params[]

    if (!empty($max_price)){
        $where_clauses[] = "n.price <= ?";
        $params[] = $max_price * 100; // Convert to cents
        $types .= 'i';
    }
    
    // Build the final where clause
    $where_clause = '';
    if (!empty($where_clauses)) {
        $where_clause = "WHERE " . implode(" AND ", $where_clauses);
    }
    
    // Get total records for pagination
    $count_sql = "SELECT COUNT(*) as total FROM notebook n $where_clause";
    if (!empty($params)) {
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param($types, ...$params);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $total_records = $count_result->fetch_assoc()['total'];
    } else {
        $count_result = $conn->query($count_sql);
        $total_records = $count_result->fetch_assoc()['total'];
    }
    
    $total_pages = ceil($total_records / $records_per_page);
    
    // Get notebook data
    $sql = "SELECT n.*, p.manufacturer AS processor_manufacturer, p.type AS processor_type, o.osname 
            FROM notebook n 
            JOIN processor p ON n.processorid = p.id 
            JOIN opsystem o ON n.opsystemid = o.id 
            $where_clause
            ORDER BY n.manufacturer, n.type 
            LIMIT $offset, $records_per_page";
    
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }
    
    // Get manufacturers for filter
    $manufacturers_sql = "SELECT DISTINCT manufacturer FROM notebook ORDER BY manufacturer";
    $manufacturers_result = $conn->query($manufacturers_sql);
    
    // Get operating systems for filter
    $os_sql = "SELECT id, osname FROM opsystem ORDER BY osname";
    $os_result = $conn->query($os_sql);
    ?>
    
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Notebooks - ReNew Notebooks</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body>
        <?php include "includes/header.php"; ?>
        
        <div class="container py-5">
            <h1 class="mb-4">Notebooks</h1>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="notebooks.php" class="row g-3">
                        <div class="col-md-3">
                            <label for="manufacturer" class="form-label">Manufacturer</label>
                            <select class="form-select" name="manufacturer" id="manufacturer">
                                <option value="">All Manufacturers</option>
                                <?php while($manufacturer = $manufacturers_result->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($manufacturer['manufacturer']); ?>" 
                                            <?php echo $manufacturer_filter == $manufacturer['manufacturer'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($manufacturer['manufacturer']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="os" class="form-label">Operating System</label>
                            <select class="form-select" name="os" id="os">
                                <option value="">All Operating Systems</option>
                                <?php while($os = $os_result->fetch_assoc()): ?>
                                    <option value="<?php echo $os['id']; ?>" 
                                            <?php echo $os_filter == $os['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($os['osname']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="min_price" class="form-label">Min Price (£)</label>
                            <input type="number" class="form-control" name="min_price" id="min_price" value="<?php echo $min_price; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="max_price" class="form-label">Max Price (£)</label>
                            <input type="number" class="form-control" name="max_price" id="max_price" value="<?php echo $max_price; ?>">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="notebooks.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Notebooks Grid -->
            <div class="row">
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        ?>
                        <div class="col-md-4 col-lg-3 mb-4">
                            <div class="card h-100">
                                <img src="assets/images/notebook-<?php echo $row['id']; ?>.jpg" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($row['manufacturer'] . ' ' . $row['type']); ?>"
                                     onerror="this.src='assets/images/default_notebook.jpg'">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($row['manufacturer'] . ' ' . $row['type']); ?></h5>
                                    <p class="card-text">
                                        <strong>Display:</strong> <?php echo $row['display']; ?>"<br>
                                        <strong>Memory:</strong> <?php echo $row['memory']; ?> MB<br>
                                        <strong>OS:</strong> <?php echo htmlspecialchars($row['osname']); ?>
                                    </p>
                                    <h5 class="mt-2">£<?php echo number_format($row['price'] / 100, 2); ?></h5>
                                    <p class="text-<?php echo $row['pieces'] > 0 ? 'success' : 'danger'; ?>">
                                        <?php echo $row['pieces'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <a href="notebook_details.php?id=<?php echo $row['id']; ?>" class="btn btn-primary w-100">View Details</a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<div class='col-12'><p class='text-center'>No notebooks found matching your criteria.</p></div>";
                }
                ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mt-4">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page-1; ?>&manufacturer=<?php echo urlencode($manufacturer_filter); ?>&os=<?php echo urlencode($os_filter); ?>&min_price=<?php echo urlencode($min_price); ?>&max_price=<?php echo urlencode($max_price); ?>">
                            Previous
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&manufacturer=<?php echo urlencode($manufacturer_filter); ?>&os=<?php echo urlencode($os_filter); ?>&min_price=<?php echo urlencode($min_price); ?>&max_price=<?php echo urlencode($max_price); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page+1; ?>&manufacturer=<?php echo urlencode($manufacturer_filter); ?>&os=<?php echo urlencode($os_filter); ?>&min_price=<?php echo urlencode($min_price); ?>&max_price=<?php echo urlencode($max_price); ?>">
                            Next
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
        
        <?php include "includes/footer.php"; ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>