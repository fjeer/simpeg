<?php
// api/pegawai/delete.php
require_once '../../vendor/autoload.php';
require_once '../../config/Database.php';
require_once '../../src/JwtService.php';
require_once '../../src/AuthMiddleware.php';

header('Content-Type: application/json');

$db = (new Database())->getConnection();
$jwtService = new JwtService($db);
$auth = new AuthMiddleware($jwtService);

$auth->authenticate(['superadmin', 'developer']);

$id_person = $_POST['id_person'] ?? '';

try {
    $db->beginTransaction();

    // 1. Hapus User dulu
    $stmt1 = $db->prepare("DELETE FROM users WHERE id_person = ?");
    $stmt1->execute([$id_person]);

    // 2. Hapus Person
    $stmt2 = $db->prepare("DELETE FROM person WHERE id_person = ?");
    $stmt2->execute([$id_person]);

    $db->commit();
    echo json_encode([
        'status' => true, 
        'message' => 'Data pegawai dan akun berhasil dihapus'
    ]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        'status' => false, 
        'message' => 'Gagal hapus: ' . $e->getMessage()
    ]);
}