<?php
// api/pegawai/toggle_status.php
require_once '../../vendor/autoload.php';
require_once '../../config/Database.php';
require_once '../../src/JwtService.php';
require_once '../../src/AuthMiddleware.php';

header('Content-Type: application/json');

$db = (new Database())->getConnection();
$jwtService = new JwtService($db);
$auth = new AuthMiddleware($jwtService);

// Proteksi akses
$auth->authenticate(['superadmin', 'developer']);

$id_user = $_POST['id_user'] ?? '';
$status = $_POST['is_active'] ?? ''; // 1 untuk aktif, 0 untuk non-aktif

if ($id_user === '' || $status === '') {
    echo json_encode(['status' => false, 'message' => 'ID User dan Status diperlukan']);
    exit;
}

$stmt = $db->prepare("UPDATE users SET is_active = ? WHERE id_user = ?");
if ($stmt->execute([$status, $id_user])) {
    echo json_encode([
        'status' => true, 
        'message' => 'Status user berhasil diperbarui'
    ]);
} else {
    echo json_encode([
        'status' => false, 
        'message' => 'Gagal mengubah status user'
    ]);
}