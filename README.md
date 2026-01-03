# 🏢 SIMPEG - Sistem Informasi Manajemen Pegawai

[![Stack](https://img.shields.io/badge/Stack-PHP%20%7C%20Javascript%20%7C%20Bootstrap-blue.svg)](https://github.com/)
[![Status](https://img.shields.io/badge/Status-Development-orange.svg)]()
[![License](https://img.shields.io/badge/License-MIT-green.svg)]()

SIMPEG adalah platform manajemen *human resource* yang dirancang untuk mengelola data personil secara efisien. Menggunakan arsitektur **Client-Side Rendering (CSR)** dengan komunikasi data via **RESTful API** berbasis PHP.

---

## 🚀 Fitur Unggulan

* **Role-Based Access Control (RBAC):** Diferensiasi hak akses antara `Supervisor`, `Admin`, dan `Superadmin`.
* **Cascading Dropdown Wilayah:** Integrasi data wilayah Indonesia (Provinsi hingga Desa) yang tersinkronisasi.
* **JWT Authentication:** Keamanan akses menggunakan JSON Web Token untuk proteksi API.
* **Dynamic Data Rendering:** Manipulasi DOM menggunakan JavaScript untuk performa UI yang lebih responsif.
* **Routing Root:** Otomatisasi pengalihan halaman berdasarkan status sesi (Login/Dashboard) melalui file index utama.

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| **Frontend** | HTML5, Bootstrap 5.3, JavaScript (Vanilla ES6) |
| **Backend** | PHP 8.x |
| **Database** | MySQL / MariaDB |
| **Auth** | JWT (Firebase/PHP-JWT) |
| **Tools** | Composer, VS Code |

---

## 📂 Struktur Database (`table_person`)

Berikut adalah skema tabel utama yang digunakan untuk menyimpan entitas pegawai:

* **Primary Identity:** `id_person` (smallint), `nama` (varchar 50), `nik` (varchar 16).
* **Biometric/Bio:** `jk` (enum L/P), `tempat_lahir`, `tanggal_lahir`, `golongan_darah`.
* **Geospatial Data:** `id_desa` (char 10) sebagai Foreign Key, `alamat`, `rt`, `rw`.
* **Account Integrity:** `email` (unique), `username`, `foto` (Hash).

---

## ⚙️ Instalasi & Konfigurasi

1.  **Clone Project:**
    ```bash
    git clone http://localhost/simpeg
    ```
2.  **Database Setup:**
    Import file `.sql` (jika ada) ke phpMyAdmin. Pastikan tabel `person` sesuai dengan skema.
3.  **Environment:**
    Sesuaikan konfigurasi database di folder `/config/database.php`.
4.  **Run:**
    Akses melalui `http://localhost/simpeg`. Sistem akan otomatis mendeteksi status login kamu.

---

## 🔒 Hak Akses (RBAC)

| Role | Edit Data | Hapus Data | Manajemen Role |
| :--- | :---: | :---: | :---: |
| **Supervisor** | ❌ | ❌ | ❌ |
| **Admin** | ✅ | ❌ | ❌ |
| **Superadmin** | ✅ | ✅ | ✅ |

> **Note:** Pembatasan dilakukan pada dua layer: UI (Frontend) dan Request Validator (Backend).

---

## 👨‍💻 Kontributor
* **Fjeer** - *Fullstack Developer* - [Mahasiswa TI]

---
Built with ❤️ and ☕ by IT Students.