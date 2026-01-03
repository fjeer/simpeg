<?php
// api/wilayah/kecamatan.php

require_once '../../config/Database.php';
header('Content-Type: application/json');

$id_kabupaten = $_GET['id_kabupaten'] ?? '';

$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT * FROM almt_kecamatan WHERE id_kabupaten = ? ORDER BY kecamatan ASC");
$stmt->execute([$id_kabupaten]);
$data = $stmt->fetchAll();

echo json_encode([
    'status' => true,
    'data' => $data
]);