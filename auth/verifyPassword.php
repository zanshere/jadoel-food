<?php
include_once __DIR__ . "/../config/connect.php";
include_once __DIR__ . "/../config/baseURL.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['valid' => false]);
    exit;
}

$user_id = $_SESSION['user_id'];
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT pass FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

if ($user_data && password_verify($password, $user_data['pass'])) {
    echo json_encode(['valid' => true]);
} else {
    echo json_encode(['valid' => false]);
}