<?php
// me.php
require_once 'vendor/autoload.php';
require_once 'config/Database.php';
require_once 'src/JwtService.php';
require_once 'src/AuthMiddleware.php';

header('Content-Type: application/json');

$db = (new Database())->getConnection();
$jwtService = new JwtService($db);
$auth = new AuthMiddleware($jwtService);

// Pastikan semua role diizinkan memanggil profile sendiri
$userData = $auth->authenticate(['admin', 'developer', 'superadmin', 'supervisor', 'user']);
// AMBIL DETAIL LENGKAP TERMASUK ROLE
$stmt = $db->prepare("SELECT p.*, u.username, r.nama_role as role, j.jabatan, ds.desa, kc.kecamatan, kb.kabupaten, pv.provinsi
                      FROM users u
                      JOIN roles r ON u.id_role = r.id_role
                      LEFT JOIN person p ON u.id_person = p.id_person 
                      LEFT JOIN jabatan j ON p.id_jabatan = j.id_jabatan
                      LEFT JOIN almt_desa ds ON p.id_desa = ds.id_desa
                      LEFT JOIN almt_kecamatan kc ON ds.id_kecamatan = kc.id_kecamatan
                      LEFT JOIN almt_kabupaten kb ON kc.id_kabupaten = kb.id_kabupaten
                      LEFT JOIN almt_provinsi pv ON kb.id_provinsi = pv.id_provinsi
                      WHERE u.id_user = ?");
$stmt->execute([$userData['data']['id_user']]);
$profile = $stmt->fetch();
if (!$profile) {
    http_response_code(404);
    echo json_encode([
        'status' => false, 
        'message' => 'User tidak ditemukan']);
    exit;
}

echo json_encode([
    'status' => true,
    'message' => 'Data profil berhasil diambil',
    'data' => $profile
]);