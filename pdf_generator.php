<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: auth/login.php");
    exit;
}

require_once 'assets/tcpdf/tcpdf.php';
require_once "config/db_config.php";
require_once 'vendor/autoload.php';

// Get data for form dropdowns
$manufacturers_sql = "SELECT DISTINCT manufacturer FROM notebook ORDER BY manufacturer";
$manufacturers_result = $conn->query($manufacturers_sql);

$processors_sql = "SELECT id, CONCAT(manufacturer, ' ', type) as processor_name FROM processor ORDER BY manufacturer, type";
$processors_result = $conn->query($processors_sql);

$os_sql = "SELECT id, osname FROM opsystem ORDER BY osname";
$os_result = $conn->query($os_sql);

// Process form submission for PDF generation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Include TCPDF library
    require_once 'assets/tcpdf/tcpdf.php';
    
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('ReNew Notebooks');
    $pdf->SetAuthor('ReNew Ltd.');
    $pdf->SetTitle('Notebook Report');
    $pdf->SetSubject('Notebook Listing');
    
    // Set default header data
    $pdf->SetHeaderData('', 0, 'ReNew Notebooks', 'Generated on: ' . date('Y-m-d H:i:s'));
    
    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    
    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    
    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
    // Add a page
    $pdf->AddPage();
    
    // Build the query based on form input
    $where_clauses = [];
    $params = [];
    $types = '';
    
    if (!empty($_POST['manufacturer'])) {
        $where_clauses[] = "n.manufacturer = ?";
        $params[] = $_POST['manufacturer'];
        $types .= 's';
    }
    
    if (!empty($_POST['processorid'])) {
        $where_clauses[] = "n.processorid = ?";
        $params[] = $_POST['processorid'];
        $types .= 'i';
    }
    
    if (!empty($_POST['opsystemid'])) {
        $where_clauses[] = "n.opsystemid = ?";
        $params[] = $_POST['opsystemid'];
        $types .= 'i';
    }
    
    // Build the final where clause
    $where_clause = '';
    if (!empty($where_clauses)) {
        $where_clause = "WHERE " . implode(" AND ", $where_clauses);
    }
    
    // Get notebook data
    $sql = "SELECT n.*, p.manufacturer AS processor_manufacturer, p.type AS processor_type, o.osname 
            FROM notebook n 
            JOIN processor p ON n.processorid = p.id 
            JOIN opsystem o ON n.opsystemid = o.id 
            $where_clause
            ORDER BY n.manufacturer, n.type";
    
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }
    
    // Build report title based on filters
    $report_title = 'Notebook Report';
    $filters = [];
    
    if (!empty($_POST['manufacturer'])) {
        $filters[] = 'Manufacturer: ' . $_POST['manufacturer'];
    }
    
    if (!empty($_POST['processorid'])) {
        $processor_sql = "SELECT CONCAT(manufacturer, ' ', type) as name FROM processor WHERE id = ?";
        $processor_stmt = $conn->prepare($processor_sql);
        $processor_stmt->bind_param("i", $_POST['processorid']);
        $processor_stmt->execute();
        $processor_result = $processor_stmt->get_result();
        $processor_row = $processor_result->fetch_assoc();
        $filters[] = 'Processor: ' . $processor_row['name'];
    }
    
    if (!empty($_POST['opsystemid'])) {
        $os_sql = "SELECT osname FROM opsystem WHERE id = ?";
        $os_stmt = $conn->prepare($os_sql);
        $os_stmt->bind_param("i", $_POST['opsystemid']);
        $os_stmt->execute();
        $os_result = $os_stmt->get_result();
        $os_row = $os_result->fetch_assoc();
        $filters[] = 'Operating System: ' . $os_row['osname'];
    }
    
    if (!empty($filters)) {
        $report_title .= ' (' . implode(', ', $filters) . ')';
    }
    
    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    
    // Add report title
    $pdf->Cell(0, 15, $report_title, 0, 1, 'C');
    
    // Add report summary
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Total notebooks found: ' . $result->num_rows, 0, 1, 'L');
    $pdf->Ln(5);
    
    // Check if any notebooks found
    if ($result->num_rows > 0) {
        // Create the table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(60, 7, 'Notebook', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Display', 1, 0, 'C', true);
        $pdf->Cell(35, 7, 'Memory', 1, 0, 'C', true);
        $pdf->Cell(35, 7, 'Hard Disk', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Price', 1, 1, 'C', true);
        
        // Fill the table with notebook data
        $pdf->SetFont('helvetica', '', 9);
        
        while ($row = $result->fetch_assoc()) {
            $notebook_name = $row['manufacturer'] . ' ' . $row['type'];
            $display = $row['display'] . '"';
            $memory = $row['memory'] . ' MB';
            $harddisk = $row['harddisk'] . ' GB';
            $price = '£' . number_format($row['price'] / 100, 2);
            
            $pdf->Cell(60, 6, $notebook_name, 1, 0, 'L');
            
            $pdf->Cell(30, 6, $display, 1, 0, 'C');
            $pdf->Cell(35, 6, $memory, 1, 0, 'C');
            $pdf->Cell(35, 6, $harddisk, 1, 0, 'C');
            $pdf->Cell(30, 6, $price, 1, 1, 'C');
        }
        
        // Add additional information
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 7, 'Specifications', 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 10);
        
        // Reset result set
        if (!empty($params)) {
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }
        
        while ($row = $result->fetch_assoc()) {
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 7, $row['manufacturer'] . ' ' . $row['type'], 0, 1, 'L');
            
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(40, 6, 'Processor:', 0, 0, 'L');
            $pdf->Cell(0, 6, $row['processor_manufacturer'] . ' ' . $row['processor_type'], 0, 1, 'L');
            
            $pdf->Cell(40, 6, 'Memory:', 0, 0, 'L');
            $pdf->Cell(0, 6, $row['memory'] . ' MB', 0, 1, 'L');
            
            $pdf->Cell(40, 6, 'Hard Disk:', 0, 0, 'L');
            $pdf->Cell(0, 6, $row['harddisk'] . ' GB', 0, 1, 'L');
            
            $pdf->Cell(40, 6, 'Display:', 0, 0, 'L');
            $pdf->Cell(0, 6, $row['display'] . '"', 0, 1, 'L');
            
            $pdf->Cell(40, 6, 'Video Controller:', 0, 0, 'L');
            $pdf->Cell(0, 6, $row['videocontroller'], 0, 1, 'L');
            
            $pdf->Cell(40, 6, 'Operating System:', 0, 0, 'L');
            $pdf->Cell(0, 6, $row['osname'], 0, 1, 'L');
            
            $pdf->Cell(40, 6, 'Price:', 0, 0, 'L');
            $pdf->Cell(0, 6, '£' . number_format($row['price'] / 100, 2), 0, 1, 'L');
            
            $pdf->Cell(40, 6, 'In Stock:', 0, 0, 'L');
            $pdf->Cell(0, 6, $row['pieces'] . ' units', 0, 1, 'L');
            
            $pdf->Ln(5);
        }
    } else {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'No notebooks found matching the specified criteria.', 0, 1, 'C');
    }
    
    // Output the PDF as download
    $pdf->Output('notebook_report.pdf', 'D');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Generator - ReNew Notebooks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include "includes/header.php"; ?>
    
    <div class="container py-5">
        <h1>Generate PDF Report</h1>
        <p class="lead">Select criteria to generate a PDF report of notebooks.</p>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Report Options</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="pdf_generator.php">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="manufacturer" class="form-label">Manufacturer</label>
                                <select class="form-select" id="manufacturer" name="manufacturer">
                                    <option value="">All Manufacturers</option>
                                    <?php while($manufacturer = $manufacturers_result->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($manufacturer['manufacturer']); ?>">
                                            <?php echo htmlspecialchars($manufacturer['manufacturer']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="processorid" class="form-label">Processor</label>
                                <select class="form-select" id="processorid" name="processorid">
                                    <option value="">All Processors</option>
                                    <?php while($processor = $processors_result->fetch_assoc()): ?>
                                        <option value="<?php echo $processor['id']; ?>">
                                            <?php echo htmlspecialchars($processor['processor_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="opsystemid" class="form-label">Operating System</label>
                                <select class="form-select" id="opsystemid" name="opsystemid">
                                    <option value="">All Operating Systems</option>
                                    <?php while($os = $os_result->fetch_assoc()): ?>
                                        <option value="<?php echo $os['id']; ?>">
                                            <?php echo htmlspecialchars($os['osname']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Generate PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include "includes/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>