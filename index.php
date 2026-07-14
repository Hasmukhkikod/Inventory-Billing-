<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Root Entry Point - Front Controller & Router
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Global Exception Handler
set_exception_handler(function (\Throwable $e) {
    error_log($e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    http_response_code(500);
    if (php_sapi_name() !== 'cli' && (!isset($_SERVER['HTTP_ACCEPT']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === false)) {
        echo "<div style='font-family:sans-serif; text-align:center; padding: 50px; color: #333;'>";
        echo "<h2>System Error Encountered</h2>";
        echo "<p>We are experiencing technical difficulties. Please try again later.</p>";
        echo "<p style='color:#999; font-size:12px; margin-top:20px;'>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    } else {
        echo json_encode(['status' => false, 'message' => 'Internal Server Error']);
    }
    exit;
});

use App\Controllers\DashboardController;
use App\Controllers\ProductController;
use App\Controllers\PurchaseController;
use App\Controllers\BillingController;
use App\Controllers\CustomerController;
use App\Controllers\SupplierController;
use App\Controllers\ExpenseController;
use App\Controllers\ReturnController;
use App\Controllers\ReportsController;
use App\Controllers\UserController;
use App\Controllers\SettingsController;
use App\Controllers\RoleController;
use App\Controllers\QuotationController;
use App\Controllers\ChallanController;
use App\Models\Database;
use App\Models\Auth;

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    // Dashboard
    $r->addRoute('GET', '/', [DashboardController::class, 'index']);
    $r->addRoute('GET', '/index', [DashboardController::class, 'index']);

    // Modules (Index)
    $r->addRoute('GET', '/products/index', [ProductController::class, 'index']);
    $r->addRoute('GET', '/purchases/index', [PurchaseController::class, 'index']);
    $r->addRoute('GET', '/billing/index', [BillingController::class, 'index']);
    $r->addRoute('GET', '/customers/index', [CustomerController::class, 'index']);
    $r->addRoute('GET', '/suppliers/index', [SupplierController::class, 'index']);
    $r->addRoute('GET', '/expenses/index', [ExpenseController::class, 'index']);
    $r->addRoute('GET', '/returns/index', [ReturnController::class, 'index']);
    $r->addRoute('GET', '/reports/index', [ReportsController::class, 'index']);
    $r->addRoute('GET', '/users/index', [UserController::class, 'index']);
    $r->addRoute('GET', '/roles/index', [RoleController::class, 'index']);
    $r->addRoute('GET', '/settings/index', [SettingsController::class, 'index']);
    $r->addRoute('GET', '/quotations/index', [QuotationController::class, 'index']);
    $r->addRoute('GET', '/challans/index', [ChallanController::class, 'index']);

    // Modules (Form)
    $r->addRoute('GET', '/products/form', [ProductController::class, 'form']);
    $r->addRoute('GET', '/purchases/form', [PurchaseController::class, 'form']);
    $r->addRoute('GET', '/billing/form', [BillingController::class, 'form']);
    $r->addRoute('GET', '/customers/form', [CustomerController::class, 'form']);
    $r->addRoute('GET', '/suppliers/form', [SupplierController::class, 'form']);
    $r->addRoute('GET', '/expenses/form', [ExpenseController::class, 'form']);
    $r->addRoute('GET', '/returns/form', [ReturnController::class, 'form']);
    $r->addRoute('GET', '/users/form', [UserController::class, 'form']);
    $r->addRoute('GET', '/roles/form', [RoleController::class, 'form']);
    $r->addRoute('GET', '/quotations/form', [QuotationController::class, 'form']);
    $r->addRoute('GET', '/challans/form', [ChallanController::class, 'form']);

    // Modules (View)
    $r->addRoute('GET', '/products/view', [ProductController::class, 'view']);
    $r->addRoute('GET', '/purchases/view', [PurchaseController::class, 'view']);
    $r->addRoute('GET', '/billing/view', [BillingController::class, 'view']);
    $r->addRoute('GET', '/customers/view', [CustomerController::class, 'view']);
    $r->addRoute('GET', '/suppliers/view', [SupplierController::class, 'view']);
    $r->addRoute('GET', '/expenses/view', [ExpenseController::class, 'view']);
    $r->addRoute('GET', '/returns/view', [ReturnController::class, 'view']);
    $r->addRoute('GET', '/quotations/view', [QuotationController::class, 'view']);
    $r->addRoute('GET', '/challans/view', [ChallanController::class, 'view']);

    // Day-End Report
    $r->addRoute('GET', '/billing/day_end', [BillingController::class, 'dayEnd']);
    $r->addRoute('GET', '/users/view', [UserController::class, 'view']);
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// Handle routing
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        // Fallback for not-yet-refactored endpoints (temporary during migration)
        if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
            require __DIR__ . $uri;
        } else if ($uri !== '/' && file_exists(__DIR__ . $uri . '.php')) {
            // Clean URL (extension hidden) for a standalone file, e.g. /login -> login.php
            require __DIR__ . $uri . '.php';
        } else if ($uri !== '/' && file_exists(__DIR__ . $uri . '/index.php')) {
            require __DIR__ . $uri . '/index.php';
        } else {
            echo "404 Not Found";
        }
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        http_response_code(405);
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        
        // Instantiate the controller and call the method
        $controllerName = $handler[0];
        $method = $handler[1];
        
        $db = new Database();
        $auth = new Auth($db);
        $controller = new $controllerName($db, $auth);
        
        // Pass ID from query string if available and route variables are empty
        if (empty($vars) && isset($_GET['id'])) {
            $vars[] = $_GET['id'];
        }
        
        call_user_func_array([$controller, $method], $vars);
        break;
}
