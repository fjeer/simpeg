<?php
// api/wilayah/kabupaten.php

require_once '../../config/Database.php';
header('Content-Type: application/json');

$id_provinsi = $_GET['id_provinsi'] ?? '';

$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT * FROM almt_kabupaten WHERE id_provinsi = ? ORDER BY kabupaten ASC");
$stmt->execute([$id_provinsi]);
$data = $stmt->fetchAll();

echo json_encode([
    'status' => true, 
    'data' => $data
]);