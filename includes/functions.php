<?php
/**
 * Helper functions for ReNew Notebooks application
 */

/**
 * Format price from cents to display format
 * 
 * @param int $price Price in cents
 * @return string Formatted price with currency symbol
 */
function formatPrice($price) {
    return '£' . number_format($price / 100, 2);
}

/**
 * Truncate text to specified length
 * 
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $append String to append if truncated
 * @return string Truncated text
 */
function truncateText($text, $length = 100, $append = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, ' '));
    
    return $text . $append;
}

/**
 * Check if user has required role
 * 
 * @param string $requiredRole Role required for access
 * @param string $userRole Current user's role
 * @return bool True if user has required role or higher
 */
function hasRole($requiredRole, $userRole) {
    $roleHierarchy = [
        'admin' => 3,
        'registered' => 2,
        'visitor' => 1
    ];
    
    if (!isset($roleHierarchy[$requiredRole]) || !isset($roleHierarchy[$userRole])) {
        return false;
    }
    
    return $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
}

/**
 * Generate a random string
 * 
 * @param int $length Length of the string
 * @return string Random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    
    return $randomString;
}

/**
 * Check if a string is a valid JSON
 * 
 * @param string $string String to check
 * @return bool True if valid JSON
 */
function isValidJson($string) {
    if (!is_string($string)) {
        return false;
    }
    
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

/**
 * Get file extension from filename
 * 
 * @param string $filename Filename
 * @return string File extension without dot
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file extension is allowed
 * 
 * @param string $extension File extension
 * @param array $allowedExtensions Array of allowed extensions
 * @return bool True if extension is allowed
 */
function isAllowedExtension($extension, $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif']) {
    return in_array(strtolower($extension), $allowedExtensions);
}

/**
 * Sanitize input data
 * 
 * @param mixed $data Data to sanitize
 * @return mixed Sanitized data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitizeInput($value);
        }
    } else {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
    }
    
    return $data;
}

/**
 * Get human-readable file size
 * 
 * @param int $bytes File size in bytes
 * @param int $precision Precision of the result
 * @return string Human-readable file size
 */
function formatFileSize($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Get current page URL
 * 
 * @return string Current page URL
 */
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    
    return "$protocol://$host$uri";
}

/**
 * Generate a slug from a string
 * 
 * @param string $string String to convert to slug
 * @return string Slug
 */
function slugify($string) {
    $string = preg_replace('~[^\pL\d]+~u', '-', $string);
    $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
    $string = preg_replace('~[^-\w]+~', '', $string);
    $string = trim($string, '-');
    $string = preg_replace('~-+~', '-', $string);
    $string = strtolower($string);
    
    if (empty($string)) {
        return 'n-a';
    }
    
    return $string;
}