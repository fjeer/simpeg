<?php
// api/auth/change_password.php
require_once '../../vendor/autoload.php';
require_once '../../config/Database.php';
require_once '../../src/JwtService.php';
require_once '../../src/AuthMiddleware.php';

header('Content-Type: application/json');

$db = (new Database())->getConnection();
$jwtService = new JwtService($db);
$auth = new AuthMiddleware($jwtService);

$userData = $auth->authenticate();
$input = json_decode(file_get_contents("php://input"), true);

$currentPassword = $input['current_password'] ?? '';
$newPassword = $input['new_password'] ?? '';

try {
    // 1. Ambil password lama dari DB
    $stmt = $db->prepare("SELECT password_hash FROM users WHERE id_user = ?");
    $stmt->execute([$userData['data']['id_user']]);
    $user = $stmt->fetch();

    // 2. Verifikasi Password Lama
    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
        http_response_code(403);
        echo json_encode([
            'status' => false, 
            'message' => 'Password saat ini salah!'
        ]);
        exit;
    }

    // 3. Hash Password Baru & Update
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $update = $db->prepare("UPDATE users SET password_hash = ? WHERE id_user = ?");
    $update->execute([$hashedPassword, $userData['data']['id_user']]);

    echo json_encode([
        'status' => true, 
        'message' => 'Password berhasil diperbarui'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => false, 
        'message' => $e->getMessage()
    ]);
}