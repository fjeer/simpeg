<?php
// auth/process_login.php
require_once '../vendor/autoload.php';
require_once '../config/Database.php';
require_once '../src/JwtService.php';

header('Content-Type: application/json');
$input = json_decode(file_get_contents("php://input"), true);

// 1. Inisialisasi Database dan JwtService
try {
    $dbConnection = (new Database())->getConnection();
    $jwtService = new JwtService($dbConnection);
} catch (Exception $e) {
    echo json_encode(['status' => false, 'message' => 'Internal Server Error']);
    exit;
}

// 2. Ambil input (asumsi menggunakan JSON body atau POST)
$username = $input['username'] ?? $_POST['username'] ?? '';
$password = $input['password'] ?? $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode([
        'status' => false, 
        'message' => 'Username dan password wajib diisi'
    ]);
    exit;
}

// 3. Cari user dan join ke roles untuk multi-user support
$query = "SELECT 
                u.id_user,
                u.username,
                u.password_hash,
                u.is_active,
                r.nama_role,
                p.nama,
                p.nik
            FROM users u
            JOIN roles r ON u.id_role = r.id_role
            LEFT JOIN person p ON u.id_person = p.id_person
            WHERE u.username = :username
            LIMIT 1";

$stmt = $dbConnection->prepare($query);
$stmt->execute(['username' => $username]);
$user = $stmt->fetch();

// 4. Verifikasi User & Password
if (!$user || !password_verify($password, $user['password_hash'])) {
    echo json_encode([
        'status' => false, 
        'message' => 'Username atau password salah'
    ]);
    exit;
}

if ((int) $user['is_active'] !== 1) {
    echo json_encode([
        'status' => false,
        'message' => 'Akun tidak aktif'
    ]);
    exit;
}

// 5. Generate Token
// Kita simpan id_user dan role di dalam payload JWT
$userData = [
    'id_user' => $user['id_user'],
    'username' => $user['username'],
    'role' => $user['nama_role']
];

$authData = $jwtService->createToken($userData);

// simpan ke database
$jwtService->saveTokenToDb($user['id_user'], $authData['token'], $authData['payload']['iat'], $authData['payload']['exp']);

// $jwtService->cleanupTokens(); // Bersihkan sampah secara berkala


// 6. Response ke Client
echo json_encode([
    'status' => true,
    'message' => 'Login berhasil',
    'token' => $authData['token'],
    'expires_at' => date('Y-m-d H:i:s', $authData['payload']['exp']),
    'role' => $userData['role']
]);