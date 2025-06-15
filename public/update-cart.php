<?php
// Matikan error reporting untuk output yang bersih
error_reporting(0);
ini_set('display_errors', 0);

// Buffer output untuk mencegah output yang tidak diinginkan
ob_start();

try {
    include_once __DIR__ . '/../config/connect.php';
    include_once __DIR__ . '/../config/baseURL.php';

    // Clear any previous output
    ob_clean();
    
    // Set headers pertama kali
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Handle CORS
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");

    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Validasi method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }

    // Get input
    $input = file_get_contents('php://input');
    
    if (empty($input)) {
        throw new Exception('No data received', 400);
    }

    $data = json_decode($input, true);

    // Validasi JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg(), 400);
    }

    // Validasi cart data
    if (!isset($data['cart'])) {
        throw new Exception('Cart data missing', 400);
    }

    if (!is_array($data['cart'])) {
        throw new Exception('Invalid cart data format', 400);
    }

    // Validasi structure cart data
    foreach ($data['cart'] as $productId => $item) {
        if (!is_array($item)) {
            throw new Exception('Invalid cart item format', 400);
        }
        
        if (!isset($item['name'], $item['price'], $item['quantity'])) {
            throw new Exception('Missing required cart item fields', 400);
        }
        
        if (!is_numeric($item['price']) || !is_numeric($item['quantity'])) {
            throw new Exception('Invalid cart item values', 400);
        }
        
        if ($item['quantity'] < 1) {
            throw new Exception('Invalid quantity value', 400);
        }
    }

    // Update session
    $_SESSION['cart'] = $data['cart'];

    // Response sukses
    $response = [
        'status' => 'success',
        'message' => 'Cart updated successfully',
        'cart_count' => count($_SESSION['cart']),
        'timestamp' => time()
    ];

    echo json_encode($response);

} catch (Exception $e) {
    // Clear any output buffer
    ob_clean();
    
    // Set error response
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    
    $errorResponse = [
        'status' => 'error',
        'message' => $e->getMessage(),
        'code' => $code
    ];
    
    echo json_encode($errorResponse);
    
} catch (Error $e) {
    // Handle fatal errors
    ob_clean();
    
    http_response_code(500);
    $errorResponse = [
        'status' => 'error',
        'message' => 'Internal server error',
        'code' => 500
    ];
    
    echo json_encode($errorResponse);
    
} finally {
    // End output buffering
    ob_end_flush();
}

exit;