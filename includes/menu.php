<?php
function getMenuItems($conn, $parentId = null, $userRole = 'visitor') {
    // Check if connection is valid
    if (!$conn || $conn->connect_errno) {
        // Try to get a fresh connection
        require_once __DIR__ . "/../config/db_config.php";
        $conn = getDbConnection();
        
        // If still not working, return empty menu
        if (!$conn || $conn->connect_errno) {
            return [];
        }
    }
    
    $items = array();
    
    try {
        $sql = "SELECT * FROM menu_items WHERE parent_id ";
        if ($parentId === null) {
            $sql .= "IS NULL";
            $stmt = $conn->prepare($sql);
        } else {
            $sql .= "= ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $parentId);
            }
        }
        
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                // Check if user has permission to see this menu item
                if (hasPermission($userRole, $row['role_required'])) {
                    $item = $row;
                    $children = getMenuItems($conn, $row['id'], $userRole);
                    if (!empty($children)) {
                        $item['children'] = $children;
                    }
                    $items[] = $item;
                }
            }
        }
    } catch (Exception $e) {
        // Error handling - just return empty array
        return [];
    }
    
    return $items;
}

function hasPermission($userRole, $requiredRole) {
    $roleHierarchy = [
        'admin' => 3,
        'registered' => 2,
        'visitor' => 1
    ];
    
    if (!isset($roleHierarchy[$userRole]) || !isset($roleHierarchy[$requiredRole])) {
        return false;
    }
    
    return $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
}

/**
 * Get correct relative path to a file
 * 
 * @param string $path Path relative to project root
 * @return string Corrected path
 */
function getCorrectPath($path) {
    $currentPath = $_SERVER['PHP_SELF'];
    // Base path to the project
    $projectPath = 'renew-notebooks';
    
    // Find position of project path in the current URL
    $pos = strpos($currentPath, $projectPath);
    if ($pos === false) {
        // Fallback if project path not found
        return $path;
    }
    
    // Count directory depth after the project root
    $relevantPath = substr($currentPath, $pos + strlen($projectPath));
    $depth = substr_count($relevantPath, '/');
    
    if ($depth <= 1) {
        // In root folder
        return $path;
    } else {
        // In subfolder(s)
        $prefix = str_repeat('../', $depth - 1);
        return $prefix . $path;
    }
}

function renderMenu($items) {
    if (empty($items)) {
        // Return a basic menu if there are no items
        return '<ul class="navbar-nav">
                  <li class="nav-item"><a class="nav-link" href="' . getCorrectPath('index.php') . '">Home</a></li>
                  <li class="nav-item"><a class="nav-link" href="' . getCorrectPath('notebooks.php') . '">Notebooks</a></li>
                </ul>';
    }
    
    $html = '<ul class="navbar-nav">';
    
    foreach ($items as $item) {
        $hasChildren = isset($item['children']) && !empty($item['children']);
        
        // Fix URL path
        $url = getCorrectPath($item['url']);
        
        if ($hasChildren) {
            $html .= '<li class="nav-item dropdown">';
            $html .= '<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown'.$item['id'].'" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
            $html .= htmlspecialchars($item['name']);
            $html .= '</a>';
            $html .= '<ul class="dropdown-menu" aria-labelledby="navbarDropdown'.$item['id'].'">';
            
            foreach ($item['children'] as $child) {
                // Fix child URL path
                $childUrl = getCorrectPath($child['url']);
                
                $html .= '<li><a class="dropdown-item" href="'.$childUrl.'">';
                $html .= htmlspecialchars($child['name']);
                $html .= '</a></li>';
            }
            
            $html .= '</ul></li>';
        } else {
            $html .= '<li class="nav-item">';
            $html .= '<a class="nav-link" href="'.$url.'">';
            $html .= htmlspecialchars($item['name']);
            $html .= '</a></li>';
        }
    }
    
    $html .= '</ul>';
    return $html;
}
?>