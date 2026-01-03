<?php
// api/pegawai/detail.php

require_once '../../vendor/autoload.php';
require_once '../../config/Database.php';
require_once '../../src/JwtService.php';
require_once '../../src/AuthMiddleware.php';

header('Content-Type: application/json');

$db = (new Database())->getConnection();
$jwtService = new JwtService($db);
$auth = new AuthMiddleware($jwtService);

$auth->authenticate(['admin', 'superadmin', 'developer', 'supervisor']);

$id_person = $_GET['id'] ?? '';

$sql = "SELECT p.*, u.username, r.nama_role as role, r.id_role, j.jabatan, ds.*, kc.*, kb.*, pv.*
                      FROM users u
                      JOIN roles r ON u.id_role = r.id_role
                      LEFT JOIN person p ON u.id_person = p.id_person 
                      LEFT JOIN jabatan j ON p.id_jabatan = j.id_jabatan
                      LEFT JOIN almt_desa ds ON p.id_desa = ds.id_desa
                      LEFT JOIN almt_kecamatan kc ON ds.id_kecamatan = kc.id_kecamatan
                      LEFT JOIN almt_kabupaten kb ON kc.id_kabupaten = kb.id_kabupaten
                      LEFT JOIN almt_provinsi pv ON kb.id_provinsi = pv.id_provinsi
                      WHERE p.id_person = ?";

$stmt = $db->prepare($sql);
$stmt->execute([$id_person]);
$data = $stmt->fetch();

echo json_encode([
    'status' => true, 
    'data' => $data
]);