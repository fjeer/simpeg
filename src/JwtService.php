<?php
// src/JwtService.php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;

class JwtService
{
    private string $secretKey = "GANTI_DENGAN_KODE_RAHASIA_SANGAT_PANJANG_123";
    private string $issuer = "simpeg_app"; // Nama aplikasi kamu
    private string $audience = "simpeg_users"; // Target pengguna
    private int $defaultTtlMinutes = 60; // Token berlaku 1 jam
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Membuat Token JWT baru
     */
    public function createToken(array $userData): array
    {
        $now = time();
        $exp = $now + ($this->defaultTtlMinutes * 60);

        $payload = [
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $exp,
            'data' => $userData
        ];

        $token = JWT::encode($payload, $this->secretKey, 'HS256');

        return [
            'token' => $token,
            'payload' => $payload,
        ];
    }

    public function saveTokenToDb($userId, $token, $iat, $exp)
    {
        $sql = "INSERT INTO jwt_tokens (id_user, token_hash, issued_at, expires_at) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $userId,
            hash('sha256', $token), // Simpan hash-nya saja untuk keamanan
            date('Y-m-d H:i:s', $iat),
            date('Y-m-d H:i:s', $exp)
        ]);
    }

    /**
     * Memvalidasi dan Mendecode Token
     */
    public function validateToken(string $token): array
    {
        try {
            // Decode token menggunakan secret key dan algoritma HS256
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));

            if(( $decoded->iss ?? null) !== $this->issuer) {
                return [
                    'status' => false,
                    'message' => 'Token Issuer tidak valid',
                    'payload' => null 
                ];
            }

            if(( $decoded->aud ?? null ) !== $this->audience) {
                return [
                    'status' => false,
                    'message' => 'Token Audience tidak valid',
                    'payload' => null
                ];
            }

            $tokenHash = hash('sha256', $token);
            $stmt = $this->db->prepare("SELECT * FROM jwt_tokens WHERE token_hash = ?");
            $stmt->execute([$tokenHash]);
            $dbToken = $stmt->fetch();

            if (!$dbToken) {
                return [
                    'status' => false,
                    'message' => 'Token tidak ditemukan / sudah logOut',
                    'payload' => null
                ];
            }

            $payloadArray = json_decode(
                json_encode($decoded), 
                true
            );

            return [
                'status' => true,
                'message' => 'Token valid',
                'payload' => $payloadArray
            ];

        } catch (ExpiredException $e) {
            return [
                'ok' => false,
                'message' => 'Token sudah kadaluarsa',
                'payload' => null,
            ];
        } catch (SignatureInvalidException $e) {
            return [
                'ok' => false,
                'message' => 'Signature token tidak valid',
                'payload' => null,
            ];
        } catch (BeforeValidException $e) {
            return [
                'ok' => false,
                'message' => 'Token belum boleh digunakan (nbf/i-at)',
                'payload' => null,
            ];
        } catch (\Exception $e) {
            // Di production bisa dibuat lebih generic kalau mau
            return [
                'ok' => false,
                'message' => 'Token tidak valid',
                'payload' => null,
            ];
        }
    }

    /**
     * Membatalkan token (Logout)
     */
    public function revokeToken(string $token): bool
    {
        $tokenHash = hash('sha256', $token);
        $sql = "DELETE FROM jwt_tokens WHERE token_hash = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$tokenHash]);
    }

    /**
     * Menghapus token yang sudah expired atau di-revoke
     */
    public function cleanupTokens(): int
    {
        $sql = "DELETE FROM jwt_tokens WHERE expires_at < NOW() OR is_revoked = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount(); // Mengembalikan jumlah baris yang dihapus
    }
}