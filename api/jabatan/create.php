<?php
// api/jabatan/create.php

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

$jabatan = $input['jabatan'] ?? '';

if (empty($jabatan)) {
    echo json_encode([
        'status' => false, 
        'message' => 'Nama jabatan wajib diisi'
    ]);
    exit;
}

$stmt = $db->prepare("INSERT INTO jabatan (jabatan) VALUES (?)");
if ($stmt->execute([$jabatan])) {
    echo json_encode([
        'status' => true, 
        'message' => 'Jabatan berhasil ditambahkan'
    ]);
}