<?php
// api/pegawai/list.php
require_once '../../vendor/autoload.php';
require_once '../../config/Database.php';
require_once '../../src/JwtService.php';
require_once '../../src/AuthMiddleware.php';

header('Content-Type: application/json');

$db = (new Database())->getConnection();
$jwtService = new JwtService($db);
$auth = new AuthMiddleware($jwtService);

$auth->authenticate(['admin', 'superadmin', 'developer', 'supervisor']);

try {
    $sql = "SELECT p.id_person, p.nama, p.npwp, p.email, j.jabatan, u.username, u.is_active 
            FROM person p
            JOIN users u ON p.id_person = u.id_person
            LEFT JOIN jabatan j ON p.id_jabatan = j.id_jabatan
            ORDER BY p.nama ASC";

    $stmt = $db->query($sql);
    $data = $stmt->fetchAll();

    echo json_encode([
        'status' => true,
        'data' => $data
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}