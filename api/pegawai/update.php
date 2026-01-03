<?php
require_once '../../vendor/autoload.php';
require_once '../../config/Database.php';
require_once '../../src/JwtService.php';
require_once '../../src/AuthMiddleware.php';

header('Content-Type: application/json');

$db = (new Database())->getConnection();
$jwt = new JwtService($db);
$auth = new AuthMiddleware($jwt);

$auth->authenticate(['admin', 'superadmin', 'developer']);

$input = json_decode(file_get_contents("php://input"), true);

try {
    $db->beginTransaction();

    $stmt = $db->prepare("
        UPDATE person SET
            nik = ?,
            nomor_kk = ?,
            npwp = ?,
            nama = ?,
            jk = ?,
            tempat_lahir = ?,
            tanggal_lahir = ?,
            email = ?,
            nomor_hp = ?,
            id_jabatan = ?,
            golongan_darah = ?,
            id_desa = ?,
            rt = ?,
            rw = ?,
            alamat = ?
        WHERE id_person = ?
    ");

    $stmt->execute([
        $input['nik'],
        $input['nomor_kk'],
        $input['npwp'],
        $input['nama'],
        $input['jk'] == '' ? null : $input['jk'],
        $input['tempat_lahir'],
        $input['tanggal_lahir'] == '' ? null : $input['tanggal_lahir'],
        $input['email'],
        $input['nomor_hp'],
        $input['id_jabatan'] == '' ? null : $input['id_jabatan'],
        $input['golongan_darah'] == '' ? null : $input['golongan_darah'],
        $input['id_desa'] == '' ? null : $input['id_desa'],
        $input['rt'],
        $input['rw'],
        $input['alamat'],
        $input['id_person']
    ]);

    $stmtUser = $db->prepare("
        UPDATE users SET
            username = ?,
            id_role = ?
        WHERE id_person = ?
    ");

    $stmtUser->execute([
        $input['username'],
        $input['id_role'],
        $input['id_person']
    ]);

    $db->commit();

    echo json_encode([
        'status' => true,
        'message' => 'Data pegawai berhasil diperbarui'
    ]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => $e->getMessage()
    ]);
}
