<?php
// Set headers for RESTful API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Include database configuration
require_once "../config/db_config.php";

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get endpoint from the URL
$request_uri = $_SERVER['REQUEST_URI'];
$uri_parts = explode('/', trim(parse_url($request_uri, PHP_URL_PATH), '/'));
$endpoint = end($uri_parts);

// Get ID from query string if provided
$id = isset($_GET['id']) ? $_GET['id'] : null;

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        if ($id) {
            // Get single notebook
            getNotebook($conn, $id);
        } else {
            // Get all notebooks
            getNotebooks($conn);
        }
        break;
    case 'POST':
        // Create new notebook
        createNotebook($conn);
        break;
    case 'PUT':
        // Update existing notebook
        updateNotebook($conn, $id);
        break;
    case 'DELETE':
        // Delete notebook
        deleteNotebook($conn, $id);
        break;
    default:
        // Method not allowed
        http_response_code(405);
        echo json_encode(['message' => 'Method Not Allowed']);
        break;
}

// Functions for API operations

/**
 * Get all notebooks
 */
function getNotebooks($conn) {
    $sql = "SELECT n.*, p.manufacturer AS processor_manufacturer, p.type AS processor_type, o.osname 
            FROM notebook n 
            JOIN processor p ON n.processorid = p.id 
            JOIN opsystem o ON n.opsystemid = o.id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $notebooks = array();
        while ($row = $result->fetch_assoc()) {
            $row['price'] = number_format($row['price'] / 100, 2);
            $notebooks[] = $row;
        }
        
        http_response_code(200);
        echo json_encode(['data' => $notebooks]);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'No notebooks found']);
    }
}

/**
 * Get a single notebook by ID
 */
function getNotebook($conn, $id) {
    $sql = "SELECT n.*, p.manufacturer AS processor_manufacturer, p.type AS processor_type, o.osname 
            FROM notebook n 
            JOIN processor p ON n.processorid = p.id 
            JOIN opsystem o ON n.opsystemid = o.id 
            WHERE n.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $notebook = $result->fetch_assoc();
        $notebook['price'] = number_format($notebook['price'] / 100, 2);
        
        http_response_code(200);
        echo json_encode(['data' => $notebook]);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Notebook not found']);
    }
}

/**
 * Create a new notebook
 */
function createNotebook($conn) {
    // Get posted data
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Validate required fields
    $required_fields = ['manufacturer', 'type', 'display', 'memory', 'harddisk', 'videocontroller', 'price', 'processorid', 'opsystemid', 'pieces'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            http_response_code(400);
            echo json_encode(['message' => "Missing required field: $field"]);
            return;
        }
    }
    
    // Convert price to cents
    $price_cents = $data['price'] * 100;
    
    // Prepare SQL statement
    $sql = "INSERT INTO notebook (manufacturer, type, display, memory, harddisk, videocontroller, price, processorid, opsystemid, pieces) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdiisiiii", 
                    $data['manufacturer'], 
                    $data['type'], 
                    $data['display'], 
                    $data['memory'], 
                    $data['harddisk'], 
                    $data['videocontroller'], 
                    $price_cents, 
                    $data['processorid'], 
                    $data['opsystemid'], 
                    $data['pieces']);
    
    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        
        http_response_code(201);
        echo json_encode([
            'message' => 'Notebook created successfully',
            'id' => $new_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'message' => 'Failed to create notebook',
            'error' => $conn->error
        ]);
    }
}

/**
 * Update an existing notebook
 */
function updateNotebook($conn, $id) {
    // Check if notebook exists
    $check_sql = "SELECT id FROM notebook WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['message' => 'Notebook not found']);
        return;
    }
    
    // Get posted data
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Build update query dynamically based on provided fields
    $updates = [];
    $types = "";
    $params = [];
    
    // Check each possible field
    if (isset($data['manufacturer'])) {
        $updates[] = "manufacturer = ?";
        $types .= "s";
        $params[] = $data['manufacturer'];
    }
    
    if (isset($data['type'])) {
        $updates[] = "type = ?";
        $types .= "s";
        $params[] = $data['type'];
    }
    
    if (isset($data['display'])) {
        $updates[] = "display = ?";
        $types .= "d";
        $params[] = $data['display'];
    }
    
    if (isset($data['memory'])) {
        $updates[] = "memory = ?";
        $types .= "i";
        $params[] = $data['memory'];
    }
    
    if (isset($data['harddisk'])) {
        $updates[] = "harddisk = ?";
        $types .= "i";
        $params[] = $data['harddisk'];
    }
    
    if (isset($data['videocontroller'])) {
        $updates[] = "videocontroller = ?";
        $types .= "s";
        $params[] = $data['videocontroller'];
    }
    
    if (isset($data['price'])) {
        $updates[] = "price = ?";
        $types .= "i";
        $params[] = $data['price'] * 100; // Convert to cents
    }
    
    if (isset($data['processorid'])) {
        $updates[] = "processorid = ?";
        $types .= "i";
        $params[] = $data['processorid'];
    }
    
    if (isset($data['opsystemid'])) {
        $updates[] = "opsystemid = ?";
        $types .= "i";
        $params[] = $data['opsystemid'];
    }
    
    if (isset($data['pieces'])) {
        $updates[] = "pieces = ?";
        $types .= "i";
        $params[] = $data['pieces'];
    }
    
    // If no fields to update
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['message' => 'No fields to update']);
        return;
    }
    
    // Create SQL update string
    $sql = "UPDATE notebook SET " . implode(', ', $updates) . " WHERE id = ?";
    $types .= "i";
    $params[] = $id;
    
    // Prepare and execute statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['message' => 'Notebook updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode([
            'message' => 'Failed to update notebook',
            'error' => $conn->error
        ]);
    }
}

/**
 * Delete a notebook
 */
function deleteNotebook($conn, $id) {
    // Check if notebook exists
    $check_sql = "SELECT id FROM notebook WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['message' => 'Notebook not found']);
        return;
    }
    
    // Prepare delete statement
    $sql = "DELETE FROM notebook WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['message' => 'Notebook deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode([
            'message' => 'Failed to delete notebook',
            'error' => $conn->error
        ]);
    }
}
?>