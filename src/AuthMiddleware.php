<?php
// src/AuthMiddleware.php

class AuthMiddleware
{
    private JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function authenticate($requiredRole = null)
    {
        // 1. Ambil Header Authorization
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode([
                'status' => false, 
                'message' => 'Token tidak ditemukan'
            ]);
            exit;
        }

        $token = $matches[1];
        $result = $this->jwtService->validateToken($token);

        // 2. Cek Validitas Token
        if ($result['status'] === false) {
            http_response_code(401);
            echo json_encode($result);
            exit;
        }
        
        // 3. Cek Role (Otorisasi)
        if ($requiredRole) {
            $userRole = $result['payload']['data']['role'];
            $isAuthorized = false;

            if (is_array($requiredRole)) {
                // Jika inputnya array, gunakan in_array
                $isAuthorized = in_array($userRole, $requiredRole);
            } else {
                // Jika inputnya string tunggal, gunakan perbandingan biasa
                $isAuthorized = ($userRole === $requiredRole);
            }

            if (!$isAuthorized) {
                http_response_code(403);
                echo json_encode([
                    'status' => false,
                    'message' => 'Anda tidak memiliki akses ke halaman ini. Role Anda: ' . $userRole
                ]);
                exit;
            }
        }

        return $result['payload']; // Kembalikan data user jika butuh di controller
    }
}