<?php
// api/jabatan/update.php
require_once '../../vendor/autoload.php';
require_once '../../config/Database.php';
require_once '../../src/JwtService.php';
require_once '../../src/AuthMiddleware.php';

header('Content-Type: application/json');

$db = (new Database())->getConnection();
$jwtService = new JwtService($db);
$auth = new AuthMiddleware($jwtService);

$auth->authenticate(['admin', 'superadmin', 'developer']);
$input = json_decode(file_get_contents("php://input"), true);

$id_jabatan = $input['id_jabatan'] ?? '';
$nama_baru = $input['jabatan'] ?? '';

if (empty($id_jabatan) || empty($nama_baru)) {
    echo json_encode([
        'status' => false, 
        'message' => 'ID dan Nama jabatan baru wajib diisi'
    ]);
    exit;
}

$stmt = $db->prepare("UPDATE jabatan SET jabatan = ? WHERE id_jabatan = ?");
if ($stmt->execute([$nama_baru, $id_jabatan])) {
    echo json_encode([
        'status' => true, 
        'message' => 'Jabatan berhasil diperbarui'
    ]);
}