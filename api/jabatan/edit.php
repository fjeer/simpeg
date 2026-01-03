<?php
// api/jabatan/edit.php
require_once '../../vendor/autoload.php';
require_once '../../config/Database.php';
require_once '../../src/JwtService.php';
require_once '../../src/AuthMiddleware.php';

header('Content-Type: application/json');

$db = (new Database())->getConnection();
$jwtService = new JwtService($db);
$auth = new AuthMiddleware($jwtService);
$auth->authenticate();

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode([
        'status' => false, 
        'message' => 'ID tidak ditemukan'
    ]);
    exit;
}

try {
    $stmt = $db->prepare("SELECT * FROM jabatan WHERE id_jabatan = ?");
    $stmt->execute([$id]);
    $jabatan = $stmt->fetch();

    if ($jabatan) {
        echo json_encode([
            'status' => true, 
            'data' => $jabatan]);
    } else {
        echo json_encode([
            'status' => false, 
            'message' => 'Data tidak ditemukan'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'status' => false, 
        'message' => $e->getMessage()
    ]);
}