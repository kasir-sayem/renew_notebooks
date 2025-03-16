<?php
function getMenuItems($conn, $parentId = null, $userRole = 'visitor') {
    $items = array();
    
    $sql = "SELECT * FROM menu_items WHERE parent_id ";
    if ($parentId === null) {
        $sql .= "IS NULL";
        $stmt = $conn->prepare($sql);
    } else {
        $sql .= "= ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $parentId);
    }
    
    $stmt->execute();
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
    
    return $items;
}

function hasPermission($userRole, $requiredRole) {
    $roleHierarchy = [
        'admin' => 3,
        'registered' => 2,
        'visitor' => 1
    ];
    
    return $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
}

function renderMenu($items) {
    $html = '<ul class="navbar-nav">';
    
    foreach ($items as $item) {
        $hasChildren = isset($item['children']) && !empty($item['children']);
        
        if ($hasChildren) {
            $html .= '<li class="nav-item dropdown">';
            $html .= '<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown'.$item['id'].'" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
            $html .= htmlspecialchars($item['name']);
            $html .= '</a>';
            $html .= '<ul class="dropdown-menu" aria-labelledby="navbarDropdown'.$item['id'].'">';
            
            foreach ($item['children'] as $child) {
                $html .= '<li><a class="dropdown-item" href="'.$child['url'].'">';
                $html .= htmlspecialchars($child['name']);
                $html .= '</a></li>';
            }
            
            $html .= '</ul></li>';
        } else {
            $html .= '<li class="nav-item">';
            $html .= '<a class="nav-link" href="'.$item['url'].'">';
            $html .= htmlspecialchars($item['name']);
            $html .= '</a></li>';
        }
    }
    
    $html .= '</ul>';
    return $html;
}
?>