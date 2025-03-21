<?php
/**
 * Configuration file for ReNew Notebooks application
 */

// Application settings
define('APP_NAME', 'ReNew Notebooks');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/renew-notebooks');
define('APP_ADMIN_EMAIL', 'admin@renew-notebooks.com');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
session_name('RENEW_SESSION');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1); // Set to 0 in production

// Timezone setting
date_default_timezone_set('Europe/London');

// Define user roles
define('ROLE_VISITOR', 'visitor');
define('ROLE_REGISTERED', 'registered');
define('ROLE_ADMIN', 'admin');

// Define paths
define('BASE_PATH', dirname(__DIR__) . '/');
define('UPLOADS_PATH', BASE_PATH . 'assets/uploads/');
define('IMAGES_PATH', BASE_PATH . 'assets/images/');

// TCPDF configuration
define('PDF_CREATOR', APP_NAME);
define('PDF_AUTHOR', 'ReNew Ltd.');
define('PDF_UNIT', 'mm');
define('PDF_PAGE_FORMAT', 'A4');
define('PDF_MARGIN_LEFT', 15);
define('PDF_MARGIN_TOP', 15);
define('PDF_MARGIN_RIGHT', 15);
define('PDF_MARGIN_BOTTOM', 15);
define('PDF_FONT', 'helvetica');