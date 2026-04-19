<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

define('AUTH_EMAIL', 'nigamvivek2805@gmail.com');
define('AUTH_PASSWORD', '12345678');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === AUTH_EMAIL && $password === AUTH_PASSWORD) {
    $_SESSION['logged_in'] = true;
    ob_end_clean();
    echo json_encode(['success' => true]);
} else {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
}
