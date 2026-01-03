<?php
// api/wilayah/provinsi.php
require_once '../../config/Database.php';
header('Content-Type: application/json');

$db = (new Database())->getConnection();
$stmt = $db->query("SELECT * FROM almt_provinsi ORDER BY provinsi ASC");
$data = $stmt->fetchAll();

echo json_encode([
    'status' => true, 
    'data' => $data
]);