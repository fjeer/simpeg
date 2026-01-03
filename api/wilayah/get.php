<?php
// api/wilayah/get.php
require_once '../../config/Database.php';
header('Content-Type: application/json');

$id_desa = $_GET['id_desa'] ?? '';

$sql = "SELECT 
            d.id_desa, d.id_kecamatan, 
            kc.id_kabupaten, 
            kb.id_provinsi
        FROM desa d
        JOIN kecamatan kc ON d.id_kecamatan = kc.id_kecamatan
        JOIN kabupaten kb ON kc.id_kabupaten = kb.id_kabupaten
        WHERE d.id_desa = ?";

$stmt = $db->prepare($sql);
$stmt->execute([$id_desa]);
$data = $stmt->fetchAll();

echo json_encode([
    'status' => true, 
    'data' => $data
]);