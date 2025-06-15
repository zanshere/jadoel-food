<?php
include_once __DIR__ . '/../config/connect.php';
include_once __DIR__ . '/../config/baseURL.php';

// Matikan error reporting untuk output yang bersih
error_reporting(0);
ini_set('display_errors', 0);

// Buffer output untuk mencegah output yang tidak diinginkan
ob_start();

try {
    // Clear any previous output
    ob_clean();
    
    // Set header
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Handle CORS if needed
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET");
    header("Access-Control-Allow-Headers: Content-Type");

    // Validasi method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method not allowed');
    }

    // Validasi parameter ID
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('Product ID not provided');
    }

    $productId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($productId === false || $productId <= 0) {
        throw new Exception('Invalid product ID');
    }

    // Pastikan koneksi database tersedia
    if (!isset($conn) || !$conn) {
        throw new Exception('Database connection failed');
    }

    // Get product stock dengan prepared statement
    $query = $conn->prepare("SELECT stock, product_name FROM products WHERE id = ? AND is_available = 1");
    if (!$query) {
        throw new Exception('Database query preparation failed');
    }
    
    $query->bind_param("i", $productId);
    
    if (!$query->execute()) {
        throw new Exception('Database query execution failed');
    }
    
    $result = $query->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Product not found or not available');
    }

    $product = $result->fetch_assoc();
    $stock = (int)$product['stock'];

    // Response sukses
    $response = [
        'success' => true,
        'stock' => $stock,
        'product_name' => $product['product_name']
    ];

    echo json_encode($response);

} catch (Exception $e) {
    // Clear any output buffer
    ob_clean();
    
    // Set error response
    http_response_code(400);
    $errorResponse = [
        'success' => false,
        'message' => $e->getMessage(),
        'stock' => 0
    ];
    
    echo json_encode($errorResponse);
} catch (Error $e) {
    // Handle fatal errors
    ob_clean();
    
    http_response_code(500);
    $errorResponse = [
        'success' => false,
        'message' => 'Internal server error',
        'stock' => 0
    ];
    
    echo json_encode($errorResponse);
} finally {
    // End output buffering and clean up
    ob_end_flush();
    
    // Close database connection if exists
    if (isset($query)) {
        $query->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}

exit;