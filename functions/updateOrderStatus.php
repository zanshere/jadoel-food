<?php
include_once __DIR__ . "/../config/connect.php";
include_once __DIR__ . "/../config/baseURL.php";

// Debugging - bisa dihapus setelah fix
error_log("Update status request received: " . print_r($_POST, true));

// Cek login admin
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Pastikan ini adalah POST request dan ada update_status
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['update_status'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid request method or missing update_status flag']);
    exit;
}

// Pastikan order_id dan status ada
if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$order_id = (int)$_POST['order_id'];
$new_status = $_POST['status'];

// Validasi status
$allowed_statuses = ['Pending', 'Process', 'Delivery', 'Completed'];
if (!in_array($new_status, $allowed_statuses)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => "Invalid status value"]);
    exit;
}

// Update status
try {
    $stmt = $conn->prepare("UPDATE orders SET status = ?, update_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        // Simpan pesan di session
        $_SESSION['status_update_message'] = "Status pesanan #$order_id berhasil diubah ke $new_status";
        $_SESSION['status_update_message_type'] = 'success';
        
        echo json_encode([
            'success' => true,
            'message' => "Status updated successfully",
            'redirect' => BASE_URL . 'admin/orders.php'
        ]);
        exit;
    } else {
        throw new Exception("Database error: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Error updating order status: " . $e->getMessage());
    echo json_encode([
        'error' => "Failed to update status: " . $e->getMessage()
    ]);
    exit;
}
?>