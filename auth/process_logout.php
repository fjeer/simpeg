<?php
// auth/process_logout.php
require_once '../vendor/autoload.php';
require_once '../config/Database.php';
require_once '../src/JwtService.php';
require_once '../src/AuthMiddleware.php';

header('Content-Type: application/json');

$db = (new Database())->getConnection();
$jwtService = new JwtService($db);
$auth = new AuthMiddleware($jwtService);

// 1. Pastikan user login dulu sebelum bisa logout
// Kita tidak perlu cek role spesifik, cukup pastikan token valid
$auth->authenticate();

// 2. Ambil token dari Header Authorization
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
preg_match('/Bearer\s(\S+)/', $authHeader, $matches);
$token = $matches[1];

// 3. Jalankan fungsi revoke
if ($jwtService->revokeToken($token)) {
    echo json_encode([
        'status' => true,
        'message' => 'Logout berhasil, token telah dicabut.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Gagal melakukan logout.'
    ]);
}