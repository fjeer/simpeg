<?php 
// api/jabatan/list.php

require_once '../../vendor/autoload.php';
require_once '../../config/Database.php';
require_once '../../src/JwtService.php';
require_once '../../src/AuthMiddleware.php';

header('Content-Type: application/json');

$db = (new Database())->getConnection();
$jwtService = new JwtService($db);
$auth = new AuthMiddleware($jwtService);

$auth->authenticate(['admin', 'superadmin', 'developer']);

$stmt = $db->prepare("SELECT * FROM jabatan");
$stmt->execute();
$data = $stmt->fetchAll();

echo json_encode([
    'status' => true,
    'data' => $data
]);
