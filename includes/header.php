<?php
// Check if session has been started in the including file
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Make sure we have a valid db connection
require_once __DIR__ . "/../config/db_config.php";
$conn = getDbConnection();

// Get current user role
$userRole = isset($_SESSION["role"]) ? $_SESSION["role"] : 'visitor';

// Include menu functions if not already included
require_once __DIR__ . "/menu.php";

// Get menu items with error handling
try {
    $menuItems = getMenuItems($conn, null, $userRole);
} catch (Exception $e) {
    // Fallback for critical errors
    $menuItems = [];
}
?>

<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo getCorrectPath('index.php'); ?>">
                ReNew Notebooks
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <?php echo renderMenu($menuItems); ?>
                
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo htmlspecialchars($_SESSION["username"]); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                                <?php if($_SESSION["role"] === "admin"): ?>
                                    <li><a class="dropdown-item" href="<?php echo getCorrectPath('admin/index.php'); ?>">Admin Panel</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?php echo getCorrectPath('auth/logout.php'); ?>">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo getCorrectPath('auth/login.php'); ?>">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo getCorrectPath('auth/register.php'); ?>">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>