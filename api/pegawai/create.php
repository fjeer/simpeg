<?php
// api/pegawai/create.php
require_once '../../vendor/autoload.php';
require_once '../../config/Database.php';
require_once '../../src/JwtService.php';
require_once '../../src/AuthMiddleware.php';

header('Content-Type: application/json');

$db = (new Database())->getConnection();
$jwtService = new JwtService($db);
$auth = new AuthMiddleware($jwtService);

// 1. Autentikasi & Cek Role Pengguna yang sedang login
$userData = $auth->authenticate(['admin', 'superadmin', 'developer']);
$currentUserRole = $userData['data']['role'];

// 2. Ambil Input JSON
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode([
        'status' => false, 
        'message' => 'Data input tidak ditemukan'
    ]);
    exit;
}

$id_role_input = (int) ($input['id_role'] ?? 5);

// --- LOGIKA PEMBATASAN ROLE (Hierarki) ---
if ($currentUserRole === 'admin') {
    if (in_array($id_role_input, [1, 2])) {
        http_response_code(403);
        echo json_encode([
            'status' => false, 
            'message' => 'Admin tidak diizinkan membuat akun Superadmin atau Developer'
        ]);
        exit;
    }
} elseif ($currentUserRole === 'superadmin') {
    if ($id_role_input === 1) {
        http_response_code(403);
        echo json_encode([
            'status' => false, 
            'message' => 'Hanya Developer yang bisa membuat akun Developer'
        ]);
        exit;
    }
}

try {
    $db->beginTransaction();

    // 1. Insert ke tabel person (Data Lengkap)
    $sqlPerson = "INSERT INTO person (
        nama, jk, nik, nomor_kk, npwp,
        tempat_lahir, tanggal_lahir, nomor_hp, email, 
        id_desa, id_jabatan
    ) VALUES (
        :nama, :jk, :nik, :nomor_kk, :npwp, 
        :tempat_lahir, :tanggal_lahir, :nomor_hp, :email, 
        :id_desa, :id_jabatan
    )";

    $stmt = $db->prepare($sqlPerson);
    $stmt->execute([
        ':nama' => $input['nama'] ?? '',
        ':jk' => $input['jk'] ?? '',
        ':nik' => $input['nik'] ?? null,
        ':nomor_kk' => $input['nokk'] ?? null,
        ':npwp' => $input['npwp'] ?? null,
        ':tempat_lahir' => $input['tempat_lahir'] ?? null,
        ':tanggal_lahir' => $input['tanggal_lahir'] ?? null,
        ':nomor_hp' => $input['nomor_hp'] ?? null,
        ':email' => $input['email'] ?? null,
        ':id_desa' => !empty($input['id_desa']) ? $input['id_desa'] : null,
        ':id_jabatan' => $input['id_jabatan'] ?? null
    ]);

    $id_person_baru = $db->lastInsertId();

    // 2. Insert ke tabel users
    $username = $input['npwp'];
    $password = password_hash('123456', PASSWORD_BCRYPT);

    $stmtUser = $db->prepare("INSERT INTO users (id_person, username, password_hash, id_role) VALUES (?, ?, ?, ?)");
    $stmtUser->execute([$id_person_baru, $username, $password, $id_role_input]);

    $db->commit();
    echo json_encode([
        'status' => true, 
        'message' => 'Pegawai dan Akun User berhasil dibuat'
    ]);

} catch (Exception $e) {
    if ($db->inTransaction())
        $db->rollBack();
    http_response_code(500);
    echo json_encode([
        'status' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}