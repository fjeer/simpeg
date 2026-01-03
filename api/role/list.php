<?php
// api/role/list.php
require_once '../../vendor/autoload.php';
require_once '../../config/Database.php';
require_once '../../src/JwtService.php';
require_once '../../src/AuthMiddleware.php';

header('Content-Type: application/json');

$db = (new Database())->getConnection();
$jwtService = new JwtService($db);
$auth = new AuthMiddleware($jwtService);

$userData = $auth->authenticate(['admin', 'superadmin', 'developer']);
$currentUserRole = $userData['data']['role'];

try {
    // Logika Hierarki: Filter role apa saja yang boleh dipilih oleh user saat ini
    if ($currentUserRole === 'admin') {
        // Admin hanya boleh melihat role admin (3), supervisor (4), user (5)
        $sql = "SELECT id_role, nama_role FROM roles WHERE id_role IN (3, 4, 5) ORDER BY id_role ASC";
    } elseif ($currentUserRole === 'superadmin') {
        // Superadmin boleh melihat superadmin (2) sampai user (5)
        $sql = "SELECT id_role, nama_role FROM roles WHERE id_role IN (2, 3, 4, 5) ORDER BY id_role ASC";
    } else {
        // Developer boleh melihat semua
        $sql = "SELECT id_role, nama_role FROM roles ORDER BY id_role ASC";
    }

    $stmt = $db->query($sql);
    $roles = $stmt->fetchAll();

    echo json_encode([
        'status' => true,
        'data' => $roles
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => false, 
        'message' => $e->getMessage()
    ]);
}