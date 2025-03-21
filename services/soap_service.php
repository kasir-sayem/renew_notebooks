<?php
// Include database configuration
require_once "../config/db_config.php";

// Set SOAP WSDL Mode
ini_set("soap.wsdl_cache_enabled", "0");

// Define the NotebookService class
class NotebookService {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Get all notebooks
     * @return array Notebooks
     */
    public function getAllNotebooks() {
        $sql = "SELECT n.*, p.manufacturer AS processor_manufacturer, p.type AS processor_type, o.osname 
                FROM notebook n 
                JOIN processor p ON n.processorid = p.id 
                JOIN opsystem o ON n.opsystemid = o.id";
        $result = $this->conn->query($sql);
        
        $notebooks = array();
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $notebooks[] = $row;
            }
        }
        
        return $notebooks;
    }
    
    /**
     * Get notebook by ID
     * @param int $id Notebook ID
     * @return array Notebook details
     */
    public function getNotebookById($id) {
        $sql = "SELECT n.*, p.manufacturer AS processor_manufacturer, p.type AS processor_type, o.osname 
                FROM notebook n 
                JOIN processor p ON n.processorid = p.id 
                JOIN opsystem o ON n.opsystemid = o.id 
                WHERE n.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Get notebooks by manufacturer
     * @param string $manufacturer Manufacturer name
     * @return array Notebooks
     */
    public function getNotebooksByManufacturer($manufacturer) {
        $sql = "SELECT n.*, p.manufacturer AS processor_manufacturer, p.type AS processor_type, o.osname 
                FROM notebook n 
                JOIN processor p ON n.processorid = p.id 
                JOIN opsystem o ON n.opsystemid = o.id 
                WHERE n.manufacturer = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $manufacturer);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notebooks = array();
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $notebooks[] = $row;
            }
        }
        
        return $notebooks;
    }
    
    /**
     * Get all processors
     * @return array Processors
     */
    public function getAllProcessors() {
        $sql = "SELECT * FROM processor";
        $result = $this->conn->query($sql);
        
        $processors = array();
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $processors[] = $row;
            }
        }
        
        return $processors;
    }
    
    /**
     * Get all operating systems
     * @return array Operating systems
     */
    public function getAllOperatingSystems() {
        $sql = "SELECT * FROM opsystem";
        $result = $this->conn->query($sql);
        
        $operating_systems = array();
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $operating_systems[] = $row;
            }
        }
        
        return $operating_systems;
    }
}

// Create SOAP Server
$server = new SoapServer(null, array(
    'uri' => 'http://localhost/renew-notebooks/services/soap_service.php'
));

// Create NotebookService instance and register it
$service = new NotebookService($conn);
$server->setObject($service);

// Handle SOAP requests
$server->handle();
?>