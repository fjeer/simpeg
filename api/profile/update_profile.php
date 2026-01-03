<?php
// api/pegawai/update_profile.php

require_once '../../vendor/autoload.php';
require_once '../../config/Database.php';
require_once '../../src/JwtService.php';
require_once '../../src/AuthMiddleware.php';

header('Content-Type: application/json');

$db = (new Database())->getConnection();
$jwtService = new JwtService($db);
$auth = new AuthMiddleware($jwtService);

// 1. Validasi Token & Ambil id_user
$userData = $auth->authenticate();

// Ambil id_person dari tabel users (karena person_id yang kita update)
$stmtUser = $db->prepare("SELECT id_person FROM users WHERE id_user = ?");
$stmtUser->execute([$userData['data']['id_user']]);
$userRow = $stmtUser->fetch();

if (!$userRow) {
    echo json_encode(['status' => false, 'message' => 'Data person tidak ditemukan']);
    exit;
}
$id_person_user = $userRow['id_person'];

// 2. Baca Input JSON
$input = json_decode(file_get_contents("php://input"), true);

// 3. Mapping Data (Pastikan key sesuai dengan yang dikirim payload JS)
$params = [
    ':nama' => $input['nama'] ?? '',
    ':jk' => ($input['jenis_kelamin'] == '' ? null : $input['jenis_kelamin']),
    ':nik' => $input['nik'] ?? null,
    ':nomor_kk' => $input['nokk'] ?? null,
    ':npwp' => $input['npwp'] ?? null,
    ':golongan_darah' => ($input['golongan_darah'] == '' ? null : $input['golongan_darah']),
    ':tempat_lahir' => $input['tempat_lahir'] ?? null,
    ':tanggal_lahir' => $input['tanggal_lahir'] ?? null,
    ':nomor_hp' => $input['nomor_hp'] ?? null,
    ':email' => $input['email'] ?? null,
    ':id_desa' => (!empty($input['id_desa']) ? $input['id_desa'] : null),
    ':rt' => $input['rt'] ?? null,
    ':rw' => $input['rw'] ?? null,
    ':alamat' => $input['alamat'] ?? null,
    ':id_person' => $id_person_user
];

try {
    // 4. Query SQL
    $sql = "UPDATE person SET 
                nama = :nama, 
                jk = :jk, 
                nik = :nik, 
                nomor_kk = :nomor_kk, 
                npwp = :npwp,
                golongan_darah = :golongan_darah,
                tempat_lahir = :tempat_lahir,
                tanggal_lahir = :tanggal_lahir,
                nomor_hp = :nomor_hp, 
                email = :email, 
                id_desa = :id_desa, 
                rt = :rt, 
                rw = :rw,
                alamat = :alamat
            WHERE id_person = :id_person";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        'status' => true,
        'message' => 'Profil berhasil diperbarui'
    ]);

} catch (PDOException $e) {
    // Berikan pesan error yang lebih spesifik jika terjadi kegagalan DB
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Gagal Database: ' . $e->getMessage()
    ]);
}