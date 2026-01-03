<?php
// api/jabatan/delete.php
require_once '../../vendor/autoload.php';
require_once '../../config/Database.php';
require_once '../../src/JwtService.php';
require_once '../../src/AuthMiddleware.php';

header('Content-Type: application/json');

$db = (new Database())->getConnection();
$jwtService = new JwtService($db);
$auth = new AuthMiddleware($jwtService);

$auth->authenticate(['superadmin', 'developer']);
$input = json_decode(file_get_contents("php://input"), true);

$id_jabatan = $input['id_jabatan'] ?? '';

try {
    $stmt = $db->prepare("DELETE FROM jabatan WHERE id_jabatan = ?");
    $stmt->execute([$id_jabatan]);

    echo json_encode([
        'status' => true, 
        'message' => 'Jabatan berhasil dihapus'
    ]);
} catch (PDOException $e) {
    // Menangani error jika jabatan masih dipakai oleh data person
    if ($e->getCode() == "23000") {
        echo json_encode([
            'status' => false, 
            'message' => 'Gagal: Jabatan masih digunakan oleh pegawai'
        ]);
    } else {
        echo json_encode([
            'status' => false, 
            'message' => $e->getMessage()
        ]);
    }
}