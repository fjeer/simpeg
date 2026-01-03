<?php
// api/wilayah/desa.php

require_once '../../config/Database.php';
header('Content-Type: application/json');

$id_kecamatan = $_GET['id_kecamatan'] ?? '';

$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT * FROM almt_desa WHERE id_kecamatan = ? ORDER BY desa ASC");
$stmt->execute([$id_kecamatan]);
$data = $stmt->fetchAll();

echo json_encode([
    'status' => true,
    'data' => $data
]);